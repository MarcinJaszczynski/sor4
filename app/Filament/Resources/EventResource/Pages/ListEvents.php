<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEvents extends ListRecords
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('test_sms_hybrid')
                ->label('Test SMS (Hybrid)')
                ->color('warning')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->action(function () {
                    // Pobierz zalogowanego użytkownika jako cel testu
                    $user = auth()->user();
                    
                    // Ustaw mu tymczasowo przykładowy numer, jeśli nie ma
                    if (!$user->phone) {
                         $user->phone = '500600700';
                    }

                    // Wyślij powiadomienie hybrydowe
                    $user->notify(new \App\Notifications\UrgentMeetingChangeNotification(
                        'Wycieczka Testowa', 
                        '07:30'
                    ));

                    \Filament\Notifications\Notification::make()
                        ->title('Wysłano powiadomienie hybrydowe')
                        ->body('Sprawdź logi (storage/logs/laravel.log) dla SMS oraz Mailtrap dla e-maila.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
