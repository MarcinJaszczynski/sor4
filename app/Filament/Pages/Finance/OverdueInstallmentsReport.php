<?php

namespace App\Filament\Pages\Finance;

use App\Models\ContractInstallment;
use App\Models\User;
use App\Services\Finance\OverdueInstallmentsReportService;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class OverdueInstallmentsReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Finanse';
    protected static ?string $navigationLabel = 'Raport zaległości';
    protected static ?string $title = 'Raport zaległych rat';
    protected static string $view = 'filament.pages.finance.overdue-installments-report';

    public ?int $assignedTo = null;
    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    public function mount(): void
    {
        $this->assignedTo = request()->query('assigned_to')
            ? (int) request()->query('assigned_to')
            : null;

        $this->dateFrom = request()->query('date_from') ?: null;
        $this->dateTo = request()->query('date_to') ?: null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Pobierz CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function (OverdueInstallmentsReportService $service) {
                    return $service->exportCsv($this->assignedTo, $this->dateFrom, $this->dateTo);
                }),
            Action::make('exportPdf')
                ->label('Pobierz PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function (OverdueInstallmentsReportService $service) {
                    return $service->exportPdf($this->assignedTo, $this->dateFrom, $this->dateTo);
                }),
        ];
    }

    public function getViewData(): array
    {
        $query = ContractInstallment::query()
            ->with(['contract.event.assignedUser'])
            ->where('is_paid', false)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->startOfDay());

        if ($this->assignedTo) {
            $query->whereHas('contract.event', fn ($q) => $q->where('assigned_to', $this->assignedTo));
        }

        if ($this->dateFrom) {
            $query->whereDate('due_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('due_date', '<=', $this->dateTo);
        }

        $rows = $query->orderBy('due_date')->get();

        $summary = $rows->groupBy(fn ($inst) => $inst->contract?->event?->assignedUser?->name ?? 'Brak opiekuna')
            ->map(fn (Collection $items) => [
                'count' => $items->count(),
                'amount' => $items->sum(fn ($i) => (float) ($i->amount ?? 0)),
            ])
            ->sortByDesc('amount');

        $assignees = User::query()->orderBy('name')->get();

        return [
            'rows' => $rows,
            'summary' => $summary,
            'assignees' => $assignees,
            'assignedTo' => $this->assignedTo,
        ];
    }
}
