<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Contractor;
use App\Models\EventProgramPoint;

class ReservationsRelationManager extends RelationManager
{
    protected static string $relationship = 'reservations';
    protected static ?string $title = 'Rezerwacje';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Podstawowe informacje')
                    ->schema([
                        Forms\Components\Select::make('contractor_id')
                            ->label('Kontrahent')
                            ->relationship('contractor', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nazwa firmy / Nazwisko')
                                    ->required(),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->label('Email'),
                                Forms\Components\TextInput::make('phone')
                                    ->tel()
                                    ->label('Telefon'),
                                Forms\Components\TextInput::make('street')
                                    ->label('Ulica'),
                                Forms\Components\TextInput::make('city')
                                    ->label('Miasto'),
                            ])
                            ->required()
                            ->createOptionUsing(function (array $data) {
                                return Contractor::create($data);
                            }),

                        Forms\Components\Select::make('event_program_point_id')
                            ->label('Punkt programu')
                            ->options(function (RelationManager $livewire) {
                                return $livewire->getOwnerRecord()->programPoints()->pluck('name', 'id');
                            })
                            ->searchable(),
                    ])
                    ->columns(2)
                    ->compact(),

                Forms\Components\Section::make('Finanse i status')
                    ->schema([
                        Forms\Components\TextInput::make('cost')
                            ->label('Koszt')
                            ->numeric()
                            ->suffix('PLN'),
                        Forms\Components\TextInput::make('advance_payment')
                            ->label('Zaliczka')
                            ->numeric()
                            ->suffix('PLN'),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Oczekuje',
                                'confirmed' => 'Potwierdzone',
                                'paid' => 'Opłacone',
                                'cancelled' => 'Anulowane',
                            ])
                            ->default('pending')
                            ->required(),
                        Forms\Components\DatePicker::make('due_date')
                            ->label('Termin płatności/realizacji'),
                    ])
                    ->columns(4)
                    ->compact(),

                Forms\Components\Section::make('Uwagi')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Uwagi')
                            ->rows(3),
                    ])
                    ->collapsed()
                    ->compact(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('contractor.name')
                    ->label('Kontrahent')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => $state ?? '—')
                    ->description(
                        fn($record) =>
                        collect([
                            $record->contractor?->phone,
                            $record->contractor?->email,
                        ])->filter()->join(' | ')
                    ),
                Tables\Columns\TextColumn::make('programPoint.name')
                    ->label('Punkt programu')
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('financial')
                    ->label('Finanse')
                    ->formatStateUsing(
                        fn($record) =>
                        'Koszt: ' . number_format($record->cost ?? 0, 2) . ' PLN' .
                            ($record->advance_payment ? ' | Zaliczka: ' . number_format($record->advance_payment, 2) . ' PLN' : '')
                    )
                    ->description(function ($record) {
                        if (! $record->due_date) {
                            return null;
                        }

                        // If due_date is a string, try to parse it to Carbon, otherwise assume it's a Date instance
                        try {
                            $date = is_string($record->due_date)
                                ? \Illuminate\Support\Carbon::parse($record->due_date)
                                : $record->due_date;

                            return 'Termin: ' . $date->format('d.m.Y');
                        } catch (\Throwable $e) {
                            return null;
                        }
                    })
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'info' => 'paid',
                        'danger' => 'cancelled',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Oczekuje',
                        'confirmed' => 'Potwierdzone',
                        'paid' => 'Opłacone',
                        'cancelled' => 'Anulowane',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->hiddenLabel()
                    ->tooltip('Dodaj rezerwację'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hiddenLabel(),
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
