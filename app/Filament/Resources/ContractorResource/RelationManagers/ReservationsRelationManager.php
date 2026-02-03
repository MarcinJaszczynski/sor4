<?php

namespace App\Filament\Resources\ContractorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\EventResource;

class ReservationsRelationManager extends RelationManager
{
    protected static string $relationship = 'reservations';
    protected static ?string $title = 'Rezerwacje u tego kontrahenta';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('event_id')
                    ->label('Impreza')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                
                Forms\Components\Select::make('event_program_point_id')
                    ->label('Punkt programu')
                    ->relationship('programPoint', 'name')
                    ->searchable()
                    ->preload(),

                Forms\Components\Fieldset::make('Finanse')
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
                    ]),
                
                Forms\Components\Textarea::make('notes')
                    ->label('Uwagi')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('event.name')
                    ->label('Impreza')
                    ->searchable()
                    ->url(fn ($record) => EventResource::getUrl('edit', ['record' => $record->event_id])),
                
                Tables\Columns\TextColumn::make('event.status')
                    ->label('Status Imprezy')
                    ->badge()
                     ->colors([
                        'danger' => 'cancelled',
                        'warning' => 'draft',
                        'success' => ['confirmed', 'completed'],
                        'info' => 'planned',
                    ]),

                Tables\Columns\TextColumn::make('programPoint.name')
                    ->label('Punkt programu'),

                Tables\Columns\TextColumn::make('cost')
                    ->label('Koszt')
                    ->money('PLN'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status rezerwacji')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'info' => 'paid',
                        'danger' => 'cancelled',
                    ]),
                
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Termin')
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
