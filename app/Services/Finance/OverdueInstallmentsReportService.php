<?php

namespace App\Services\Finance;

use App\Models\ContractInstallment;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OverdueInstallmentsReportService
{
    public function exportCsv(?int $assignedTo = null, ?string $dateFrom = null, ?string $dateTo = null): StreamedResponse
    {
        $filename = 'zaleglosci_raty_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($assignedTo) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['Raport zaległych rat', now()->format('Y-m-d H:i')], ';');
            fputcsv($out, [] , ';');
            fputcsv($out, ['Opiekun', 'Kod imprezy', 'Impreza', 'Umowa', 'Termin', 'Kwota'], ';');

            $query = ContractInstallment::query()
                ->with(['contract.event.assignedUser'])
                ->where('is_paid', false)
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', now()->startOfDay());

            if ($assignedTo) {
                $query->whereHas('contract.event', fn ($q) => $q->where('assigned_to', $assignedTo));
            }

            if ($dateFrom) {
                $query->whereDate('due_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('due_date', '<=', $dateTo);
            }

            foreach ($query->orderBy('due_date')->get() as $inst) {
                $event = $inst->contract?->event;
                fputcsv($out, [
                    $event?->assignedUser?->name ?? '-',
                    $event?->public_code ?? '-',
                    $event?->name ?? '-',
                    $inst->contract?->contract_number ?? '-',
                    $inst->due_date?->format('Y-m-d') ?? '-',
                    number_format((float) ($inst->amount ?? 0), 2, ',', ' '),
                ], ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportPdf(?int $assignedTo = null, ?string $dateFrom = null, ?string $dateTo = null): \Symfony\Component\HttpFoundation\Response
    {
        $query = ContractInstallment::query()
            ->with(['contract.event.assignedUser'])
            ->where('is_paid', false)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->startOfDay());

        if ($assignedTo) {
            $query->whereHas('contract.event', fn ($q) => $q->where('assigned_to', $assignedTo));
        }

        if ($dateFrom) {
            $query->whereDate('due_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('due_date', '<=', $dateTo);
        }

        $rows = $query->orderBy('due_date')->get();

        $html = view('reports.overdue-installments-pdf', [
            'rows' => $rows,
            'generatedAt' => now()->format('Y-m-d H:i'),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ])->render();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="zaleglosci_raty_' . now()->format('Ymd_His') . '.pdf"',
        ]);
    }
}
