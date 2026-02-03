<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventPaymentCostAllocationResource\Pages;
use App\Models\EventCost;
use App\Models\EventPayment;
use App\Models\EventPaymentCostAllocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EventPaymentCostAllocationResource extends Resource
{
    protected static ?string $model = EventPaymentCostAllocation::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = 'Finanse';
    protected static ?int $navigationSort = 35;
    protected static ?string $navigationLabel = 'Alokacje wpłat';
    protected static ?string $modelLabel = 'Alokacja wpłaty';
    protected static ?string $pluralModelLabel = 'Alokacje wpłat';
    
    // Ukryte w nawigacji - funkcjonalność nie jest używana
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Alokacja')
                    ->schema([
                        Forms\Components\Select::make('event_id')
                            ->label('Impreza')
                            ->relationship('event', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->columnSpan(4),

                        Forms\Components\Select::make('event_payment_id')
                            ->label('Wpłata')
                            ->required()
                            ->options(function (Get $get): array {
                                $eventId = $get('event_id');
                                if (! $eventId) {
                                    return [];
                                }

                                return EventPayment::query()
                                    ->where('event_id', $eventId)
                                    ->orderByDesc('payment_date')
                                    ->orderByDesc('id')
                                    ->limit(200)
                                    ->get()
                                    ->mapWithKeys(function (EventPayment $p) {
                                        $label = trim(($p->payment_date?->format('d.m.Y') ?? '') . ' • ' . number_format((float) $p->amount, 2, '.', ' ') . ' PLN • ' . ($p->description ?? ('Wpłata #' . $p->id)));
                                        return [$p->id => $label];
                                    })
                                    ->all();
                            })
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if (! $state) {
                                    $set('contract_id', null);
                                    return;
                                }

                                $payment = EventPayment::query()->find($state);
                                $set('contract_id', $payment?->contract_id);
                            })
                            ->columnSpan(4),

                        Forms\Components\Select::make('event_cost_id')
                            ->label('Koszt')
                            ->required()
                            ->options(function (Get $get): array {
                                $eventId = $get('event_id');
                                if (! $eventId) {
                                    return [];
                                }

                                return EventCost::query()
                                    ->with('currency')
                                    ->where('event_id', $eventId)
                                    ->orderByDesc('payment_date')
                                    ->orderByDesc('id')
                                    ->limit(300)
                                    ->get()
                                    ->mapWithKeys(function (EventCost $c) {
                                        $raw = number_format((float) $c->amount, 2, '.', ' ');
                                        $sym = $c->currency?->symbol ?? 'PLN';
                                        $pln = number_format((float) $c->amount_pln, 2, '.', ' ');
                                        $label = trim(($c->payment_date?->format('d.m.Y') ?? '') . ' • ' . ($c->name ?? ('Koszt #' . $c->id)) . " • {$raw} {$sym} (≈ {$pln} PLN)");
                                        return [$c->id => $label];
                                    })
                                    ->all();
                            })
                            ->searchable()
                            ->columnSpan(4),

                        Forms\Components\TextInput::make('amount')
                            ->label('Kwota alokacji (PLN)')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->columnSpan(2),

                        Forms\Components\DateTimePicker::make('allocated_at')
                            ->label('Data alokacji')
                            ->default(now())
                            ->columnSpan(2),

                        Forms\Components\Select::make('contract_id')
                            ->label('Umowa (opcjonalnie)')
                            ->relationship('contract', 'contract_number')
                            ->searchable()
                            ->preload()
                            ->columnSpan(2),

                        Forms\Components\Textarea::make('note')
                            ->label('Notatka')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(4)
                    ->compact(),
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
                    ->url(fn (EventPaymentCostAllocation $record) => EventResource::getUrl('edit', ['record' => $record->event_id])),
                Tables\Columns\TextColumn::make('payment.payment_date')
                    ->label('Wpłata: data')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment.amount')
                    ->label('Wpłata: kwota')
                    ->money('PLN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost.name')
                    ->label('Koszt')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Alokacja')
                    ->money('PLN')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Suma')),
                Tables\Columns\TextColumn::make('allocated_at')
                    ->label('Data')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utworzył')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->relationship('event', 'name')
                    ->label('Impreza')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('allocated_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Od'),
                        Forms\Components\DatePicker::make('until')->label('Do'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('allocated_at', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('allocated_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('allocated_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['event', 'payment', 'cost', 'user']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEventPaymentCostAllocations::route('/'),
            'create' => Pages\CreateEventPaymentCostAllocation::route('/create'),
            'edit' => Pages\EditEventPaymentCostAllocation::route('/{record}/edit'),
        ];
    }
}
