<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Uczestnicy';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Imię i Nazwisko')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->label(fn (string $operation) => $operation === 'create' ? 'Hasło' : 'Zmień hasło (opcjonalnie)'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('email')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Użytkownik')
                    ->description(fn ($record) => $record->email)
                    ->searchable(['name', 'email']),
                
                Tables\Columns\TextColumn::make('pivot.role')
                    ->label('Rola')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'manager' => 'warning',
                        'participant' => 'success',
                        'pilot' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'manager' => 'Manager',
                        'participant' => 'Uczestnik',
                        'pilot' => 'Pilot',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('pivot.role')
                    ->label('Rola')
                    ->options([
                        'participant' => 'Uczestnik',
                        'manager' => 'Manager',
                        'pilot' => 'Pilot',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('participantsFromPayments')
                    ->label('Generuj z wpłat')
                    ->icon('heroicon-o-user-group')
                    ->url(fn () => \App\Filament\Pages\Participants\GenerateParticipantsFromPayments::getUrl([
                        'match_mode' => 'event_code',
                        'key' => $this->getOwnerRecord()->public_code,
                    ])),
                Tables\Actions\Action::make('participantsImportCsv')
                    ->label('Import CSV')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->url(fn () => \App\Filament\Pages\Participants\ImportParticipants::getUrl([
                        'match_mode' => 'event_code',
                        'key' => $this->getOwnerRecord()->public_code,
                    ])),
                Tables\Actions\CreateAction::make()
                    ->label('Utwórz konto')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
                        $data['type'] = 'client'; // Default type for portal users
                        return $data;
                    }),
                Tables\Actions\AttachAction::make()
                    ->label('Dodaj istniejącego')
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\Select::make('role')
                            ->label('Rola')
                            ->options([
                                'participant' => 'Uczestnik',
                                'manager' => 'Manager',
                                'pilot' => 'Pilot',
                            ])
                            ->default('participant')
                            ->required(),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make('pivot_edit')
                    ->label('Edytuj Rolę')
                    ->icon('heroicon-m-pencil-square')
                    ->hiddenLabel()
                    ->tooltip('Edytuj rolę')
                    ->form([
                         Forms\Components\Select::make('role')
                            ->label('Rola')
                            ->options([
                                'participant' => 'Uczestnik',
                                'manager' => 'Manager',
                                'pilot' => 'Pilot',
                            ])
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->pivot->update(['role' => $data['role']]);
                    }),
                Tables\Actions\DetachAction::make()
                    ->hiddenLabel(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
