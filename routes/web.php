<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\EventTemplate;
use App\Http\Controllers\Admin\NotificationController;
use App\Models\Conversation;
use App\Http\Controllers\EventCsvController;
use App\Http\Controllers\EventPriceDescriptionController;
use App\Http\Controllers\Admin\CalendarFeedController;

// === FRONTEND ROUTES (dodane z mergingSOR) ===
use App\Http\Controllers\Front\FrontController;
use App\Models\Place;
use App\Support\Region;
use Illuminate\Http\Request;
use App\Http\Controllers\EmailAttachmentController;

// Redirect old root to canonical region root using cookie (handled by middleware later)
Route::get('/', function () {
    // Użyj helpera Region, aby domyślnie kierować do Warszawy gdy brak cookie
    $cookieId = request()->cookie('start_place_id');
    $slug = Region::slugForLinks($cookieId ? (int)$cookieId : null);
    return redirect()->route('home', ['regionSlug' => $slug]);
});

// Compatibility: accept GET on livewire update endpoint to avoid MethodNotAllowed
// Some browsers or scripts may attempt a GET; Livewire expects POST. Return empty 200.
Route::get('/livewire/update', function () {
    return response('', 200);
});

// Przechwyć wszystkie żądania, które zaczynają się od /region i przekieruj
// je do rzeczywistego sluga (np. /warszawa/...), zachowując resztę ścieżki i query string.
Route::any('/region/{any?}', function (Request $request, $any = '') {
    $slug = Region::slugForLinks(null);
    $path = trim($any, '/');
    $new = '/' . $slug . ($path !== '' ? '/' . $path : '');
    $qs = $request->getQueryString();
    if ($qs) {
        $new .= '?' . $qs;
    }
    return redirect($new, 301);
})->where('any', '.*');

// Public login helper: keep legacy links working by pointing to Filament login screen
Route::get('/login', fn() => redirect()->route('filament.admin.auth.login'))->name('login');
// Global blog routes (no region slug) for canonical blog URLs
Route::get('/blog', [FrontController::class, 'blog'])->name('blog.global');
Route::get('/blog/{slug}', [FrontController::class, 'blogPost'])->name('blog.post.global');
// Global documents routes (no region slug)
Route::get('/documents', [FrontController::class, 'documents'])->name('documents.global');
Route::get('/documents/{slug}', [FrontController::class, 'document'])->name('documents.post.global');

