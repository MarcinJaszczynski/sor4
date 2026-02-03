<?php

namespace App\Services\Participants;

use App\Models\Contract;
use App\Models\Event;
use App\Models\EventPayment;
use App\Models\Participant;
use Illuminate\Support\Str;

class ParticipantGenerationFromPaymentsService
{
    public function generate(array $options): array
    {
        $matchMode = (string) ($options['match_mode'] ?? 'contract_number');
        $key = trim((string) ($options['key'] ?? ''));
        $dryRun = (bool) ($options['dry_run'] ?? false);
        $createFromDescriptions = (bool) ($options['create_from_descriptions'] ?? true);
        $createPlaceholders = (bool) ($options['create_placeholders_to_expected'] ?? false);

        if (!in_array($matchMode, ['contract_number', 'event_code'], true)) {
            return ['ok' => false, 'error' => 'Nieprawidłowy tryb dopasowania.'];
        }

        if ($key === '') {
            return ['ok' => false, 'error' => 'Brak klucza (kod imprezy lub numer umowy).'];
        }

        $contracts = collect();
        $event = null;

        if ($matchMode === 'contract_number') {
            $contract = Contract::query()->where('contract_number', $key)->first();
            if (!$contract) {
                return ['ok' => false, 'error' => 'Nie znaleziono umowy o podanym numerze.'];
            }
            $contracts = collect([$contract]);
        } else {
            $event = Event::query()->where('public_code', $key)->first();
            if (!$event) {
                return ['ok' => false, 'error' => 'Nie znaleziono imprezy o podanym kodzie.'];
            }
            $contracts = $event->contracts()->get();
            if ($contracts->isEmpty()) {
                return ['ok' => false, 'error' => 'Impreza nie ma żadnych umów.'];
            }
        }

        $created = 0;
        $skipped = 0;
        $errors = [];

        // Preload participant names for quick dedupe
        $existingIndex = [];
        foreach ($contracts as $c) {
            $existingIndex[$c->id] = Participant::query()
                ->where('contract_id', $c->id)
                ->get(['first_name', 'last_name'])
                ->map(fn ($p) => mb_strtolower(trim($p->first_name . ' ' . $p->last_name)))
                ->filter()
                ->flip()
                ->toArray();
        }

        if ($createFromDescriptions) {
            $paymentsQuery = EventPayment::query();

            if ($matchMode === 'contract_number') {
                $contract = $contracts->first();
                $paymentsQuery->where(function ($q) use ($contract) {
                    $q->where('contract_id', $contract->id)
                        ->orWhere(function ($q2) use ($contract) {
                            $q2->where('event_id', $contract->event_id)
                                ->whereNull('contract_id')
                                ->where('description', 'like', '%' . $contract->contract_number . '%');
                        });
                });
            } else {
                $paymentsQuery->where('event_id', $event->id);
            }

            $payments = $paymentsQuery->orderBy('payment_date')->orderBy('id')->get();

            foreach ($payments as $payment) {
                $contract = null;

                if ($payment->contract_id) {
                    $contract = $contracts->firstWhere('id', (int) $payment->contract_id);
                    if (!$contract) {
                        // poza zakresem (np. błędny event), pomijamy
                        continue;
                    }
                } else {
                    $contractNumber = $this->extractContractNumberFromText((string) $payment->description);
                    if ($contractNumber) {
                        $contract = $contracts->firstWhere('contract_number', $contractNumber);
                    }

                    if (!$contract && $contracts->count() === 1) {
                        $contract = $contracts->first();
                    }
                }

                if (!$contract) {
                    $errors[] = 'Wpłata ID ' . $payment->id . ': nie udało się dopasować do umowy (brak contract_id i brak numeru w opisie)';
                    continue;
                }

                [$firstName, $lastName] = $this->parsePersonNameFromPaymentDescription((string) $payment->description);
                if ($firstName === '' && $lastName === '') {
                    $skipped++;
                    continue;
                }

                $keyName = mb_strtolower(trim($firstName . ' ' . $lastName));
                if ($keyName === '') {
                    $skipped++;
                    continue;
                }

                if (!empty($existingIndex[$contract->id][$keyName])) {
                    $skipped++;
                    continue;
                }

                if (!$dryRun) {
                    Participant::create([
                        'contract_id' => $contract->id,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'status' => 'active',
                    ]);
                }

                $existingIndex[$contract->id][$keyName] = true;
                $created++;
            }
        }

        if ($createPlaceholders) {
            foreach ($contracts as $contract) {
                $expectedCount = $this->expectedParticipantsForContract($contract);
                if ($expectedCount <= 0) {
                    continue;
                }

                $currentCount = Participant::query()->where('contract_id', $contract->id)->count();
                $missing = max(0, $expectedCount - $currentCount);

                for ($i = 0; $i < $missing; $i++) {
                    $index = $currentCount + $i + 1;
                    $firstName = 'Uczestnik';
                    $lastName = (string) $index;
                    $keyName = mb_strtolower(trim($firstName . ' ' . $lastName));

                    if (!empty($existingIndex[$contract->id][$keyName])) {
                        $skipped++;
                        continue;
                    }

                    if (!$dryRun) {
                        Participant::create([
                            'contract_id' => $contract->id,
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                            'status' => 'active',
                        ]);
                    }

                    $existingIndex[$contract->id][$keyName] = true;
                    $created++;
                }
            }
        }

        return [
            'ok' => true,
            'created' => $created,
            'skipped' => $skipped,
            'errors' => array_slice($errors, 0, 50),
        ];
    }

