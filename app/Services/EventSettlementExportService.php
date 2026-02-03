<?php

namespace App\Services;

use App\Models\Event;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventSettlementExportService
{
    public function exportCsv(Event $event): StreamedResponse
    {
        $filename = 'rozliczenie_imprezy_' . $event->public_code . '.csv';

        return response()->streamDownload(function () use ($event) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['Impreza', $event->name], ';');
            fputcsv($out, ['Kod', $event->public_code], ';');
            fputcsv($out, ['Start', optional($event->start_date)->format('Y-m-d')], ';');
            fputcsv($out, ['Koniec', optional($event->end_date)->format('Y-m-d')], ';');
            fputcsv($out, []);

            $payments = $event->payments()->orderBy('payment_date')->get();
            $costs = $event->costs()->with('currency')->orderBy('payment_date')->get();

            $paymentsSum = (float) $payments->sum('amount');
            $costsSum = (float) $costs->sum(fn ($c) => (float) $c->amount_pln);

            fputcsv($out, ['Podsumowanie'], ';');
            fputcsv($out, ['Wpłaty (PLN)', number_format($paymentsSum, 2, ',', ' ')], ';');
            fputcsv($out, ['Koszty (PLN)', number_format($costsSum, 2, ',', ' ')], ';');
            fputcsv($out, ['Saldo (PLN)', number_format($paymentsSum - $costsSum, 2, ',', ' ')], ';');
            fputcsv($out, []);

            fputcsv($out, ['Wpłaty'], ';');
            fputcsv($out, ['Data', 'Kwota', 'Opis', 'Umowa'], ';');
            foreach ($payments as $p) {
                fputcsv($out, [
                    optional($p->payment_date)->format('Y-m-d'),
                    number_format((float) $p->amount, 2, ',', ' '),
                    $p->description,
                    $p->contract?->contract_number ?? '',
                ], ';');
            }

            fputcsv($out, []);
            fputcsv($out, ['Koszty'], ';');
            fputcsv($out, ['Data', 'Kwota PLN', 'Nazwa', 'Kontrahent'], ';');
            foreach ($costs as $c) {
                fputcsv($out, [
                    optional($c->payment_date)->format('Y-m-d'),
                    number_format((float) $c->amount_pln, 2, ',', ' '),
                    $c->name,
                    $c->contractor?->name ?? '',
                ], ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
