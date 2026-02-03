<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Models\EventCost;
use App\Models\EventPayment;
use App\Models\EventPaymentCostAllocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PaymentAllocationsRelationManager extends RelationManager
{
    protected static string $relationship = 'paymentAllocations';
    protected static ?string $title = 'Alokacje wpłat';
    protected static ?string $icon = 'heroicon-o-arrows-right-left';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('event_id')
                    ->default(fn (PaymentAllocationsRelationManager $livewire) => $livewire->getOwnerRecord()->id)
                    ->required(),

                Forms\Components\Select::make('event_payment_id')
                    ->label('Wpłata')
                    ->required()
                    ->options(function (PaymentAllocationsRelationManager $livewire): array {
                        $eventId = $livewire->getOwnerRecord()->id;
                        return EventPayment::query()
                            ->where('event_id', $eventId)
                            ->orderByDesc('payment_date')
                            ->orderByDesc('id')
                            ->get()
                            ->mapWithKeys(function (EventPayment $p) {
                                $label = trim(($p->payment_date?->format('d.m.Y') ?? '') . ' • ' . number_format((float) $p->amount, 2, '.', ' ') . ' PLN • ' . ($p->description ?? ('Wpłata #' . $p->id)));
                                return [$p->id => $label];
                            })
                            ->all();
                    })
                    ->searchable(),

                Forms\Components\Select::make('event_cost_id')
                    ->label('Koszt')
                    ->required()
                    ->options(function (PaymentAllocationsRelationManager $livewire): array {
                        $eventId = $livewire->getOwnerRecord()->id;
                        return EventCost::query()
                            ->with('currency')
                            ->where('event_id', $eventId)
                            ->orderByDesc('payment_date')
                            ->orderByDesc('id')
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
                    ->searchable(),

                Forms\Components\TextInput::make('amount')
                    ->label('Kwota (PLN)')
                    ->numeric()
                    ->required()
                    ->minValue(0.01),

                Forms\Components\DateTimePicker::make('allocated_at')
                    ->label('Data')
                    ->default(now()),

                Forms\Components\Textarea::make('note')
                    ->label('Notatka')
                    ->rows(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment.payment_date')
                    ->label('Wpłata: data')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment.description')
                    ->label('Wpłata: opis')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cost.name')
                    ->label('Koszt')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Kwota')
                    ->money('PLN')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Suma')),
                Tables\Columns\TextColumn::make('allocated_at')
                    ->label('Data')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Dodaj alokację')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();
                        if (empty($data['allocated_at'])) {
                            $data['allocated_at'] = now();
                        }
                        return $data;
                    })
                    ->using(function (array $data) {
                        $payment = EventPayment::query()->find($data['event_payment_id'] ?? null);
                        $cost = EventCost::query()->with('currency')->find($data['event_cost_id'] ?? null);

                        if (! $payment || ! $cost) {
                            Notification::make()->title('Brak wpłaty lub kosztu')->danger()->send();
                            return null;
                        }

                        $alreadyFromPayment = (float) EventPaymentCostAllocation::query()
                            ->where('event_payment_id', $payment->id)
                            ->sum('amount');
                        $available = max(0.0, (float) $payment->amount - $alreadyFromPayment);

                        $alreadyToCost = (float) EventPaymentCostAllocation::query()
                            ->where('event_cost_id', $cost->id)
                            ->sum('amount');
                        $remaining = max(0.0, (float) $cost->amount_pln - $alreadyToCost);

                        $amount = (float) ($data['amount'] ?? 0);
                        if ($amount <= 0) {
                            Notification::make()->title('Kwota alokacji musi być większa od 0')->danger()->send();
                            return null;
                        }
                        if ($amount - $available > 0.0001) {
                            Notification::make()
                                ->title('Kwota przekracza dostępne środki wpłaty')
                                ->danger()
                                ->body('Pozostało do alokacji: ' . number_format($available, 2, '.', ' ') . ' PLN')
                                ->send();
                            return null;
                        }
                        if ($amount - $remaining > 0.0001) {
                            Notification::make()
                                ->title('Kwota przekracza pozostałą kwotę kosztu')
                                ->danger()
                                ->body('Pozostało do pokrycia: ' . number_format($remaining, 2, '.', ' ') . ' PLN')
                                ->send();
                            return null;
                        }

                        if (empty($data['contract_id']) && ! empty($payment->contract_id)) {
                            $data['contract_id'] = $payment->contract_id;
                        }

                        return EventPaymentCostAllocation::query()->create($data);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('allocated_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['payment', 'cost']);
    }
}