    private function expectedParticipantsForContract(Contract $contract): int
    {
        $total = (float) ($contract->total_amount ?? 0);
        $pp = (float) ($contract->locked_price_per_person ?? 0);
        if ($total <= 0 || $pp <= 0) {
            return 0;
        }

        $raw = $total / $pp;
        $rounded = (int) round($raw);
        if ($rounded <= 0) {
            return 0;
        }

        // zabezpieczenie przed absurdami
        return min($rounded, 200);
    }

    private function extractContractNumberFromText(string $text): ?string
    {
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        // (Umowa #ABC123) / umowa nr 123 / nr umowy 123
        $patterns = [
            '/\(\s*umowa\s*#\s*([^\)]+)\)/iu',
            '/\bumowa\s*#\s*([a-z0-9\-\/]+)\b/iu',
            '/\bumowa\s*(nr|numer)?\s*[:#]?\s*([a-z0-9\-\/]+)\b/iu',
            '/\bnr\s*umowy\s*[:#]?\s*([a-z0-9\-\/]+)\b/iu',
        ];

        foreach ($patterns as $p) {
            if (preg_match($p, $text, $m)) {
                $val = trim((string) ($m[2] ?? $m[1] ?? ''));
                return $val !== '' ? $val : null;
            }
        }

        return null;
    }

    private function parsePersonNameFromPaymentDescription(string $description): array
    {
        $d = trim($description);
        if ($d === '') {
            return ['', ''];
        }

        // usuń fragmenty z umową / nawiasy
        $d = preg_replace('/\(\s*umowa\s*#.*?\)/iu', ' ', $d);
        $d = preg_replace('/\(.*?\)/u', ' ', (string) $d);

        // usuń częste słowa kluczowe
        $d = Str::ascii($d);
        $d = mb_strtolower($d);
        $d = str_replace(['zaliczka', 'wplata', 'wpata', 'platnosc', 'płatność', 'impreza', 'faktura', 'fv'], ' ', $d);

        // zostaw litery/spacje
        $d = preg_replace('/[^a-z\s\-]+/u', ' ', (string) $d);
        $d = preg_replace('/\s+/u', ' ', trim((string) $d));

        $parts = array_values(array_filter(explode(' ', $d), fn ($p) => trim($p) !== ''));
        if (count($parts) < 2) {
            return ['', ''];
        }

        // Heurystyka: imię + reszta jako nazwisko
        $first = Str::title(array_shift($parts));
        $last = Str::title(implode(' ', $parts));

        if ($first === '' || $last === '') {
            return ['', ''];
        }

        return [$first, $last];
    }
}
