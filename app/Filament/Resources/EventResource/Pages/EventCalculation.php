<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Filament\Resources\EventResource\Traits\HasEventHeaderActions;
use Filament\Resources\Pages\Page;
use App\Models\Event;
use Filament\Actions;
use App\Services\EventPriceCalculator;

class EventCalculation extends Page
{
    use HasEventHeaderActions;

    protected static string $resource = EventResource::class;
    protected static string $view = 'filament.resources.event-resource.pages.event-calculation';

    public Event $record;

    public function mount($record): void
    {
        if (is_array($record) && isset($record['id'])) {
            $this->record = Event::with(['bus', 'markup', 'programPoints.templatePoint'])->findOrFail($record['id']);
        } elseif ($record instanceof Event) {
            // Jeśli już jest modelem, załaduj relacje
            $this->record = $record->load(['bus', 'markup', 'programPoints.templatePoint']);
        } else {
            $this->record = Event::with(['bus', 'markup', 'programPoints.templatePoint'])->findOrFail($record);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            ...$this->getNavigationActions(),
            
            Actions\Action::make('create_snapshot')
                ->label('Utwórz snapshot')
                ->icon('heroicon-o-camera')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\TextInput::make('name')
                        ->label('Nazwa snapshotu')
                        ->required()
                        ->maxLength(255)
                        ->default('Snapshot kalkulacji ' . now()->format('d.m.Y H:i')),
                    
                    \Filament\Forms\Components\Textarea::make('description')
                        ->label('Opis')
                        ->rows(3)
                        ->maxLength(500)
                        ->helperText('Opisz powód utworzenia tego snapshotu'),
                ])
                ->action(function (array $data) {
                    $this->record->createManualSnapshot($data['name'], $data['description']);
                    
                    \Filament\Notifications\Notification::make()
                        ->title('Snapshot utworzony')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('recalculate_event')
                ->label('Przelicz dla imprezy')
                ->icon('heroicon-o-calculator')
                ->color('primary')
                ->requiresConfirmation()
                ->action(function () {
                    $calculator = new EventPriceCalculator();
                    $calculator->calculateForEvent($this->record);

                    \Filament\Notifications\Notification::make()
                        ->title('Kalkulacja wykonana')
                        ->success()
                        ->send();

                    $this->redirect(request()->fullUrl());
                }),
            Actions\Action::make('edit_prices')
                ->label('Edytuj ceny')
                ->icon('heroicon-o-currency-dollar')
                ->url(fn() => static::getResource()::getUrl('edit', ['record' => $this->record->id]) . '#price-per-person')
                ->color('primary'),
        ];
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Resources\EventResource\Widgets\EventPriceTable::class,
        ];
    }
}
