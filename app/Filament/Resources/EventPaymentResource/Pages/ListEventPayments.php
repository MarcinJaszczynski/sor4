<?php

namespace App\Filament\Resources\EventPaymentResource\Pages;

use App\Filament\Resources\EventPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEventPayments extends ListRecords
{
    protected static string $resource = EventPaymentResource::class;

    public ?string $quickEventCode = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('goParticipantsFromPayments')
                ->label('Uczestnicy z wpłat (kod imprezy)')
                ->icon('heroicon-o-user-group')
                ->color('gray')
                ->form([
                    \Filament\Forms\Components\TextInput::make('event_code')
                        ->label('Kod imprezy (public_code)')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $code = (string) ($data['event_code'] ?? '');
                    $this->redirect(\App\Filament\Pages\Participants\GenerateParticipantsFromPayments::getUrl([
                        'match_mode' => 'event_code',
                        'key' => $code,
                    ]));
                }),
            Actions\Action::make('goParticipantsImportCsv')
                ->label('Import uczestników (CSV) (kod imprezy)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->form([
                    \Filament\Forms\Components\TextInput::make('event_code')
                        ->label('Kod imprezy (public_code)')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $code = (string) ($data['event_code'] ?? '');
                    $this->redirect(\App\Filament\Pages\Participants\ImportParticipants::getUrl([
                        'match_mode' => 'event_code',
                        'key' => $code,
                    ]));
                }),
        ];
    }
}
