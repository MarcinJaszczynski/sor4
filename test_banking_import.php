<?php

use App\Models\Contract;
use App\Models\Event;
use App\Models\Customer;
use App\Services\Banking\Parsers\CsvBankStatementParser;
use App\Services\Banking\BankReconciliationService;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Znajdź lub stwórz umowę do testów
$contract = Contract::first();
if (!$contract) {
    echo "Brak umów w bazie. Tworzę przykładową...\n";
    // 1.1 Znajdź Event
    $event = Event::first();
    if (!$event) {
        $event = Event::factory()->create(['name' => 'Wielka Wyprawa 2024']);
        echo "Stworzono Event ID: " . $event->id . "\n";
    }

    // 1.2 Znajdź ContractTemplate
    $template = \App\Models\ContractTemplate::first(); // Pobierz pierwszy szablon
    if (!$template) {
        $template = \App\Models\ContractTemplate::create([
            'name' => 'Default Template',
            'content' => 'Lorem ipsum'
        ]);
        echo "Stworzono Template ID: " . $template->id . "\n";
    }
    // echo "Używam Template ID: '" . $template->id . "' (Type: " . gettype($template->id) . ")\n";

    $contract = new Contract();
    $contract->event_id = $event->id;
    $contract->contract_template_id = $template->id; 
    // Dodaj jakieś dummy data
    $contract->contract_number = 'TEST/123';
    $contract->save();
}

$contractId = $contract->id;
echo "Testujemy dla Umowy ID: $contractId\n";

// 2. Stwórz lub użyj pliku CSV
$csvPath = __DIR__ . '/tools/mt940_examples/Historia_transakcji_20260113_150649.csv';
// $csvContent = "Data,Nadawca,Tytul,Kwota\n";
// $csvContent .= "2024-05-10,Jan Kowalski,Rezerwacja nr $contractId za wycieczkę,500.00\n";
// $csvContent .= "2024-05-11,Nieznany Ktoś,Opłata bez numeru,120.00\n";
// $csvContent .= "2024-05-12,Anna Nowak,Dopłata do umowy $contractId,300.50\n";

// file_put_contents($csvPath, $csvContent);
echo "Używam pliku testowego: $csvPath\n";

// 3. Uruchom Parser
try {
    echo "Parsowanie pliku...\n";
    $parser = new CsvBankStatementParser();
    $transactions = $parser->parse($csvPath);
    echo "Znaleziono transakcji: " . $transactions->count() . "\n";

    // 4. Uruchom Reconciliation
    echo "Parowanie płatności...\n";
    $service = new BankReconciliationService();
    $results = $service->reconcile($transactions);

    $incomes = $results->filter(fn($r) => $r['transaction']->amount > 0);
    $expenses = $results->filter(fn($r) => $r['transaction']->amount < 0);

    echo "\n--- WPŁATY (" . $incomes->count() . ") ---\n";
    foreach ($incomes as $result) {
        $tx = $result['transaction'];
        $status = $result['match_found'] ? "[DOPASOWANO -> Umowa ID: {$result['matched_contract']->id}]" : "[BRAK]";
        
        echo "{$tx->transactionDate} | {$tx->amount} PLN | {$tx->senderName} | {$tx->title} => $status\n";
    }

    echo "\n--- WYDATKI (" . $expenses->count() . ") ---\n";
    foreach ($expenses as $result) {
        $tx = $result['transaction'];
        // Dla wydatków matching może nie mieć sensu w kontekście umów klienckich, ale zostawiamy informacyjnie
        // $status = $result['match_found'] ? "[DOPASOWANO -> Umowa ID: {$result['matched_contract']->id}]" : "[BRAK]";
        
        echo "{$tx->transactionDate} | {$tx->amount} PLN | {$tx->senderName} | {$tx->title}\n";
    }

} catch (\Exception $e) {
    echo "Błąd: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

// Cleanup
// unlink($csvPath);
