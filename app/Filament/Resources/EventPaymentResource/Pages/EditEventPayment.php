<?php

namespace App\Filament\Resources\EventPaymentResource\Pages;

use App\Filament\Resources\EventPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEventPayment extends EditRecord
{
    protected static string $resource = EventPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('participantsFromPayments')
                ->label('Uczestnicy z wpłat')
                ->icon('heroicon-o-user-group')
                ->color('gray')
                ->url(function () {
                    $payment = $this->record;

                    if (!empty($payment->contract?->contract_number)) {
                        return \App\Filament\Pages\Participants\GenerateParticipantsFromPayments::getUrl([
                            'match_mode' => 'contract_number',
                            'key' => $payment->contract->contract_number,
                        ]);
                    }

                    return \App\Filament\Pages\Participants\GenerateParticipantsFromPayments::getUrl([
                        'match_mode' => 'event_code',
                        'key' => $payment->event?->public_code,
                    ]);
                })
                ->openUrlInNewTab(),
            Actions\Action::make('participantsImportCsv')
                ->label('Import uczestników (CSV)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->visible(fn () => !empty($this->record->event?->public_code))
                ->url(fn () => \App\Filament\Pages\Participants\ImportParticipants::getUrl([
                    'match_mode' => 'event_code',
                    'key' => $this->record->event?->public_code,
                ]))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }
}
