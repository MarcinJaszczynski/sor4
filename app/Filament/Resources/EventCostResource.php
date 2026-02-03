<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventCostResource\Pages;
use App\Filament\Resources\EventCostResource\RelationManagers;
use App\Models\EventCost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EventCostResource extends Resource
{
    protected static ?string $model = EventCost::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Finanse';
    protected static ?int $navigationSort = 30;
    protected static ?string $navigationLabel = 'Koszty';
    protected static ?string $modelLabel = 'Koszt';
    protected static ?string $pluralModelLabel = 'Koszty';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dane kosztu')
                    ->schema([
                        Forms\Components\Select::make('event_id')
                            ->relationship('event', 'name')
                            ->required()
                            ->label('Impreza')
                            ->searchable()
                            ->preload()
                            ->columnSpan(3),
                        Forms\Components\TextInput::make('name')
                            ->label('Opis kosztu')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(3),
                        
                        Forms\Components\Select::make('contractor_id')
                            ->label('Kontrahent')
                            ->relationship('contractor', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required()->label('Nazwa'),
                            ])
                            ->columnSpan(3),
                        Forms\Components\Select::make('payer_id')
                            ->label('Płatnik')
                            ->relationship('payer', 'name')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required()->label('Nazwa'),
                            ])
                            ->required()
                            ->columnSpan(3),

                        Forms\Components\TextInput::make('amount')
                            ->label('Kwota')
                            ->numeric()
                            ->required()
                            ->columnSpan(2),
                        Forms\Components\Select::make('currency_id')
                            ->label('Waluta')
                            ->relationship('currency', 'symbol')
                            ->default(function () {
                                return \App\Models\Currency::where('symbol', 'PLN')->first()?->id;
                            })
                            ->columnSpan(1),
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Data płatności')
                            ->columnSpan(3),

                        Forms\Components\Select::make('payment_type_id')
                            ->label('Forma płatności')
                            ->relationship('paymentType', 'name')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required()->label('Nazwa'),
                            ])
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('invoice_number')
                            ->label('Nr faktury')
                            ->columnSpan(2),
                        Forms\Components\Toggle::make('is_paid')
                            ->label('Opłacone')
                            ->default(false)
                            ->inline(false)
                            ->columnSpan(2),
                    ])->columns(6)->compact(),
                
                Forms\Components\Section::make('Dokumenty')
                    ->schema([
                        Forms\Components\FileUpload::make('documents')
                            ->label('Załączniki')
                            ->multiple()
                            ->directory('event-costs-documents')
                            ->downloadable()
                            ->openable()
                            ->maxFiles(10)
                            ->helperText('Możesz dodać do 10 dokumentów (faktury, umowy, potwierdzenia, itp.)')
                            ->columnSpanFull(),
                    ])->collapsed(),
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
                    ->url(fn (EventCost $record) => \App\Filament\Resources\EventResource::getUrl('edit', ['record' => $record->event_id])),
                Tables\Columns\TextColumn::make('name')
                    ->label('Opis')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contractor.name')
                    ->label('Kontrahent')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Kwota')
                    ->numeric(2)
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Suma')),
                Tables\Columns\TextColumn::make('currency.symbol')
                    ->label('Waluta'),
                Tables\Columns\TextColumn::make('amount_pln')
                    ->label('Kwota (PLN)')
                    ->money('PLN')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Data')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_paid')
                    ->label('Opłacone')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payer.name')
                    ->label('Płatnik'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->relationship('event', 'name')
                    ->label('Impreza')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('contractor')
                    ->relationship('contractor', 'name')
                    ->label('Kontrahent')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('is_paid')
                    ->label('Tylko nieopłacone')
                    ->query(fn (Builder $query): Builder => $query->where('is_paid', false)),
                Tables\Filters\Filter::make('payment_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Od'),
                        Forms\Components\DatePicker::make('until')->label('Do'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('payment_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('payment_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('mark_paid')
                    ->label('Oznacz jako opłacone')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (EventCost $record) => !$record->is_paid)
                    ->action(fn (EventCost $record) => $record->update(['is_paid' => true])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('payment_date', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['currency']);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\TaskResource\RelationManagers\TasksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEventCosts::route('/'),
            'create' => Pages\CreateEventCost::route('/create'),
            'edit' => Pages\EditEventCost::route('/{record}/edit'),
        ];
    }
}
