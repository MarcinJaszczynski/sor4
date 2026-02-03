<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Banking\BankReconciliationService;
use App\Services\Banking\DTO\BankTransactionDTO;

class BankReconciliationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_reconcile_structure()
    {
        $service = new BankReconciliationService();
        $dto = new BankTransactionDTO('2023-01-10', 1500.00, 'Jan Kowalski', '', 'IMP_ID=1001;BOOKING=BK-1001');
        $results = $service->reconcile(collect([$dto]));

        $this->assertIsIterable($results);
        $first = $results->first();
        $this->assertArrayHasKey('transaction', $first);
        $this->assertArrayHasKey('match_found', $first);
        $this->assertArrayHasKey('confidence', $first);
        $this->assertArrayHasKey('parsed_keys', $first);
    }
}
