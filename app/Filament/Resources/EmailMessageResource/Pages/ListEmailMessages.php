<?php

namespace App\Filament\Resources\EmailMessageResource\Pages;

use App\Filament\Resources\EmailMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmailMessages extends ListRecords
{
    protected static string $resource = EmailMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sync')
                ->label('Synchronizuj')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function () {
                    \Illuminate\Support\Facades\Artisan::call('emails:sync');
                    \Filament\Notifications\Notification::make()
                        ->title('Synchronizacja zakończona')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('send')
                ->label('Nowa wiadomość')
                ->icon('heroicon-o-paper-airplane')
                ->form([
                    \Filament\Forms\Components\Select::make('email_account_id')
                        ->label('Z konta')
                        ->options(\App\Models\EmailAccount::pluck('account_name', 'id'))
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('to')
                        ->label('Do')
                        ->email()
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('subject')
                        ->label('Temat')
                        ->required(),
                    \Filament\Forms\Components\RichEditor::make('body')
                        ->label('Treść')
                        ->required(),
                ])
                ->action(function (array $data, \App\Services\EmailService $service) {
                    $account = \App\Models\EmailAccount::find($data['email_account_id']);
                    $service->send(
                        $account,
                        $data['to'],
                        $data['subject'],
                        $data['body']
                    );
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Wiadomość wysłana')
                        ->success()
                        ->send();
                }),
        ];
    }
}
