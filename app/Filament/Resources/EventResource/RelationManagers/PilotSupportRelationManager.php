<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Models\EventProgramPoint;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class PilotSupportRelationManager extends RelationManager
{
    protected static string $relationship = 'programPoints';

    protected static ?string $title = 'Obsługa pilota';

    protected static ?string $icon = 'heroicon-o-clipboard-document-list';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query
                    ->where('pilot_pays', true)
                    ->with(['templatePoint']);
            })
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('day')
                    ->label('Dzień')
                    ->sortable(),
                Tables\Columns\TextColumn::make('templatePoint.name')
                    ->label('Punkt programu')
                    ->formatStateUsing(fn ($state, EventProgramPoint $record) => $state ?: ($record->name ?? '—'))
                    ->wrap(),
                Tables\Columns\TextColumn::make('pilot_payment_needed')
                    ->label('Potrzebuje')
                    ->formatStateUsing(fn ($state, EventProgramPoint $record) => $state !== null
                        ? number_format((float) $state, 2, ',', ' ') . ' ' . ($record->pilot_payment_currency ?? 'PLN')
                        : '—'
                    )
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('pilot_payment_given')
                    ->label('Otrzymał')
                    ->formatStateUsing(fn ($state, EventProgramPoint $record) => $state !== null
                        ? number_format((float) $state, 2, ',', ' ') . ' ' . ($record->pilot_payment_currency ?? 'PLN')
                        : '—'
                    )
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('pilot_payment_currency')
                    ->label('Waluta')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('pilot_payment_notes')
                    ->label('Uwagi')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Action::make('pilot_summary')
                    ->label('Podsumowanie pilota')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->modalHeading('Podsumowanie pilota')
                    ->modalWidth('6xl')
                    ->modalContent(function () {
                        $event = $this->getOwnerRecord();

                        $points = $event->programPoints()
                            ->where('pilot_pays', true)
                            ->get();

                        $neededTotals = $points
                            ->groupBy(fn ($p) => $p->pilot_payment_currency ?: 'PLN')
                            ->map(fn ($items) => (float) $items->sum('pilot_payment_needed'))
                            ->toArray();

                        $givenTotals = $points
                            ->groupBy(fn ($p) => $p->pilot_payment_currency ?: 'PLN')
                            ->map(fn ($items) => (float) $items->sum('pilot_payment_given'))
                            ->toArray();

                        $expenseTotals = $event->pilotExpenses()
                            ->get()
                            ->groupBy(fn ($e) => $e->currency ?: 'PLN')
                            ->map(fn ($items) => (float) $items->sum('amount'))
                            ->toArray();

                        $latestExpenses = $event->pilotExpenses()
                            ->with(['eventProgramPoint.templatePoint', 'user'])
                            ->latest('expense_date')
                            ->limit(8)
                            ->get();

                        return view('filament.resources.event-resource.relation-managers.pilot-support-summary', [
                            'neededTotals' => $neededTotals,
                            'givenTotals' => $givenTotals,
                            'expenseTotals' => $expenseTotals,
                            'latestExpenses' => $latestExpenses,
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Zamknij'),
            ])
            ->emptyStateHeading('Brak punktów opłacanych przez pilota');
    }
}
