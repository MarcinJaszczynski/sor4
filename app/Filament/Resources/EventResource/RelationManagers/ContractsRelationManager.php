<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use App\Jobs\GenerateContractJob;
use App\Models\ContractTemplate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContractsRelationManager extends RelationManager
{
    protected static string $relationship = 'contracts';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dane umowy')
                    ->schema([
                        Forms\Components\TextInput::make('contract_number')
                            ->label('Numer umowy')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->compact(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('contract_number')
            ->columns([
                Tables\Columns\TextColumn::make('contract_number')
                    ->label('Numer umowy')
                    ->searchable()
                    ->description(fn ($record) => $record->date_issued ? 'Wystawiono: ' . $record->date_issued->format('d.m.Y') : null),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'generated' => 'info',
                        'signed' => 'success',
                        'cancelled' => 'danger',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Szkic',
                        'generated' => 'Wygenerowana',
                        'signed' => 'Podpisana',
                        'cancelled' => 'Anulowana',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('generate_contract')
                    ->label('Generuj umowę')
                    ->icon('heroicon-o-document-text')
                    ->form([
                        Forms\Components\Select::make('contract_template_id')
                            ->label('Szablon umowy')
                            ->options(fn() => ContractTemplate::all()->pluck('name', 'id'))
                            ->required(),
                        Forms\Components\TextInput::make('contract_number')
                            ->label('Numer umowy')
                            ->default(fn () => 'UM/' . date('Y') . '/' . rand(100, 999))
                            ->required(),
                        Forms\Components\DatePicker::make('date_issued')
                            ->label('Data wystawienia')
                            ->default(now())
                            ->required(),
                        Forms\Components\TextInput::make('place_issued')
                            ->label('Miejsce wystawienia')
                            ->default('Warszawa'),
                    ])
                    ->action(function (array $data) {
                        $event = $this->getOwnerRecord();

                        GenerateContractJob::dispatchSync(
                            $event->id,
                            (int) $data['contract_template_id'],
                            (string) $data['contract_number'],
                            $data['date_issued'],
                            (string) $data['place_issued']
                        );

                        Notification::make()
                            ->title('Generowanie umowy')
                            ->success()
                            ->body('Zadanie generowania umowy zostało wysłane do kolejki. Po ukończeniu znajdziesz umowę w sekcji Umowy.')
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('tasks')
                    ->label('Zadania')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('warning')
                    ->hiddenLabel()
                    ->tooltip('Zadania')
                    ->modalContent(fn ($record) => view('livewire.task-manager-wrapper', ['taskable' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false),
                Tables\Actions\Action::make('edit')
                    ->icon('heroicon-o-pencil')
                    ->hiddenLabel()
                    ->tooltip('Edytuj')
                    ->url(fn ($record) => \App\Filament\Resources\ContractResource::getUrl('edit', ['record' => $record])),
                Tables\Actions\Action::make('print')
                    ->icon('heroicon-o-printer')
                    ->hiddenLabel()
                    ->tooltip('Drukuj')
                    ->url(fn ($record) => route('client.contract', $record->uuid))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->hiddenLabel()
                    ->tooltip('Podgląd')
                    ->modalWidth('6xl')
                    ->modalContent(fn ($record) => view('filament.modals.contract-preview', ['contract' => $record])),
                Tables\Actions\DeleteAction::make()
                    ->hiddenLabel(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
