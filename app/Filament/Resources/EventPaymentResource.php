<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventPaymentResource\Pages;
use App\Filament\Resources\EventPaymentResource\RelationManagers;
use App\Models\Contract;
use App\Models\EventPayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EventPaymentResource extends Resource
{
    protected static ?string $model = EventPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Finanse';
    protected static ?int $navigationSort = 30;
    protected static ?string $navigationLabel = 'Wpłaty';
    protected static ?string $modelLabel = 'Wpłata';
    protected static ?string $pluralModelLabel = 'Wpłaty';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Szczegóły wpłaty')
                    ->schema([
                        Forms\Components\Select::make('event_id')
                            ->relationship('event', 'name')
                            ->required()
                            ->label('Impreza')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->columnSpan(3),
                        Forms\Components\Select::make('contract_id')
                            ->label('Umowa (opcjonalnie)')
                            ->options(fn (callable $get) =>
                                ($get('event_id')
                                    ? Contract::query()
                                        ->where('event_id', $get('event_id'))
                                        ->orderBy('contract_number')
                                        ->pluck('contract_number', 'id')
                                        ->toArray()
                                    : [])
                            )
                            ->searchable()
                            ->placeholder('—')
                            ->columnSpan(3),
                        Forms\Components\TextInput::make('description')
                            ->label('Opis')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(3),
                        
                        Forms\Components\TextInput::make('amount')
                            ->label('Kwota (PLN)')
                            ->numeric()
                            ->required()
                            ->columnSpan(2),
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Data płatności')
                            ->required()
                            ->columnSpan(2),
                        Forms\Components\Select::make('payment_type_id')
                            ->label('Forma płatności')
                            ->relationship('paymentType', 'name')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required()->label('Nazwa'),
                            ])
                            ->required()
                            ->columnSpan(2),
                        
                        Forms\Components\TextInput::make('invoice_number')
                            ->label('Numer faktury')
                            ->columnSpan(3),
                        Forms\Components\Toggle::make('is_advance')
                            ->label('Zaliczka')
                            ->inline(false)
                            ->columnSpan(3),
                    ])->columns(6)->compact(),
                
                Forms\Components\Section::make('Dokumenty')
                    ->schema([
                        Forms\Components\FileUpload::make('documents')
                            ->label('Załączniki')
                            ->multiple()
                            ->directory('event-payments-documents')
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
                    ->url(fn (EventPayment $record) => \App\Filament\Resources\EventResource::getUrl('edit', ['record' => $record->event_id])),
                Tables\Columns\TextColumn::make('description')
                    ->label('Opis')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Kwota')
                    ->money('pln')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Suma')),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Data')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('paymentType.name')
                    ->label('Forma płatności'),
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Faktura')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_advance')
                    ->label('Zaliczka')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->relationship('event', 'name')
                    ->label('Impreza')
                    ->searchable()
                    ->preload(),
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
        return parent::getEloquentQuery();
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
            'index' => Pages\ListEventPayments::route('/'),
            'create' => Pages\CreateEventPayment::route('/create'),
            'edit' => Pages\EditEventPayment::route('/{record}/edit'),
        ];
    }
}
