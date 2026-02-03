<?php

namespace App\Services\Banking\Parsers;

use App\Services\Banking\DTO\BankTransactionDTO;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class Mt940BankStatementParser implements BankStatementParserInterface
{
    public function parse(string $filePath): Collection
    {
        $content = file_get_contents($filePath);
        $transactions = collect();

        // Uproszczony tekstowy parser MT940: znajdujemy pary :61: (transakcja) i :86: (opis)
        $lines = preg_split('/\r\n|\r|\n/', $content);
        $count = count($lines);
        for ($i = 0; $i < $count; $i++) {
            $line = trim($lines[$i]);
            if (str_starts_with($line, ':61:')) {
                $raw61 = substr($line, 4);

                // Parsuj datę (pierwsze 6 cyfr: YYMMDD)
                $transactionDate = null;
                if (preg_match('/^(\d{6})/', $raw61, $m)) {
                    try {
                        $transactionDate = Carbon::createFromFormat('ymd', $m[1])->format('Y-m-d');
                    } catch (\Throwable $e) {
                        $transactionDate = date('Y-m-d');
                    }
                } else {
                    $transactionDate = date('Y-m-d');
                }

                // Parsuj znak i kwotę (C = credit, D = debit)
                $amount = 0.0;
                if (preg_match('/[CD]([0-9,]+)(?:[A-Z])*/i', $raw61, $m2)) {
                    $amt = str_replace(',', '.', $m2[1]);
                    $amount = (float) $amt;
                    if (str_contains($raw61, 'D')) {
                        $amount = -abs($amount);
                    }
                }

                // Spróbuj wyciągnąć identyfikator transakcji po // (np. //BK1001)
                $transactionId = null;
                if (preg_match('/\/\/(\S+)/', $raw61, $m3)) {
                    $transactionId = $m3[1];
                }

                // Znajdź następną linię :86: i zbierz ewentualne kontynuacje (linie bez znacznika)
                $title = '';
                for ($j = $i + 1; $j < $count; $j++) {
                    $next = $lines[$j];
                    if (trim($next) === '') {
                        continue;
                    }
                    if (str_starts_with(trim($next), ':86:')) {
                        $part = substr(trim($next), 4);
                        $title .= $part;
                        // zbierz kolejne linie, które nie zaczynają się od ':' (kontynuacja pola)
                        $k = $j + 1;
                        while ($k < $count && !str_starts_with(trim($lines[$k]), ':')) {
                            $title .= ' ' . trim($lines[$k]);
                            $k++;
                        }
                        break;
                    }
                    // jeśli natrafimy na kolejny tag inny niż :86: przed :86:, przerwij
                    if (str_starts_with(trim($next), ':')) {
                        break;
                    }
                }

                $title = trim($title);

                // Utwórz DTO — nadawca i konto mogą być puste, bo MT940 może nie zawierać ich w prostym pliku
                $dto = new BankTransactionDTO(
                    $transactionDate,
                    $amount,
                    '', // senderName
                    '', // senderAccount
                    $title ?: ($transactionId ?? ''),
                    'PLN',
                    $transactionId
                );

                $transactions->push($dto);
            }
        }

        return $transactions;
    }
}
