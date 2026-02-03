<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Banking\Parsers\Mt940BankStatementParser;
use App\Services\Banking\BankReconciliationService;

class ParseMt940Examples extends Command
{
    protected $signature = 'bank:parse-mt940-examples {--apply : Zastosuj (utwórz płatności dla dopasowań o wysokiej pewności)}';
    protected $description = 'Parsuje przykładowe pliki MT940 z katalogu tools/mt940_examples';

    public function handle()
    {
        $dir = base_path('tools/mt940_examples');
        if (!is_dir($dir)) {
            $this->error('Katalog ' . $dir . ' nie istnieje');
            return 1;
        }

        // zbierz .sta i .txt
        $files = array_merge(glob($dir . '/*.sta') ?: [], glob($dir . '/*.txt') ?: []);
        if (empty($files)) {
            $this->info('Brak plików .sta/.txt w ' . $dir);
            return 0;
        }

        $parser = new Mt940BankStatementParser();

        $reconciler = new BankReconciliationService();
        $apply = $this->option('apply');

        foreach ($files as $file) {
            $this->info('Plik: ' . $file);
            $transactions = $parser->parse($file);
            $results = $reconciler->reconcile($transactions);
            foreach ($results as $res) {
                $t = $res['transaction'];
                $match = $res['matched_contract'];
                $matchDesc = $match ? ('contract_id=' . $match->id . ' number=' . $match->contract_number) : 'NO MATCH';
                $parsed = $res['parsed_keys'] ?? [];
                $parsedStr = empty($parsed) ? '-' : implode(', ', array_map(function($k, $v){ return "$k=$v"; }, array_keys($parsed), $parsed));
                $this->line(' - Date: ' . $t->transactionDate . ' | Amount: ' . number_format($t->amount, 2, ',', '') . ' | Title: ' . $t->title . ' | Confidence: ' . ($res['confidence'] ?? 'none') . ' | Parsed: ' . $parsedStr . ' | Match: ' . $matchDesc);

                if ($apply && ($res['confidence'] ?? 'none') === 'high' && $match) {
                    // sprawdź czy już istnieje po invoice_number (transactionId)
                    $txId = $t->transactionId ?? null;
                    $exists = $txId ? \App\Models\EventPayment::where('invoice_number', $txId)->exists() : false;
                    if ($exists) {
                        $this->info('  -> Pomiń: transakcja już zaksięgowana (invoice_number=' . $txId . ')');
                    } else {
                        \App\Models\EventPayment::create([
                            'event_id' => $match->event_id,
                            'amount' => $t->amount,
                            'currency' => 'PLN',
                            'payment_date' => $t->transactionDate,
                            'description' => $t->title,
                            'invoice_number' => $txId,
                            'is_advance' => str_contains(strtolower($t->title), 'zaliczka'),
                            'source' => 'office',
                        ]);
                        $this->info('  -> Utworzono EventPayment dla contract ' . $match->id);
                    }
                }
            }
            $this->line('');
        }

        return 0;
    }
}