// Portal Klienta (Imprezy)
Route::prefix('strefa-klienta')->name('portal.')->group(function () {
    // Legacy: allow both '/strefa-klienta' and '/strefa-klienta/login' to show login
    Route::get('/', [App\Http\Controllers\PortalController::class, 'showLogin'])->name('login');
    Route::get('/login', [App\Http\Controllers\PortalController::class, 'showLogin'])->name('login.show');

    Route::middleware('auth')->group(function () {
        Route::get('attachments/{attachment}/download', [EmailAttachmentController::class, 'download'])
            ->name('attachments.download');
    });
    Route::post('/login', [App\Http\Controllers\PortalController::class, 'login'])->name('auth');
    Route::get('/logout', [App\Http\Controllers\PortalController::class, 'logout'])->name('logout');
    
    // Activation Flow
    Route::post('/validate-code', [App\Http\Controllers\PortalController::class, 'validateCode'])->name('validate_code');
    Route::get('/rejestracja', [App\Http\Controllers\PortalController::class, 'showRegister'])->name('register');
    Route::post('/rejestracja', [App\Http\Controllers\PortalController::class, 'register'])->name('register_submit');

    Route::middleware([\App\Http\Middleware\PortalAuthMiddleware::class])->group(function() {
        Route::get('/dashboard', [App\Http\Controllers\PortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/documents', [App\Http\Controllers\PortalController::class, 'documents'])->name('documents');
        Route::get('/documents/{id}/download', [App\Http\Controllers\PortalController::class, 'downloadDocument'])->name('documents.download');
        Route::get('/payments', [App\Http\Controllers\PortalController::class, 'payments'])->name('payments');
        Route::post('/payments/pay', [App\Http\Controllers\PortalController::class, 'processPayment'])->name('payments.process'); // Symulacja
        
        // Pilot functionality
        Route::get('/wydatki', [App\Http\Controllers\PortalController::class, 'expenses'])->name('expenses');
        Route::post('/wydatki', [App\Http\Controllers\PortalController::class, 'storeExpense'])->name('expenses.store');
        Route::get('/wiadomosci', [App\Http\Controllers\PortalController::class, 'pilotMessages'])->name('messages');
        
        Route::get('/contact', [App\Http\Controllers\PortalController::class, 'contact'])->name('contact');
        Route::post('/contact', [App\Http\Controllers\PortalController::class, 'sendMessage'])->name('contact.submit');
        // Help pages
        Route::get('/help', function () {
            return response()->file(base_path('docs/USER_GUIDE.html'));
        })->name('help');
        Route::get('/help/user-guide.pdf', function () {
            return response()->download(base_path('docs/USER_GUIDE.pdf'), 'USER_GUIDE.pdf');
        })->name('help.pdf');
    });
});

Route::group(['prefix' => '{regionSlug}', 'where' => ['regionSlug' => '[A-Za-z0-9\-]+']], function () {
    Route::post('/send-email', [FrontController::class, 'sendEmail'])->middleware('throttle:5,1')->name('send-email');
    Route::get('/', [FrontController::class, 'home'])->name('home');
    Route::get('/directory-packages', [FrontController::class, 'directorypackages'])->name('directory-packages');
    // regional blog routes removed: use global routes '/blog' and '/blog/{slug}' instead
    // public-facing packages are now under '/oferty' (Polish). Keep the route name 'packages'
    // so all existing calls to route('packages') keep working. Add a redirect from the old
    // '/packages' path to the new '/oferty' for backward compatibility.
    Route::get('/oferty', [FrontController::class, 'packages'])->name('packages');
    Route::get('/packages', function ($regionSlug) {
        return redirect()->route('packages', ['regionSlug' => $regionSlug]);
    });
    Route::get('/packages/partial', [FrontController::class, 'packagesPartial'])->name('packages.partial');
    Route::get('/package/{slug}', [FrontController::class, 'package'])->name('package');
    // Pretty package route remains the same pattern but now nested (duplicated regionSlug) -> keep original outside group
    Route::get('/insurance', [FrontController::class, 'insurance'])->name('insurance');
    Route::get('/documents', function ($regionSlug) {
        return redirect()->route('documents.global');
    })->name('documents');
    Route::get('/contact', [FrontController::class, 'contact'])->name('contact');
    Route::get('/faq', [FrontController::class, 'faq'])->name('faq');
});

// SEO friendly pretty package route stays global to avoid double region slug
// Pretty package route (unicode-aware slug). Allow diacritics in slug using \pL (letters) + digits + hyphen.
Route::get('/{regionSlug}/{dayLength}/{id}/{slug}', [FrontController::class, 'packagePretty'])
    ->where([
        'regionSlug' => '[A-Za-z0-9\-]+', // pozostawiamy region jako ascii slug (pochodzi z Place::name slug)
        'dayLength' => '[0-9]+-dniowe',
        'id' => '[0-9]+',
        'slug' => '[\pL0-9\-]+' // wymaga trybu unicode w PCRE, Laravel domyślnie używa 'u'
    ])
    ->name('package.pretty');

Route::post('/{regionSlug}/{dayLength}/{id}/{slug}/word', [FrontController::class, 'packagePrettyWord'])
    ->where([
        'regionSlug' => '[A-Za-z0-9\-]+',
        'dayLength' => '[0-9]+-dniowe',
        'id' => '[0-9]+',
        'slug' => '[\pL0-9\-]+'
    ])
    ->middleware('auth')
    ->name('package.pretty.word');


// Import/eksport CSV dla Eventów
Route::get('/events/export-csv', [EventCsvController::class, 'export'])->name('events.export.csv');
Route::post('/events/import-csv', [EventCsvController::class, 'import'])->name('events.import.csv');

Route::get('/test-log', function () {
    Log::info('Test route accessed at ' . now());
    return 'Test log written - check storage/logs/laravel.log';
});

// Local-only: quick email test endpoint
Route::get('/test-mail', function () {
    if (!config('app.debug')) {
        abort(404);
    }
    try {
        \Illuminate\Support\Facades\Mail::raw('Test message from /test-mail at ' . now(), function ($m) {
            $m->to(config('mail.inquiries_to') ?: (app()->environment('production') ? 'rafa@bprafa.pl' : 'm.jasczynski@gmail.com'))
                ->subject('Postmark/Mailer smoke test');
        });
        return 'Mail dispatched using mailer: ' . config('mail.default');
    } catch (\Throwable $e) {
        return response('Mail failed: ' . $e->getMessage(), 500);
    }
});

Route::get('/test-drag-drop', function () {
    Log::info('Testing drag & drop functionality');

    try {
        // Znajdź pierwszy event template
        $eventTemplate = EventTemplate::first();
        if (!$eventTemplate) {
            return 'No event template found';
        }

        // Utwórz instancję komponentu
        $kanban = new \App\Filament\Resources\EventTemplateResource\Widgets\EventProgramKanban();
        $kanban->record = $eventTemplate;

        // Sprawdź, czy są jakieś punkty programu
        $pivotRecords = \Illuminate\Support\Facades\DB::table('event_template_event_template_program_point')
            ->where('event_template_id', $eventTemplate->id)
            ->get();

        if ($pivotRecords->isEmpty()) {
            return 'No program points found for event template';
        }

        // Testuj movePoint z pierwszym rekordem
        $firstRecord = $pivotRecords->first();
        $kanban->movePoint($firstRecord->id, 2, [$firstRecord->id]);

        return 'Test completed - check logs';
    } catch (\Exception $e) {
        Log::error('Test drag & drop error: ' . $e->getMessage());
        return 'Error: ' . $e->getMessage();
    }
});

// Public short route to find event by its unique `public_code` and redirect to panel
Route::get('/e/{code}', function ($code) {
    $event = \App\Models\Event::where('public_code', $code)->first();
    if (!$event) {
        abort(404);
    }

    // If current user can access admin panel - redirect to Filament edit URL
    if (auth()->check() && auth()->user()->can('viewAny', \App\Models\Event::class)) {
        return redirect(\App\Filament\Resources\EventResource::getUrl('edit', ['record' => $event->id]));
    }

    // Otherwise show a simple public page if exists or redirect to portal login
    return redirect()->route('portal.login');
})->name('event.by_code');

Route::get('/check-data', function () {
    try {
        $eventTemplate = EventTemplate::first();
        if (!$eventTemplate) {
            return response()->json(['error' => 'No event template found']);
        }

        $pivotRecords = \Illuminate\Support\Facades\DB::table('event_template_event_template_program_point')
            ->where('event_template_id', $eventTemplate->id)
            ->get();

        $programPoints = $eventTemplate->programPoints()->withPivot(['day', 'order_number'])->get();

        $data = $programPoints->map(function ($point) {
            return [
                'id' => $point->id,
                'pivot_id' => $point->pivot->id,
                'name' => $point->name,
                'day' => $point->pivot->day,
                'order_number' => $point->pivot->order_number
            ];
        });

        return response()->json([
            'event_template' => $eventTemplate->name,
            'program_points' => $data,
            'pivot_records_count' => $pivotRecords->count()
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});

Route::get('/auto-login', function () {
    try {
        // Sprawdź czy użytkownik testowy istnieje
        $user = \App\Models\User::where('email', 'admin@test.com')->first();
        if (!$user) {
            return 'User not found. Please run: php artisan make:test-user';
        }

        // Zaloguj użytkownika
        Auth::login($user, true);

        // Przekieruj do panelu admina
        return redirect('/admin');
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

// Admin-only JSON feed for FullCalendar (events + notes)
Route::middleware(['auth'])
    ->prefix('admin/api')
    ->group(function () {
        Route::get('/calendar/feed', CalendarFeedController::class)->name('admin.calendar.feed');
    });

// Admin notifications API endpoint
Route::middleware(['auth', 'web'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/notifications/counts', [NotificationController::class, 'getCounts'])->name('notifications.counts');
    Route::get('/notifications/task-comments/{notification}/open', [NotificationController::class, 'openTaskComment'])
        ->name('notifications.task-comments.open');
    Route::post('/task-comments/{task}/reply', [NotificationController::class, 'replyToTaskComment'])
        ->name('task-comments.reply');
    
    // Event PDF generation routes
    Route::prefix('events/{event}')->name('events.')->group(function () {
        Route::get('/pdf/pilot', [\App\Http\Controllers\EventPdfController::class, 'pilotPdf'])->name('pdf.pilot');
        Route::get('/pdf/driver', [\App\Http\Controllers\EventPdfController::class, 'driverPdf'])->name('pdf.driver');
        Route::get('/pdf/hotel', [\App\Http\Controllers\EventPdfController::class, 'hotelPdf'])->name('pdf.hotel');
        Route::get('/pdf/briefcase', [\App\Http\Controllers\EventPdfController::class, 'briefcasePdf'])->name('pdf.briefcase');
    });
});

Route::get('/admin/conversations', function () {
    $user = Auth::user();
    if (!$user) {
        return redirect('/login');
    }
    // Najpierw nieprzeczytana, potem najnowsza
    $conversation = Conversation::whereHas('participants', function ($q) use ($user) {
        $q->where('user_id', $user->id);
    })
        ->with(['participants', 'messages'])
        ->get()
        ->sortByDesc(fn($c) => $c->unreadCount($user))
        ->sortByDesc('last_message_at')
        ->first();
    if ($conversation) {
        return redirect('/admin/chat?conversation=' . $conversation->id);
    }
    return redirect('/admin/chat');
});

// Event price description routes
Route::get('/event/{eventId}/price-description', [EventPriceDescriptionController::class, 'show'])->name('event.price-description.show');
Route::get('/event/{eventId}/price-description/edit', [EventPriceDescriptionController::class, 'edit'])->name('event.price-description.edit');
Route::post('/event/{eventId}/price-description/update', [EventPriceDescriptionController::class, 'update'])->name('event.price-description.update');

use App\Http\Controllers\Client\ClientPanelController;

Route::prefix('client')->name('client.')->group(function () {
    Route::get('/panel/{uuid}', [ClientPanelController::class, 'dashboard'])->name('panel');
    Route::post('/panel/{uuid}/participants', [ClientPanelController::class, 'updateParticipants'])->name('participants.update');
    Route::get('/panel/{uuid}/contract', [ClientPanelController::class, 'downloadContract'])->name('contract');
    Route::get('/panel/{uuid}/addendum/{addendum}', [ClientPanelController::class, 'downloadAddendum'])->name('addendum');

    Route::get('/panel/{uuid}/voucher', [ClientPanelController::class, 'downloadVoucher'])->name('voucher');
});

// Admin: Offer PDF download (outside client group)
Route::get('/admin/offers/{id}/pdf', [\App\Http\Controllers\Admin\OfferPdfController::class, 'download'])->name('admin.offer.pdf');
    Route::get('/admin/offers/{id}/word', [\App\Http\Controllers\Admin\OfferWordController::class, 'download'])->name('admin.offer.word');
use App\Http\Controllers\Pilot\PilotPanelController;
use App\Http\Controllers\Reports\EventReportController;

Route::middleware(['auth'])->prefix('pilot')->name('pilot.')->group(function () {
    Route::get('/', [PilotPanelController::class, 'index'])->name('index');
    Route::get('/event/{id}', [PilotPanelController::class, 'show'])->name('event');
    Route::get('/event/{id}/participants', [PilotPanelController::class, 'participants'])->name('participants');
    Route::post('/event/{id}/participants/{participantId}', [PilotPanelController::class, 'updateParticipant'])->name('participants.update');
    Route::get('/event/{id}/expenses', [PilotPanelController::class, 'expenses'])->name('expenses');
    Route::post('/event/{id}/expenses', [PilotPanelController::class, 'storeExpense'])->name('expenses.store');
    Route::get('/event/{id}/payments', [PilotPanelController::class, 'payments'])->name('payments');
    Route::post('/event/{id}/payments', [PilotPanelController::class, 'storePayment'])->name('payments.store');
    
    // Raporty dostępne dla pilota
    Route::get('/event/{id}/report/rooming', [EventReportController::class, 'roomingList'])->name('report.rooming');
    Route::get('/event/{id}/report/manifest', [EventReportController::class, 'flightManifest'])->name('report.manifest');
});
