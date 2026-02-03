<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Filament\Resources\EventPaymentResource;
use App\Filament\Resources\EventResource;
use App\Filament\Resources\ContractResource;
use App\Filament\Resources\EventTemplateResource;
use App\Filament\Pages\Finance\InstallmentControl;
use App\Models\Contract;
use App\Models\Event;
use App\Models\EventTemplate;
use App\Models\EventProgramPoint;
use App\Models\EventTemplateProgramPoint;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\TaskAttachment;
use Illuminate\Support\Str;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        $task = $this->record;
        $taskable = $task?->taskable;

        $event = null;
        $contract = null;
        $template = null;
        $programPoint = null;
        $templateProgramPoint = null;

        // Określ typ powiązania i ekstrahuj powiązane encje
        if ($taskable instanceof Contract) {
            $contract = $taskable;
            $event = $contract->event;
        } elseif ($taskable instanceof Event) {
            $event = $taskable;
        } elseif ($taskable instanceof EventTemplate) {
            $template = $taskable;
        } elseif ($taskable instanceof EventProgramPoint) {
            $programPoint = $taskable;
            $event = $programPoint->event;
        } elseif ($taskable instanceof EventTemplateProgramPoint) {
            $templateProgramPoint = $taskable;
            $template = $templateProgramPoint->eventTemplate;
        }

        $installmentContractNumber = null;
        $description = (string) ($task?->description ?? '');
        if (preg_match('/\[installment:(\d+)\]/', $description, $m)) {
            $installmentContractNumber = $contract?->contract_number;
        }

        return [
            // Zadanie nadrzędne
            Actions\Action::make('openParent')
                ->label('Zadanie nadrzędne')
                ->icon('heroicon-o-arrow-up-circle')
                ->color('info')
                ->visible(fn () => (bool) $task->parent_id)
                ->url(fn () => TaskResource::getUrl('edit', ['record' => $task->parent_id]))
                ->openUrlInNewTab(),

            // Szablon imprezy
            Actions\Action::make('openTemplate')
                ->label('Szablon imprezy')
                ->icon('heroicon-o-document-duplicate')
                ->color('success')
                ->visible(fn () => (bool) $template?->id)
                ->url(fn () => EventTemplateResource::getUrl('edit', ['record' => $template->id]))
                ->openUrlInNewTab(),

            // Punkt szablonu
            Actions\Action::make('openTemplateProgramPoint')
                ->label('Punkt szablonu')
                ->icon('heroicon-o-map-pin')
                ->color('success')
                ->visible(fn () => (bool) $templateProgramPoint?->id)
                ->url(fn () => $template 
                    ? EventTemplateResource::getUrl('edit', ['record' => $template->id]) . '#program'
                    : '#'
                )
                ->openUrlInNewTab(),

            // Punkt programu imprezy
            Actions\Action::make('openProgramPoint')
                ->label('Punkt programu')
                ->icon('heroicon-o-map-pin')
                ->color('warning')
                ->visible(fn () => (bool) $programPoint?->id)
                ->url(fn () => $event 
                    ? EventResource::getUrl('edit', ['record' => $event->id]) . '#program'
                    : '#'
                )
                ->openUrlInNewTab(),

            // Umowa
            Actions\Action::make('openContract')
                ->label('Umowa')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->visible(fn () => (bool) $contract?->id)
                ->url(fn () => ContractResource::getUrl('edit', ['record' => $contract->id]))
                ->openUrlInNewTab(),

            // Impreza
            Actions\Action::make('openEvent')
                ->label('Impreza')
                ->icon('heroicon-o-calendar')
                ->color('primary')
                ->visible(fn () => (bool) $event?->id)
                ->url(fn () => EventResource::getUrl('edit', ['record' => $event->id]))
                ->openUrlInNewTab(),

            // Wpłaty
            Actions\Action::make('openPayments')
                ->label('Wpłaty')
                ->icon('heroicon-o-banknotes')
                ->color('gray')
                ->visible(fn () => (bool) $event?->id)
                ->url(fn () => EventPaymentResource::getUrl('index', [
                    'tableFilters' => [
                        'event' => [
                            'value' => $event->id,
                        ],
                    ],
                ]))
                ->openUrlInNewTab(),

            // Raty
            Actions\Action::make('openInstallments')
                ->label('Raty / przypomnienia')
                ->icon('heroicon-o-calendar-days')
                ->color('info')
                ->visible(fn () => (bool) $contract?->contract_number)
                ->url(fn () => InstallmentControl::getUrl([
                    'scope' => 'all',
                    'contract_number' => $contract->contract_number,
                ]))
                ->openUrlInNewTab(),

            Actions\Action::make('uploadAttachment')
                ->label('Dodaj załącznik')
                ->icon('heroicon-o-paper-clip')
                ->form([
                    FileUpload::make('attachment')
                        ->label('Plik')
                        ->directory('task-attachments')
                        ->disk('public')
                        ->preserveFilenames()
                        ->acceptedFileTypes(['image/*', 'application/pdf', 'application/zip'])
                        ->maxSize(10240)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $file = $data['attachment'];
                    if (! $file) {
                        return;
                    }
                    $path = Storage::disk('public')->putFile('task-attachments', $file);
                    TaskAttachment::create([
                        'task_id' => $this->record->id,
                        'user_id' => Auth::id(),
                        'name' => $file->getClientOriginalName() ?? basename($path),
                        'file_path' => $path,
                        'mime_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
} 
