<?php

namespace App\Filament\Resources\ContractResource\Pages;

use App\Filament\Resources\ContractResource;
use App\Filament\Pages\Finance\InstallmentControl;
use Illuminate\Support\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContract extends EditRecord
{
    protected static string $resource = ContractResource::class;

    protected function getHeaderActions(): array
    {
        $contract = $this->record;
        $today = now()->startOfDay();

        $overdueAmount = $contract->installments()
            ->where('is_paid', false)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today)
            ->sum('amount');

        $nextInstallment = $contract->installments()
            ->where('is_paid', false)
            ->whereNotNull('due_date')
            ->orderBy('due_date')
            ->first();

        $nextLabel = $nextInstallment
            ? (Carbon::parse($nextInstallment->due_date)->format('d.m.Y') . ' • ' . number_format((float) $nextInstallment->amount, 2, ',', ' ') . ' PLN')
            : 'brak';

        return [
            Actions\Action::make('installmentSummary')
                ->label('Zaległe: ' . number_format((float) $overdueAmount, 2, ',', ' ') . ' PLN • Najbliższa: ' . $nextLabel)
                ->icon('heroicon-o-exclamation-circle')
                ->color($overdueAmount > 0 ? 'danger' : 'gray')
                ->disabled(),

            Actions\Action::make('tasks')
                ->label('Zadania (umowa)')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('gray')
                ->url(fn () => \App\Filament\Resources\TaskResource::getUrl('index', [
                    'q' => $this->record->contract_number,
                ]))
                ->openUrlInNewTab(),

            Actions\Action::make('installmentTasks')
                ->label('Zadania (raty)')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('gray')
                ->url(fn () => \App\Filament\Resources\TaskResource::getUrl('index', [
                    'installments' => 1,
                    'event_code' => (string) ($this->record->event?->public_code ?? ''),
                    'q' => $this->record->contract_number,
                ]))
                ->openUrlInNewTab(),

            Actions\Action::make('installmentsControl')
                ->label('Raty / przypomnienia')
                ->icon('heroicon-o-calendar-days')
                ->color('info')
                ->url(fn () => InstallmentControl::getUrl([
                    'scope' => 'all',
                    'contract_number' => $this->record->contract_number,
                ]))
                ->openUrlInNewTab(),
            Actions\Action::make('participantsFromPayments')
                ->label('Uczestnicy z wpłat')
                ->icon('heroicon-o-user-group')
                ->color('gray')
                ->url(fn () => \App\Filament\Pages\Participants\GenerateParticipantsFromPayments::getUrl([
                    'match_mode' => 'contract_number',
                    'key' => $this->record->contract_number,
                ]))
                ->openUrlInNewTab(),
            Actions\Action::make('participantsImportCsv')
                ->label('Import uczestników (CSV)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->url(fn () => \App\Filament\Pages\Participants\ImportParticipants::getUrl([
                    'match_mode' => 'contract_number',
                    'key' => $this->record->contract_number,
                ]))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }
}
