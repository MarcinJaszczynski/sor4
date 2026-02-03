<?php

namespace App\Services\Banking;

use App\Models\Contract;
use App\Services\Banking\DTO\BankTransactionDTO;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Models\EventPayment;

class BankReconciliationService
{
    /**
     * Próbuje dopasować transakcje do umów/rezerwacji.
     *
     * @param Collection<int, BankTransactionDTO> $transactions
     * @return Collection
     */
    public function reconcile(Collection $transactions): Collection
    {
        return $transactions->map(function (BankTransactionDTO $transaction) {
            $matchResult = $this->findMatch($transaction);
            $contract = $matchResult['contract'] ?? null;
            $confidence = $matchResult['confidence'] ?? 'none';
            $already = false;
            if (!empty($transaction->transactionId)) {
                $already = EventPayment::where('invoice_number', $transaction->transactionId)->exists();
            }

            return [
                'transaction' => $transaction,
                'match_found' => (bool) $contract,
                'matched_contract' => $contract,
                'confidence' => $confidence,
                'match_reason' => $matchResult['reason'] ?? null,
                'match_score' => $matchResult['score'] ?? null,
                'parsed_keys' => $this->parseKeyValueTitle($transaction->title),
                'already_exists' => $already,
            ];
        });
    }

    /**
     * Znajdź najlepsze dopasowanie dla pojedynczej transakcji.
     * Zwraca tablicę z kluczami: contract (model|null), confidence (none|low|medium|high), reason, opcjonalnie score.
     */
    private function findMatch(BankTransactionDTO $transaction): array
    {
        $kv = $this->parseKeyValueTitle($transaction->title);

        // Jeśli mamy BOOKING => wysoka pewność
        if (!empty($kv['BOOKING'])) {
            $booking = $kv['BOOKING'];
            $contract = Contract::where('contract_number', $booking)->first();
            if ($contract) {
                return ['contract' => $contract, 'confidence' => 'high', 'reason' => 'booking_match'];
            }
        }

        // Jeśli mamy IMP_ID => wysoka pewność
        if (!empty($kv['IMP_ID'])) {
            $impId = $kv['IMP_ID'];
            $contract = Contract::where('event_id', $impId)->first();
            if ($contract) {
                return ['contract' => $contract, 'confidence' => 'high', 'reason' => 'imp_id_match'];
            }
        }

        // Jeśli mamy EVENT_CODE => spróbuj znaleźć event i umowę
        if (!empty($kv['EVENT_CODE'])) {
            $code = $kv['EVENT_CODE'];
            $event = \App\Models\Event::where('public_code', $code)->orWhere('name', 'like', "%{$code}%")->first();
            if ($event) {
                $contract = Contract::where('event_id', $event->id)->first();
                if ($contract) {
                    return ['contract' => $contract, 'confidence' => 'high', 'reason' => 'event_code_match'];
                }
            }
        }

        // Fuzzy matching po nazwiskach/kliencie — ogranicz liczbę kandydatów po dacie eventu
        $candidatesQuery = Contract::with(['event', 'participants']);
        $txDate = null;
        try {
            $txDate = $transaction->transactionDate ? Carbon::parse($transaction->transactionDate) : null;
        } catch (\Throwable $e) {
            $txDate = null;
        }

        if ($txDate) {
            $from = $txDate->copy()->subYear();
            $to = $txDate->copy()->addYear();
            $candidatesQuery = $candidatesQuery->whereHas('event', function($q) use ($from, $to) {
                $q->whereBetween('start_date', [$from->format('Y-m-d'), $to->format('Y-m-d')]);
            });
        }

        $candidates = $candidatesQuery->limit(200)->get();
        // jeśli brak wyników, poszerzamy bez filtra
        if ($candidates->isEmpty()) {
            $candidates = Contract::with(['event', 'participants'])->limit(200)->get();
        }
        $best = null;
        $bestScore = 0;

        $searchText = strtolower($transaction->senderName . ' ' . $transaction->title);

        foreach ($candidates as $contract) {
            $score = 0;
            $eventName = strtolower(optional($contract->event)->client_name ?? '');
            if ($eventName) {
                $lev = levenshtein($eventName, $searchText);
                $maxLen = max(strlen($eventName), strlen($searchText));
                $similarity = $maxLen > 0 ? (1 - $lev / $maxLen) : 0;
                $score = max($score, $similarity);
            }

            foreach ($contract->participants as $p) {
                $pname = strtolower(trim($p->first_name . ' ' . $p->last_name));
                if ($pname) {
                    $lev = levenshtein($pname, $searchText);
                    $maxLen = max(strlen($pname), strlen($searchText));
                    $similarity = $maxLen > 0 ? (1 - $lev / $maxLen) : 0;
                    $score = max($score, $similarity);
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $contract;
            }
        }

        if ($best && $bestScore >= 0.85) {
            return ['contract' => $best, 'confidence' => 'high', 'reason' => 'fuzzy_name_high', 'score' => $bestScore];
        }
        if ($best && $bestScore >= 0.6) {
            return ['contract' => $best, 'confidence' => 'medium', 'reason' => 'fuzzy_name_medium', 'score' => $bestScore];
        }

        // cyfry w tytule jako id umowy
        preg_match_all('/(\d+)/', $transaction->title, $matches);
        if (!empty($matches[0])) {
            foreach ($matches[0] as $potentialId) {
                $contract = Contract::find($potentialId);
                if ($contract) {
                    return ['contract' => $contract, 'confidence' => 'high', 'reason' => 'id_in_title'];
                }
            }
        }

        return ['contract' => null, 'confidence' => 'none', 'reason' => 'no_match'];
    }

    private function parseKeyValueTitle(string $title): array
    {
        $result = [];
        if (str_contains($title, '=')) {
            $parts = preg_split('/[;\n\r]+/', $title);
            foreach ($parts as $part) {
                $part = trim($part);
                if ($part === '') continue;
                if (strpos($part, '=') !== false) {
                    [$k, $v] = explode('=', $part, 2);
                    $k = strtoupper(trim($k));
                    $v = trim($v);
                    $result[$k] = $v;
                }
            }
        }
        return $result;
    }
}
