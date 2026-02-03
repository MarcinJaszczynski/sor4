<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventTemplate;
use App\Models\EventDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesApplication;

class PortalFlowTest extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a test user and authenticate to satisfy Event::creating() created_by hook
        \Illuminate\Support\Facades\DB::table('users')->insert([
            'id' => 1,
            'name' => 'Portal Test User',
            'email' => 'portal@example.test',
            'password' => bcrypt('password'),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->actingAs(User::find(1));
    }

    protected function createTestEvent(array $overrides = [])
    {
        // Potrzebujemy szablonu
        $template = EventTemplate::first();
        if (!$template) {
            // Tworzymy dummy template jeśli nie ma
            $template = EventTemplate::create([
                'name' => 'Template Testowy',
                'status' => 'active', 
            ]);
        }

        return Event::create(array_merge([
            'event_template_id' => $template->id,
            'name' => 'Wycieczka Testowa',
            'client_name' => 'Klient Testowy',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(15),
            'duration_days' => 5,
            'participant_count' => 40,
            'status' => 'confirmed',
            'access_code_participant' => 'PART123',
            'access_code_manager' => 'MGR123',
        ], $overrides));
    }

    public function test_portal_login_page_loads()
    {
        $response = $this->get('/strefa-klienta/login');
        $response->assertStatus(200);
        $response->assertSee('Strefa Klienta - Logowanie');
    }

    public function test_participant_login_flow()
    {
        // Arrange
        $event = $this->createTestEvent([
            'name' => 'Wycieczka Testowa Participant',
            'access_code_participant' => 'PART_TEST_1',
            'access_code_manager' => 'MGR_TEST_1',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(12),
        ]);

        // Act - Login
        $response = $this->post('/strefa-klienta/login', [
            'access_code' => 'PART_TEST_1',
        ]);

        // Assert Redirect to Dashboard
        $response->assertRedirect('/strefa-klienta/dashboard');
        
        // Follow Redirect and check session
        $this->assertEquals($event->id, session('portal_event_id'));
        $this->assertEquals('participant', session('portal_role'));

        // Check Dashboard Content
        $response = $this->withSession([
            'portal_event_id' => $event->id, 
            'portal_role' => 'participant'
        ])->get('/strefa-klienta/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Wycieczka Testowa Participant');

        // Cleanup
        $event->delete();
    }

    public function test_manager_login_flow()
    {
        // Arrange
        $event = $this->createTestEvent([
            'name' => 'Wycieczka Testowa Manager',
            'access_code_participant' => 'PART_TEST_2',
            'access_code_manager' => 'MGR_TEST_2',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(12),
        ]);

        $doc = EventDocument::create([
            'event_id' => $event->id,
            'name' => 'Tajny Dokument',
            'file_path' => 'dummy.pdf',
            'type' => 'manager',
        ]);

        // Act - Login
        $response = $this->post('/strefa-klienta/login', [
            'access_code' => 'MGR_TEST_2',
        ]);

        // Assert
        $response->assertRedirect('/strefa-klienta/dashboard');
        $this->assertEquals('manager', session('portal_role'));

        // Check Documents Access
        $response = $this->withSession([
            'portal_event_id' => $event->id, 
            'portal_role' => 'manager'
        ])->get('/strefa-klienta/documents');

        $response->assertStatus(200);
        $response->assertSee('Tajny Dokument');
        $response->assertSee('Strefa Organizatora'); // Banner check

        // Cleanup
        $doc->delete();
        $event->delete();
    }

    public function test_invalid_code_login()
    {
        $response = $this->post('/strefa-klienta/login', [
            'access_code' => 'WRONGCODE',
        ]);

        $response->assertSessionHas('error');
    }
}
