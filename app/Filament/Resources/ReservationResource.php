<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationResource\Pages;
use App\Filament\Resources\ReservationResource\RelationManagers;
use App\Models\Reservation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\EventResource;
use App\Filament\Resources\ContractorResource;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Rezerwacje';
    protected static ?string $pluralLabel = 'Rezerwacje';
    protected static ?string $modelLabel = 'Rezerwacja';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('event_id')
                    ->label('Impreza')
                    ->relationship('event', 'name')
                    ->getOptionLabelUsing(fn($value): string => (string) (\App\Models\Event::find($value)?->name ?? ''))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->default(fn() => request()->query('event_id'))
                    ->disabledOn('edit'),

                Forms\Components\Select::make('contractor_id')
                    ->label('Kontrahent')
                    ->relationship('contractor', 'name')
                    ->getOptionLabelUsing(fn($value): string => (string) (\App\Models\Contractor::find($value)?->name ?? ''))
                    ->searchable()
                    ->preload()
                    ->default(fn() => request()->query('contractor_id'))
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')->required()->label('Nazwa'),
                        Forms\Components\TextInput::make('email')->email(),
                        Forms\Components\TextInput::make('phone'),
                    ])
                    ->required(),

                Forms\Components\Select::make('event_program_point_id')
                    ->label('Punkt programu')
                    ->relationship('programPoint', 'name')
                    ->getOptionLabelUsing(fn($value): string => (string) (\App\Models\EventTemplateProgramPoint::find($value)?->name ?? \App\Models\EventTemplateProgramPoint::find($value)?->title ?? ''))
                    // Logic to filter program points by selected event would be nice but tricky in global create
                    // Simplification: just searchable
                    ->searchable()
                    ->preload()
                    ->default(fn() => request()->query('event_program_point_id')),

                Forms\Components\Fieldset::make('Szczegóły finansowe')
                    ->schema([
                        Forms\Components\TextInput::make('cost')
                            ->label('Koszt')
                            ->numeric()
                            ->suffix('PLN'),
                        Forms\Components\TextInput::make('advance_payment')
                            ->label('Zaliczka')
                            ->numeric()
                            ->suffix('PLN')
                            ->default(fn() => request()->query('advance_payment')),
                        Forms\Components\Select::make('status')
                            ->label('Status rezerwacji')
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
                    ->columnSpanFull()
                    ->default(fn() => request()->query('notes')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event.name')
                    ->label('Impreza')
                    ->searchable()
                    ->sortable()
                    ->url(fn($record) => EventResource::getUrl('edit', ['record' => $record->event_id])),

                Tables\Columns\TextColumn::make('event.status')
                    ->label('Status Imprezy')
                    ->badge()
                    ->colors([
                        'danger' => 'cancelled',
                        'warning' => 'draft',
                        'success' => ['confirmed', 'completed'],
                        'info' => 'planned',
                    ]),

                Tables\Columns\TextColumn::make('contractor.name')
                    ->label('Kontrahent')
                    ->searchable()
                    ->sortable()
                    ->url(fn($record) => $record->contractor_id ? ContractorResource::getUrl('edit', ['record' => $record->contractor_id]) : null),

                Tables\Columns\TextColumn::make('programPoint.name')
                    ->label('Punkt programu')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('cost')
                    ->label('Koszt')
                    ->money('PLN')
                    ->sortable(),

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
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status rezerwacji')
                    ->options([
                        'pending' => 'Oczekuje',
                        'confirmed' => 'Potwierdzone',
                        'paid' => 'Opłacone',
                        'cancelled' => 'Anulowane',
                    ]),
                SelectFilter::make('contractor_id')
                    ->label('Kontrahent')
                    ->relationship('contractor', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('event_id')
                    ->label('Impreza')
                    ->relationship('event', 'name')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }
}
