<?php

namespace App\Services\Banking\Parsers;

use App\Services\Banking\DTO\BankTransactionDTO;
use Illuminate\Support\Collection;
use SplFileObject;

class CsvBankStatementParser implements BankStatementParserInterface
{
    public function parse(string $filePath): Collection
    {
        $transactions = collect();
        $file = new SplFileObject($filePath);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        
        foreach ($file as $index => $row) {
             // Obsługa "podwójnie pakowanego" CSV (cały wiersz jako jedna kolumna)
             if (count($row) === 1 && isset($row[0])) {
                 $row = str_getcsv($row[0]);
             }

             if (empty($row)) continue;

             // Pomijamy nagłówek
             if ($index === 0) {
                 // Opcjonalne sprawdzenie nagłówków
                 continue;
             }
             
             // Format: "Numer rachunku/karty","Data transakcji","Data rozliczenia","Rodzaj transakcji","Na konto/Z konta","Odbiorca/Zleceniodawca","Opis","Obciążenia","Uznania","Saldo","Waluta"
             // Oczekujemy 11 kolumn dla tego formatu
             if (count($row) >= 11) {
                 $date = $row[1];
                 $type = $row[3];
                 $otherAccount = $row[4]; // Na konto/Z konta
                 $otherName = $row[5]; // Odbiorca/Zleceniodawca
                 $description = $row[6]; // Opis
                 
                 // Kwota: Obciążenia (7) lub Uznania (8)
                 $debit = str_replace([',', ' '], ['.', ''], $row[7]);
                 $credit = str_replace([',', ' '], ['.', ''], $row[8]);
                 
                 $amount = 0.0;
                 if ($credit !== '' && $credit !== null) {
                     $amount = (float) $credit;
                 } elseif ($debit !== '' && $debit !== null) {
                     $amount = (float) $debit;
                 }

                 $currency = $row[10] ?? 'PLN';

                 $transactions->push(new BankTransactionDTO(
                     transactionDate: $date,
                     amount: $amount,
                     senderName: $otherName ?? 'Nieznany',
                     senderAccount: $otherAccount ?? '',
                     title: $description ?? '',
                     currency: $currency
                 ));
                 continue;
             }

             // Wsparcie dla starego prostego formatu (Data, Nadawca, Tytuł, Kwota)
             if (count($row) >= 4 && count($row) < 11) {
                 $amount = str_replace([',', ' '], ['.', ''], $row[3]); 
                 $transactions->push(new BankTransactionDTO(
                     transactionDate: $row[0] ?? now()->toDateString(),
                     amount: (float) $amount,
                     senderName: $row[1] ?? 'Nieznany',
                     senderAccount: '',
                     title: $row[2] ?? '',
                     currency: 'PLN'
                 ));
             }
        }

        return $transactions;
    }
}
