<?php

namespace App\Filament\Resources\EmailMessageResource\Pages;

use App\Filament\Resources\EmailMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\User;
use App\Services\EmailService;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use App\Notifications\EmailSharedNotification;
use App\Services\NotificationService as AppNotificationService;

class EditEmailMessage extends EditRecord
{
    protected static string $resource = EmailMessageResource::class;

    protected array $originalShared = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('forward')
                ->label('Prześlij dalej')
                ->icon('heroicon-o-arrow-right')
                ->form([
                    Actions\Forms\TextInput::make('to')->label('Do')->required(),
                    Actions\Forms\RichEditor::make('body')->label('Treść')->required()
                ])
                ->action(function (array $data) {
                    $service = app(EmailService::class);
                    $service->send(
                        $this->record->account,
                        $data['to'],
                        'Fwd: ' . $this->record->subject,
                        $data['body']
                    );

                    Notification::make()->title('Wiadomość przesłana dalej')->success()->send();
                }),

            Actions\DeleteAction::make(),
        ];
    }

    public function mount(string|int $record): void
    {
        parent::mount($record);

        $this->originalShared = $this->record->sharedUsers()->pluck('id')->toArray();
    }

    protected function afterSave(): void
    {
        parent::afterSave();

        $current = $this->record->sharedUsers()->pluck('id')->toArray();
        $added = array_diff($current, $this->originalShared);

        if (! empty($added)) {
            $service = app(EmailService::class);
            foreach ($added as $userId) {
                $user = User::find($userId);
                if (! $user || ! $user->email) {
                    continue;
                }

                $body = "Wiadomość została udostępniona przez " . auth()->user()->name . ".\n\n";
                $body .= "Temat: " . $this->record->subject . "\n\n";
                $body .= "Link: " . url(EmailMessageResource::getUrl('edit', ['record' => $this->record->id])) . "\n\n";
                $body .= strip_tags($this->record->body_html);

                // Send notification email to the user
                try {
                    $service->send($this->record->account, $user->email, 'Wiadomość udostępniona: ' . $this->record->subject, $body);
                } catch (\Throwable $e) {
                    // swallow errors to not block the UI; log if needed
                }

                // Create a database notification and clear cache for the user
                try {
                    NotificationFacade::send($user, new EmailSharedNotification($this->record->id, $this->record->subject));
                    AppNotificationService::clearCacheForUser($user->id);

                    // Also create a Filament DatabaseNotification so it appears in Filament notification center
                    $filamentData = [
                        'title' => 'Wiadomość udostępniona',
                        'body' => 'Wiadomość "' . $this->record->subject . '" została Ci udostępniona.',
                        'view' => 'filament-notifications::notification',
                        'viewData' => [],
                        'format' => 'filament',
                        'icon' => 'heroicon-o-mail',
                        'iconColor' => 'primary',
                        'status' => 'info',
                    ];

                    NotificationFacade::send($user, new \Filament\Notifications\DatabaseNotification($filamentData));
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        }
    }
}
