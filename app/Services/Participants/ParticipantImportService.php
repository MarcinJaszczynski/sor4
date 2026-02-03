<?php

namespace App\Services\Participants;

use App\Models\Contract;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ParticipantImportService
{
    public function importCsv(string $absoluteFilePath, array $options): array
    {
        $matchMode = (string) ($options['match_mode'] ?? 'contract_number');
        $key = trim((string) ($options['key'] ?? ''));
        $delimiter = (string) ($options['delimiter'] ?? ';');
        $hasHeader = (bool) ($options['has_header'] ?? true);
        $dryRun = (bool) ($options['dry_run'] ?? false);
        $updateExisting = (bool) ($options['update_existing'] ?? true);

        if (!in_array($matchMode, ['contract_number', 'event_code'], true)) {
            return ['ok' => false, 'error' => 'Nieprawidłowy tryb dopasowania.'];
        }

        if ($key === '') {
            return ['ok' => false, 'error' => 'Brak klucza (kod imprezy lub numer umowy).'];
        }

        $event = null;
        $defaultContract = null;

        if ($matchMode === 'event_code') {
            $event = Event::query()->where('public_code', $key)->first();
            if (!$event) {
                return ['ok' => false, 'error' => 'Nie znaleziono imprezy o podanym kodzie.'];
            }

            $defaultContract = $event->contracts()->orderByDesc('total_amount')->first();
        }

        if (!is_file($absoluteFilePath)) {
            return ['ok' => false, 'error' => 'Nie znaleziono pliku CSV.'];
        }

        $handle = fopen($absoluteFilePath, 'rb');
        if ($handle === false) {
            return ['ok' => false, 'error' => 'Nie można otworzyć pliku CSV.'];
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        $header = [];
        $rowIndex = 0;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowIndex++;

            if ($rowIndex === 1 && $hasHeader) {
                $header = $this->normalizeHeader($row);
                continue;
            }

            if (count($row) === 1 && trim((string) $row[0]) === '') {
                continue;
            }

            $data = $hasHeader
                ? $this->rowToAssoc($header, $row)
                : $this->rowToAssoc($this->fallbackHeader(count($row)), $row);

            $mapped = $this->mapRow($data);

            $firstName = trim((string) ($mapped['first_name'] ?? ''));
            $lastName = trim((string) ($mapped['last_name'] ?? ''));

            if ($firstName === '' && $lastName === '') {
                $errors[] = "Wiersz {$rowIndex}: brak imienia i nazwiska";
                continue;
            }

            $contract = null;

            if ($matchMode === 'contract_number') {
                $contractNumber = trim((string) ($mapped['contract_number'] ?? $key));
                if ($contractNumber === '') {
                    $errors[] = "Wiersz {$rowIndex}: brak numeru umowy";
                    continue;
                }

                $contract = Contract::query()->where('contract_number', $contractNumber)->first();
                if (!$contract) {
                    $errors[] = "Wiersz {$rowIndex}: nie znaleziono umowy {$contractNumber}";
                    continue;
                }
            } else {
                // event_code
                $contractNumber = trim((string) ($mapped['contract_number'] ?? ''));

                if ($contractNumber !== '') {
                    $contract = Contract::query()
                        ->where('event_id', $event->id)
                        ->where('contract_number', $contractNumber)
                        ->first();

                    if (!$contract) {
                        $errors[] = "Wiersz {$rowIndex}: nie znaleziono umowy {$contractNumber} w tej imprezie";
                        continue;
                    }
                } else {
                    if (!$defaultContract) {
                        $errors[] = "Wiersz {$rowIndex}: impreza nie ma żadnej umowy (nie da się przypisać uczestnika)";
                        continue;
                    }
                    $contract = $defaultContract;
                }
            }

            $email = $this->nullIfEmpty($mapped['email'] ?? null);
            $pesel = $this->nullIfEmpty($mapped['pesel'] ?? null);

            $existingQuery = Participant::query()->where('contract_id', $contract->id);
            $existing = null;

            if ($pesel) {
                $existing = (clone $existingQuery)->where('pesel', $pesel)->first();
            } elseif ($email) {
                $existing = (clone $existingQuery)->where('email', $email)->first();
            } else {
                $existing = (clone $existingQuery)
                    ->where('first_name', $firstName)
                    ->where('last_name', $lastName)
                    ->first();
            }

            $payload = Arr::only($mapped, [
                'first_name',
                'last_name',
                'email',
                'phone',
                'pesel',
                'birth_date',
                'status',
                'diet_info',
                'seat_number',
                'gender',
                'nationality',
                'document_type',
                'document_number',
                'document_expiry_date',
                'room_type',
                'room_notes',
            ]);

            $payload = array_filter($payload, fn ($v) => !($v === null || $v === ''));

            if ($existing) {
                if (!$updateExisting) {
                    $skipped++;
                    continue;
                }

                if (!$dryRun) {
                    $existing->fill($payload);
                    $existing->save();
                }

                $updated++;
                continue;
            }

            if (!$dryRun) {
                Participant::create(array_merge(
                    ['contract_id' => $contract->id],
                    $payload,
                ));
            }

            $created++;
        }

        fclose($handle);

        return [
            'ok' => true,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => array_slice($errors, 0, 50),
        ];
    }

    private function nullIfEmpty($value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function normalizeHeader(array $row): array
    {
        return array_map(function ($h) {
            $h = Str::ascii((string) $h);
            $h = mb_strtolower(trim($h));
            $h = preg_replace('/[^a-z0-9]+/u', '_', $h);
            $h = trim((string) $h, '_');
            return $h;
        }, $row);
    }

    private function fallbackHeader(int $count): array
    {
        $h = [];
        for ($i = 0; $i < $count; $i++) {
            $h[] = 'col_' . ($i + 1);
        }
        return $h;
    }

    private function rowToAssoc(array $header, array $row): array
    {
        $assoc = [];
        foreach ($header as $i => $key) {
            $assoc[$key] = $row[$i] ?? null;
        }
        return $assoc;
    }

    private function mapRow(array $row): array
    {
        $get = function (array $keys) use ($row) {
            foreach ($keys as $k) {
                if (array_key_exists($k, $row) && trim((string) $row[$k]) !== '') {
                    return $row[$k];
                }
            }
            return null;
        };

        return [
            'contract_number' => $get(['contract_number', 'nr_umowy', 'umowa', 'numer_umowy', 'nr_umowy_umowa']),
            'first_name' => $get(['first_name', 'imie', 'imię', 'firstname']),
            'last_name' => $get(['last_name', 'nazwisko', 'lastname', 'surname']),
            'email' => $get(['email', 'e_mail', 'mail']),
            'phone' => $get(['phone', 'telefon', 'tel', 'nr_tel']),
            'pesel' => $get(['pesel']),
            'birth_date' => $get(['birth_date', 'data_urodzenia', 'data_urodzenia_yyyy_mm_dd']),
            'status' => $get(['status']),
            'diet_info' => $get(['diet_info', 'dieta', 'uwagi_dietetyczne']),
            'seat_number' => $get(['seat_number', 'miejsce', 'nr_miejsca']),
            'gender' => $get(['gender', 'plec', 'płeć']),
            'nationality' => $get(['nationality', 'obywatelstwo']),
            'document_type' => $get(['document_type', 'typ_dokumentu']),
            'document_number' => $get(['document_number', 'nr_dokumentu', 'numer_dokumentu']),
            'document_expiry_date' => $get(['document_expiry_date', 'data_waznosci_dokumentu']),
            'room_type' => $get(['room_type', 'pokoj', 'typ_pokoju']),
            'room_notes' => $get(['room_notes', 'uwagi_pokoj', 'uwagi_pokojowe']),
        ];
    }
}
