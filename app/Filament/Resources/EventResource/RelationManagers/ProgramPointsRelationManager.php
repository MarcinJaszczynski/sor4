<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Filament\Resources\ReservationResource;
use App\Models\Contractor;
use App\Models\Currency;
use App\Models\EventProgramPoint;
use App\Models\EventTemplateProgramPoint;
use App\Models\Reservation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProgramPointsRelationManager extends RelationManager
{
    protected static string $relationship = 'programPoints';
    protected static ?string $title = 'Program imprezy';
    protected static ?string $recordTitleAttribute = 'name';

    // ============ Właściwości dla kalkulacji ============
    public $calculations = [];
    // protected $programPoints; // Removed: using local variable for hydration performance
    // protected $costsByDay;    // Removed: using local variable for hydration performance
    public $transportCost = 0;
    public $detailedCalculations = [];
    public bool $showCalculationSummary = true;
    public array $selectedContractors = [];
    protected ?Collection $contractorOptionsCache = null;
    public array $reservationForm = [];
    protected ?array $deletionReservationAlert = null;

    // ============ Inicjalizacja i ładowanie ============
    public function mount(): void
    {
        parent::mount();
        $this->loadCalculations();
    }

    public function loadCalculations()
    {
        $event = $this->getOwnerRecord();

        // Załaduj punkty programu z kosztami (LOCAL VARIABLE)
        $programPoints = $event->programPoints()
            ->with('templatePoint')
            ->where('active', true)
            ->orderBy('day')
            ->orderBy('order')
            ->get();

        foreach ($programPoints as $point) {
            $this->reservationForm[$point->id] = $this->reservationForm[$point->id] ?? $this->createReservationFormDefaults($point);
        }

        // Oblicz koszty transportu
        $this->calculateTransportCost();

        // Oblicz koszty według dni (LOCAL VARIABLE)
        $costsByDay = $programPoints
            ->groupBy('day')
            ->map(function ($points) {
                $totalCost = \App\Services\ProgramPointHelper::sumIncluded($points, 'total_price');
                $programCost = $points->where('include_in_program', true)->sum('total_price');

                return [
                    'points_count' => $points->count(),
                    'total_cost' => $totalCost,
                    'program_cost' => $programCost,
                    'calculation_points' => $points->filter(function ($p) {
                        return (bool)($p->include_in_calculation ?? true);
                    })->count(),
                    'program_points' => $points->where('include_in_program', true)->count(),
                    'points' => $points,
                ];
            });

        // Oblicz główne kalkulacje używając silnika
        $engine = new \App\Services\EventCalculationEngine();
        $mainCalculation = $engine->calculate($event);

        $this->calculations = [
            'total_points' => $programPoints->count(),
            'active_points' => $programPoints->where('active', true)->count(),
            'calculation_points' => \App\Services\ProgramPointHelper::countIncluded($programPoints),
            'program_points' => $programPoints->where('include_in_program', true)->count(),
            'total_program_cost' => $mainCalculation['program_cost'],
            'transport_cost' => $mainCalculation['transport_cost'],
            'accommodation_cost' => $mainCalculation['accommodation_cost'] ?? 0,
            'total_cost' => $mainCalculation['total_cost'],
            'program_cost' => $mainCalculation['program_cost'],
            'cost_per_person' => $mainCalculation['cost_per_person'],
            'currencies' => $mainCalculation['currencies'] ?? [],
            'currencies_per_person' => $mainCalculation['currencies_per_person'] ?? [],
            'markup_amount' => $mainCalculation['markup_amount'] ?? 0,
            'min_markup_amount' => $mainCalculation['min_markup_amount'] ?? 0,
            'is_min_markup_applied' => $mainCalculation['is_min_markup_applied'] ?? false,
            'tax_amount' => $mainCalculation['tax_amount'] ?? 0,
            'total_count_for_costs' => $mainCalculation['total_count_for_costs'] ?? null,
            'participants_count' => $mainCalculation['participant_count'] ?? $event->participant_count,
            'gratis_count' => $mainCalculation['gratis_count'] ?? ($event->gratis_count ?? 0),
            'staff_count' => $mainCalculation['staff_count'] ?? ($event->staff_count ?? 0),
            'driver_count' => $mainCalculation['driver_count'] ?? ($event->driver_count ?? 0),
            'guide_count' => $mainCalculation['guide_count'] ?? ($event->guide_count ?? 0),
            'days_count' => $costsByDay->count(),
            'participant_count' => $event->participant_count,
        ];

        // Oblicz szczegółowe kalkulacje z uwzględnieniem różnych wariantów
        $this->calculateDetailedPricing($event);
    }

    public function calculateTransportCost()
    {
        $event = $this->getOwnerRecord();
        $this->transportCost = 0;

        if (!$event->bus) {
            return;
        }

        $bus = $event->bus;
        $transferKm = $event->transfer_km ?? 0;
        $programKm = $event->program_km ?? 0;
        $duration = $event->duration_days ?? 1;

        $totalKm = 2 * $transferKm + $programKm;
        $includedKm = $duration * ($bus->package_km_per_day ?? 0);
        $baseCost = $duration * ($bus->package_price_per_day ?? 0);

        if ($totalKm <= $includedKm) {
            $this->transportCost = $baseCost;
        } else {
            $extraKm = $totalKm - $includedKm;
            $this->transportCost = $baseCost + ($extraKm * ($bus->extra_km_price ?? 0));
        }

        // Przelicz na PLN jeśli autokar ma inną walutę
        if ($bus->currency && $bus->currency !== 'PLN') {
            $currency = \App\Models\Currency::where('symbol', $bus->currency)->first();
            $exchangeRate = $currency?->exchange_rate ?? 1;
            $this->transportCost *= $exchangeRate;
        }
    }

    public function calculateDetailedPricing($event)
    {
        $this->detailedCalculations = [];
        // Zawsze używamy silnika kalkulacji, żeby warianty były spójne z podsumowaniem
        $engine = new \App\Services\EventCalculationEngine();

        // Generuj warianty: 10, 20, 30, 40, 50 oraz aktualna liczba uczestników
        $variants = collect([10, 20, 30, 40, 50]);
        if ($event->participant_count > 0) {
            $variants->push($event->participant_count);
        }
        $variants = $variants->unique()->sort()->values();

        foreach ($variants as $qty) {
            $calculation = $engine->calculate($event, $qty);
            $label = $qty === (int) $event->participant_count
                ? $qty . ' osób (bieżąca)'
                : $qty . ' osób';

            $this->detailedCalculations[] = [
                'qty' => $qty,
                'name' => $label,
                'program_cost' => $calculation['program_cost'],
                'accommodation_cost' => $calculation['accommodation_cost'] ?? 0,
                'transport_cost' => $calculation['transport_cost'],
                'markup_amount' => $calculation['markup_amount'],
                'tax_amount' => $calculation['tax_amount'],
                'total_cost' => $calculation['total_cost'],
                'cost_per_person' => $calculation['cost_per_person'],
            ];
        }
    }

    public function saveContractorAssignment(int $pointId): void
    {
        $contractorId = (int) ($this->selectedContractors[$pointId] ?? 0);
        if ($contractorId <= 0) {
            Notification::make()->warning('Wybierz najpierw kontrahenta z listy.')->send();
            return;
        }

        $point = EventProgramPoint::where('event_id', $this->getOwnerRecord()->id)->find($pointId);
        if (!$point) {
            Notification::make()->danger('Nie odnaleziono punktu programu.')->send();
            return;
        }

        $contractor = Contractor::find($contractorId);
        if (!$contractor) {
            Notification::make()->danger('Wybrany kontrahent nie istnieje.')->send();
            return;
        }

        $point->update(['assigned_contractor_id' => $contractorId]);

        Notification::make()
            ->title('Kontrahent przypisany')
            ->success()
            ->body("{$contractor->name} został przypisany do punktu programu '{$point->name}'.")
            ->send();

        $this->selectedContractors[$pointId] = $contractorId;
        $this->loadCalculations();
    }

    public function removeContractorAssignment(int $pointId): void
    {
        $point = EventProgramPoint::where('event_id', $this->getOwnerRecord()->id)->find($pointId);
        if (!$point) {
            Notification::make()->danger('Nie odnaleziono punktu programu.')->send();
            return;
        }

        if (!$point->assigned_contractor_id) {
            Notification::make()->warning('Ten punkt nie ma przypisanego kontrahenta.')->send();
            return;
        }

        $point->update(['assigned_contractor_id' => null]);

        Notification::make()
            ->title('Przypisanie usunięte')
            ->success()
            ->body('Kontrahent został odłączony od punktu programu.')
            ->send();

        $this->selectedContractors[$pointId] = null;
        $this->loadCalculations();
    }

    public function saveReservationForPoint(int $pointId): void
    {
        $point = EventProgramPoint::where('event_id', $this->getOwnerRecord()->id)->find($pointId);
        if (!$point) {
            Notification::make()->danger('Nie odnaleziono punktu programu.')->send();
            return;
        }

        $data = $this->reservationForm[$pointId] ?? $this->createReservationFormDefaults($point);

        $contractorId = (int) ($data['contractor_id'] ?? 0);
        if ($contractorId <= 0) {
            Notification::make()->warning('Wybierz kontrahenta dla rezerwacji.')->send();
            return;
        }

        $reservation = $point->latestReservation ?? new Reservation();
        $isNew = !$reservation->exists;

        $reservation->fill([
            'event_id' => $point->event_id,
            'contractor_id' => $contractorId,
            'event_program_point_id' => $point->id,
            'status' => $data['status'] ?? 'pending',
            'cost' => $data['cost'] ?? 0,
            'advance_payment' => $data['advance_payment'] ?? 0,
            'due_date' => $data['due_date'] ?: null,
            'notes' => $data['notes'] ?? null,
        ]);
        $reservation->save();

        $action = $isNew ? 'utworzono' : 'zaktualizowano';

        Notification::make()
            ->title('Rezerwacja ' . $action)
            ->success()
            ->body("Rezerwacja dla punktu programu została {$action}.")
            ->send();

        $this->selectedContractors[$pointId] = $contractorId;
        $this->refreshReservationFormForPoint($point);
        $this->loadCalculations();
        $this->dispatchBrowserEvent('reservation-saved', ['pointId' => $pointId]);
    }

    public function deleteReservationForPoint(int $pointId): void
    {
        $point = EventProgramPoint::where('event_id', $this->getOwnerRecord()->id)->find($pointId);
        if (!$point) {
            Notification::make()->danger('Nie odnaleziono punktu programu.')->send();
            return;
        }

        $reservation = $point->latestReservation;
        if (!$reservation) {
            Notification::make()->warning('Nie znaleziono rezerwacji do usunięcia.')->send();
            return;
        }

        $reservation->delete();

        Notification::make()
            ->title('Rezerwacja usunięta')
            ->warning()
            ->body('Ostatnia rezerwacja dla punktu programu została usunięta.')
            ->send();

        $this->refreshReservationFormForPoint($point);
        $this->loadCalculations();
        $this->dispatchBrowserEvent('reservation-deleted', ['pointId' => $pointId]);
    }

    protected function getContractorOptions(): Collection
    {
        if ($this->contractorOptionsCache !== null) {
            return $this->contractorOptionsCache;
        }

        return $this->contractorOptionsCache = Contractor::orderBy('name')->get(['id', 'name']);
    }

    public function suggestedAdvanceForPoint(EventProgramPoint $point): float
    {
        $total = $this->getPointTotalPrice($point);
        return round(max(0, $total) * 0.3, 2);
    }

    protected function getPointTotalPrice(EventProgramPoint $point): float
    {
        return (float) ($point->total_price ?? ($point->unit_price ?? 0) * ($point->quantity ?? 1));
    }

    public function pointTotalPrice(EventProgramPoint $point): float
    {
        return $this->getPointTotalPrice($point);
    }

    public function formatAmountForReservationUrl(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }

    protected function ensureContractorSelection(EventProgramPoint $point): void
    {
        if (!array_key_exists($point->id, $this->selectedContractors) && $point->assigned_contractor_id) {
            $this->selectedContractors[$point->id] = $point->assigned_contractor_id;
        }
    }

    protected function createReservationFormDefaults(EventProgramPoint $point): array
    {
        $reservation = $point->latestReservation;
        $dueDate = $reservation?->due_date;

        if ($dueDate && ! ($dueDate instanceof Carbon)) {
            try {
                $dueDate = Carbon::parse($dueDate)->toDateString();
            } catch (\Throwable) {
                $dueDate = $reservation?->due_date;
            }
        }

        return [
            'status' => $reservation?->status ?? 'pending',
            'cost' => $reservation?->cost ?? $point->total_price ?? 0,
            'advance_payment' => $reservation?->advance_payment ?? 0,
            'due_date' => $dueDate,
            'notes' => $reservation?->notes ?? '',
            'contractor_id' => $reservation?->contractor_id ?? $point->assigned_contractor_id ?? null,
        ];
    }

    protected function refreshReservationFormForPoint(EventProgramPoint $point): void
    {
        $point->refresh();
        $this->reservationForm[$point->id] = $this->createReservationFormDefaults($point);
    }

    protected function getPricingCurrencySymbol(EventProgramPoint $record): string
    {
        $currency = null;

        if ($record->currency_id) {
            $currency = Currency::find($record->currency_id);
        }

        if (!$currency && $record->templatePoint?->currency) {
            $currency = $record->templatePoint->currency;
        }

        return $currency?->symbol ?? 'PLN';
    }

    protected function getTotalCountForCosts(): int
    {
        $event = $this->getOwnerRecord();

        $participantCount = (int) ($event->participant_count ?? 0);
        if ($participantCount <= 0) {
            $participantCount = 1;
        }

        $gratisCount = (int) ($event->gratis_count ?? 0);
        $staffCount = (int) ($event->staff_count ?? 0);
        $driverCount = (int) ($event->driver_count ?? 0);
        $guideCount = ($event->guide_count ?? null) !== null
            ? (int) $event->guide_count
            : (method_exists($event, 'guides') ? (int) $event->guides()->count() : 0);

        $total = $participantCount + $gratisCount + $staffCount + $driverCount + $guideCount;

        return $total > 0 ? $total : 1;
    }

    protected function getProgramPointCount(): int
    {
        $event = $this->getOwnerRecord();
        $count = (int) ($event->participant_count ?? 0);
        return $count > 0 ? $count : 1;
    }

    protected function getPointCostForDisplay(EventProgramPoint $record): array
    {
        $currencySymbol = $this->getPricingCurrencySymbol($record);
        $currencyRate = 1;

        if ($record->currency_id) {
            $currency = Currency::find($record->currency_id);
            if ($currency) {
                $currencyRate = (float) ($currency->exchange_rate ?? 1);
            }
        } elseif ($record->templatePoint?->currency) {
            $currencyRate = (float) ($record->templatePoint->currency->exchange_rate ?? 1);
        }

        $groupSize = $record->group_size ?? $record->templatePoint?->group_size ?? 1;
        $quantity = (int) ($record->quantity ?? 1);
        $unitPrice = (float) ($record->unit_price ?? 0);
        $totalCount = $this->getProgramPointCount();

        if ($groupSize <= 1) {
            $groupsNeeded = $totalCount;
        } else {
            $groupsNeeded = (int) ceil($totalCount / $groupSize);
        }

        $cost = $groupsNeeded * $unitPrice * $quantity;

        if (($record->convert_to_pln ?? false) && $currencySymbol !== 'PLN') {
            $cost *= $currencyRate;
            $currencySymbol = 'PLN';
        }

        return [
            'cost' => $cost,
            'currency' => $currencySymbol,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'group_size' => $groupSize,
            'groups_needed' => $groupsNeeded,
            'total_count' => $totalCount,
        ];
    }

    protected function getTimeRangeDisplay(EventProgramPoint $record): string
    {
        $start = $record->start_time ?? null;
        $end = $record->end_time ?? null;

        if ($start || $end) {
            $startDisplay = $start ? e($start) : '—';
            $endDisplay = $end ? e($end) : '—';

            return '<div class="text-sm">' . $startDisplay . '</div>'
                . '<div class="text-sm">-</div>'
                . '<div class="text-sm">' . $endDisplay . '</div>';
        }

        $hours = $record->duration_hours ?? $record->templatePoint?->duration_hours;
        $minutes = $record->duration_minutes ?? $record->templatePoint?->duration_minutes;

        if ($hours === null && $minutes === null) {
            return '—';
        }

        $totalMinutes = ((int) ($hours ?? 0) * 60) + (int) ($minutes ?? 0);
        $endHours = intdiv($totalMinutes, 60);
        $endMinutes = $totalMinutes % 60;

        $startDisplay = '00:00';
        $endDisplay = sprintf('%02d:%02d', $endHours, $endMinutes);

        return '<div class="text-sm">' . e($startDisplay) . '</div>'
            . '<div class="text-sm">-</div>'
            . '<div class="text-sm">' . e($endDisplay) . '</div>';
    }

    public function getPricingTooltip(EventProgramPoint $record): string
    {
        $data = $this->getPointCostForDisplay($record);

        if ($data['unit_price'] <= 0) {
            return 'Brak ceny';
        }

        if ($data['group_size'] <= 1) {
            return sprintf(
                'Koszt: %s os. × %s szt. × %s %s = %s %s',
                number_format((float) $data['total_count'], 0),
                number_format((float) $data['quantity'], 0),
                number_format((float) $data['unit_price'], 2),
                $data['currency'],
                number_format((float) $data['cost'], 2),
                $data['currency']
            );
        }

        return sprintf(
            'Koszt: %s grup × %s szt. × %s %s = %s %s (grupa %s os.)',
            number_format((float) $data['groups_needed'], 0),
            number_format((float) $data['quantity'], 0),
            number_format((float) $data['unit_price'], 2),
            $data['currency'],
            number_format((float) $data['cost'], 2),
            $data['currency'],
            number_format((float) $data['group_size'], 0)
        );
    }

    public function getPricingTotalDisplay(EventProgramPoint $record): string
    {
        $data = $this->getPointCostForDisplay($record);

        if ($data['unit_price'] <= 0) {
            return '—';
        }

        return number_format((float) $data['cost'], 2) . ' ' . $data['currency'];
    }

    public function refreshCalculations()
    {
        $event = $this->getOwnerRecord();
        $event->refresh();

        // Recalculate using engine
        $engine = new \App\Services\EventCalculationEngine();
        $calculation = $engine->calculate($event);

        // Update Event total_cost
        $event->update(['total_cost' => $calculation['total_cost']]);

        // Reload
        $this->loadCalculations();
    }

    public function canMoveUp(EventProgramPoint $record): bool
    {
        return $this->getMoveQuery($record)
            ->where(function (Builder $query) use ($record) {
                $query->where('order', '<', $record->order)
                    ->orWhere(function (Builder $query) use ($record) {
                        $query->where('order', $record->order)
                            ->where('id', '<', $record->id);
                    });
            })
            ->exists();
    }

    public function canMoveDown(EventProgramPoint $record): bool
    {
        return $this->getMoveQuery($record)
            ->where(function (Builder $query) use ($record) {
                $query->where('order', '>', $record->order)
                    ->orWhere(function (Builder $query) use ($record) {
                        $query->where('order', $record->order)
                            ->where('id', '>', $record->id);
                    });
            })
            ->exists();
    }

    public function canMovePrevDay(EventProgramPoint $record): bool
    {
        return $record->day > 1;
    }

    public function canMoveNextDay(EventProgramPoint $record): bool
    {
        $maxDay = $this->getOwnerRecord()->eventTemplate->duration_days ?? 1;
        return $record->day < $maxDay;
    }

    public function moveUpRecord(int $recordId): void
    {
        $record = EventProgramPoint::where('event_id', $this->getOwnerRecord()->id)
            ->whereKey($recordId)
            ->first();

        if (!$record) {
            return;
        }

        $previous = $this->getMoveQuery($record)
            ->where(function (Builder $query) use ($record) {
                $query->where('order', '<', $record->order)
                    ->orWhere(function (Builder $query) use ($record) {
                        $query->where('order', $record->order)
                            ->where('id', '<', $record->id);
                    });
            })
            ->orderBy('order', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if (!$previous) {
            return;
        }

        DB::transaction(function () use ($record, $previous) {
            $currentOrder = $record->order;
            $record->update(['order' => $previous->order]);
            $previous->update(['order' => $currentOrder]);
        });

        $this->dispatch('$refresh');
    }

    public function moveDownRecord(int $recordId): void
    {
        $record = EventProgramPoint::where('event_id', $this->getOwnerRecord()->id)
            ->whereKey($recordId)
            ->first();

        if (!$record) {
            return;
        }

        $next = $this->getMoveQuery($record)
            ->where(function (Builder $query) use ($record) {
                $query->where('order', '>', $record->order)
                    ->orWhere(function (Builder $query) use ($record) {
                        $query->where('order', $record->order)
                            ->where('id', '>', $record->id);
                    });
            })
            ->orderBy('order')
            ->orderBy('id')
            ->first();

        if (!$next) {
            return;
        }

        DB::transaction(function () use ($record, $next) {
            $currentOrder = $record->order;
            $record->update(['order' => $next->order]);
            $next->update(['order' => $currentOrder]);
        });

        $this->dispatch('$refresh');
    }

    public function movePrevDayRecord(int $recordId): void
    {
        $record = EventProgramPoint::where('event_id', $this->getOwnerRecord()->id)
            ->whereKey($recordId)
            ->first();

        if (!$record || $record->day <= 1) {
            return;
        }

        $record->moveToDay($record->day - 1);
        $this->dispatch('$refresh');
    }

    public function moveNextDayRecord(int $recordId): void
    {
        $record = EventProgramPoint::where('event_id', $this->getOwnerRecord()->id)
            ->whereKey($recordId)
            ->first();

        if (!$record) {
            return;
        }

        $maxDay = $this->getOwnerRecord()->eventTemplate->duration_days ?? 1;
        if ($record->day >= $maxDay) {
            return;
        }

        $record->moveToDay($record->day + 1);
        $this->dispatch('$refresh');
    }

    protected function getMoveQuery(EventProgramPoint $record): Builder
    {
        return EventProgramPoint::where('event_id', $record->event_id)
            ->where('day', $record->day)
            ->where('parent_id', $record->parent_id);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Podstawowe informacje')
                    ->schema([
                        Forms\Components\Select::make('event_template_program_point_id')
                            ->label('Punkt programu')
                            ->relationship('templatePoint', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('day')
                            ->label('Dzień')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->columnSpan(1),
                        // Order is assigned automatically on create
                        Forms\Components\Select::make('assigned_contractor_id')
                            ->label('Kontrahent')
                            ->relationship('assignedContractor', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set, $state) => $set('latestReservation.contractor_id', $state))
                            ->columnSpanFull(),
                    ])->columns(2)->compact(),

                Forms\Components\Section::make('Czas')
                    ->schema([
                        Forms\Components\TimePicker::make('start_time')
                            ->label('Start')
                            ->seconds(false)
                            ->columnSpan(1),
                        Forms\Components\TimePicker::make('end_time')
                            ->label('Koniec')
                            ->seconds(false)
                            ->helperText('Możesz podać koniec lub czas trwania')
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('duration_hours')
                            ->label('Czas (h)')
                            ->numeric()
                            ->minValue(0)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('Czas (min)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(59)
                            ->columnSpan(1),
                    ])->columns(4)->compact(),

                Forms\Components\Section::make('Ceny i ilości')
                    ->schema([
                        Forms\Components\Select::make('currency_id')
                            ->label('Waluta')
                            ->options(fn () => Currency::orderBy('symbol')
                                ->get()
                                ->mapWithKeys(fn (Currency $currency) => [$currency->id => $currency->symbol . ' — ' . $currency->name]))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('unit_price')
                            ->label('Cena jedn.')
                            ->numeric()
                            ->prefix(fn (Forms\Get $get) => Currency::find($get('currency_id'))?->symbol ?? 'PLN')
                            ->step(0.01)
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                $unit = (float) ($get('unit_price') ?? 0);
                                $qty = (int) ($get('quantity') ?? 1);
                                $set('total_price', $unit * max(1, $qty));
                            })
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Ilość')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                $unit = (float) ($get('unit_price') ?? 0);
                                $qty = (int) ($get('quantity') ?? 1);
                                $set('total_price', $unit * max(1, $qty));
                            })
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('total_price')
                            ->label('Cena całk.')
                            ->numeric()
                            ->prefix(fn (Forms\Get $get) => Currency::find($get('currency_id'))?->symbol ?? 'PLN')
                            ->step(0.01)
                            ->readOnly()
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('convert_to_pln')
                            ->label('Przelicz na PLN')
                            ->helperText('Jeśli włączone, koszty będą przeliczane na PLN wg kursu waluty.')
                            ->default(false)
                            ->columnSpan(1),
                    ])->columns(3)->compact(),

                Forms\Components\Section::make('Rezerwacja')
                    ->schema([
                        Forms\Components\Fieldset::make('Dane rezerwacji')
                            ->relationship('latestReservation')
                            ->schema([
                                Forms\Components\Hidden::make('event_id')
                                    ->default(fn ($livewire) => $livewire->getOwnerRecord()->id),
                                Forms\Components\Select::make('contractor_id')
                                    ->label('Kontrahent')
                                    ->relationship('contractor', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->columnSpanFull(),
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'pending' => 'Oczekuje',
                                        'confirmed' => 'Potwierdzona',
                                        'cancelled' => 'Anulowana',
                                        'paid' => 'Opłacona',
                                    ])
                                    ->default('pending'),
                                Forms\Components\DatePicker::make('due_date')
                                    ->label('Termin płatności'),
                                Forms\Components\TextInput::make('cost')
                                    ->label('Koszt')
                                    ->numeric()
                                    ->prefix('PLN'),
                                Forms\Components\TextInput::make('advance_payment')
                                    ->label('Zaliczka')
                                    ->numeric()
                                    ->prefix('PLN'),
                                Forms\Components\Textarea::make('notes')
                                    ->label('Uwagi')
                                    ->columnSpanFull(),
                            ])
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->compact(),

                Forms\Components\Section::make('Płatności pilota')
                    ->schema([
                        Forms\Components\Toggle::make('pilot_pays')
                            ->label('Pilot opłaca ten punkt')
                            ->inline(false)
                            ->live(),
                        Forms\Components\Select::make('pilot_payment_currency')
                            ->label('Waluta')
                            ->options([
                                'PLN' => 'PLN',
                                'EUR' => 'EUR',
                                'USD' => 'USD',
                                'CZK' => 'CZK',
                                'HUF' => 'HUF',
                                'GBP' => 'GBP',
                            ])
                            ->default('PLN')
                            ->required(fn (Forms\Get $get) => (bool) $get('pilot_pays'))
                            ->visible(fn (Forms\Get $get) => (bool) $get('pilot_pays')),
                        Forms\Components\TextInput::make('pilot_payment_needed')
                            ->label('Kwota potrzebna')
                            ->numeric()
                            ->step(0.01)
                            ->visible(fn (Forms\Get $get) => (bool) $get('pilot_pays')),
                        Forms\Components\TextInput::make('pilot_payment_given')
                            ->label('Kwota przekazana pilotowi')
                            ->numeric()
                            ->step(0.01)
                            ->visible(fn (Forms\Get $get) => (bool) $get('pilot_pays')),
                        Forms\Components\Textarea::make('pilot_payment_notes')
                            ->label('Uwagi (pilot)')
                            ->columnSpanFull()
                            ->visible(fn (Forms\Get $get) => (bool) $get('pilot_pays')),
                    ])
                    ->columns(3)
                    ->compact(),

                Forms\Components\Section::make('Ustawienia')
                    ->schema([
                        Forms\Components\Toggle::make('include_in_program')
                            ->label('W programie')
                            ->default(true)
                            ->inline(false)
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('include_in_calculation')
                            ->label('W kalkulacji')
                            ->default(true)
                            ->inline(false)
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('is_cost')
                            ->label('Jest kosztem')
                            ->default(true)
                            ->inline(false)
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('active')
                            ->label('Aktywny')
                            ->default(true)
                            ->inline(false)
                            ->columnSpan(1),
                    ])->columns(4)->compact(),

                Forms\Components\Section::make('Uwagi')
                    ->schema([
                        Forms\Components\RichEditor::make('notes')
                            ->label('Uwagi')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->columnSpanFull(),
                    ])->collapsed()->compact(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->modifyQueryUsing(
                fn(Builder $query) => $query
                    ->with(['templatePoint', 'parent.templatePoint', 'latestReservation'])
                    ->withCount('children')
                    ->orderBy('day')
                    ->orderByRaw('COALESCE((SELECT p."order" FROM event_program_points p WHERE p.id = event_program_points.parent_id), event_program_points."order")')
                    ->orderByRaw('CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END')
                    ->orderBy('order')
                    ->orderBy('id')
            )
            ->groups([
                Tables\Grouping\Group::make('day')
                    ->label(' ')
                    ->getTitleFromRecordUsing(fn($record) => '━━━━━━ Dzień ' . $record->day . ' ━━━━━━')
                    ->collapsible(false) // Nie pozwalamy na zwijanie
                    ->orderQueryUsing(fn($query, string $direction) => $query->orderBy('day', $direction))
            ])
            ->defaultGroup('day')
            ->recordClasses('pp-actions-vertical')
            ->recordAction(null)
            ->recordUrl(null)
            ->columns([
                Tables\Columns\ViewColumn::make('toggle_children')
                    ->label('')
                    ->view('filament.resources.event-resource.relation-managers.columns.program-point-toggle')
                    ->alignCenter(),

                // Order column removed; ordering is assigned automatically

                Tables\Columns\ViewColumn::make('move_day_actions')
                    ->label('')
                    ->view('filament.resources.event-resource.relation-managers.columns.program-point-day-actions')
                    ->alignCenter(),

                Tables\Columns\ViewColumn::make('move_actions')
                    ->label('')
                    ->view('filament.resources.event-resource.relation-managers.columns.program-point-move-actions')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('time_range')
                    ->label('Start–Koniec')
                    ->getStateUsing(fn(EventProgramPoint $record) => $this->getTimeRangeDisplay($record))
                    ->html()
                    ->alignCenter()
                    ->sortable(false),

                Tables\Columns\TextColumn::make('templatePoint.name')
                    ->label('Punkt programu')
                    ->formatStateUsing(function ($state, EventProgramPoint $record) {
                        $this->ensureContractorSelection($record);

                        return view('filament.resources.event-resource.relation-managers.program-point-column-content', [
                            'point' => $record,
                            'manager' => $this,
                            'contractors' => $this->getContractorOptions(),
                        ])->render();
                    })
                    ->html()
                    ->extraAttributes(function (EventProgramPoint $record) {
                        return $record->parent_id ? ['style' => 'padding-left: 1.5rem;'] : [];
                    })
                    ->searchable()
                    ->sortable(false),

                Tables\Columns\IconColumn::make('is_cost')
                    ->label('K')
                    ->tooltip(fn(EventProgramPoint $record) => $this->getPricingTooltip($record))
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('include_in_program')
                    ->label('P')
                    ->tooltip('W programie')
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('include_in_calculation')
                    ->label('C')
                    ->tooltip('W kalkulacji')
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('pilot_pays')
                    ->label('Pi')
                    ->tooltip('Pilot opłaca')
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('active')
                    ->label('A')
                    ->tooltip('Aktywny')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('day')
                    ->label('Dzień')
                    ->options(function () {
                        $maxDay = $this->getOwnerRecord()->eventTemplate->duration_days ?? 3;
                        $options = [];
                        for ($i = 1; $i <= $maxDay; $i++) {
                            $options[$i] = "Dzień {$i}";
                        }
                        return $options;
                    }),

                Tables\Filters\TernaryFilter::make('include_in_program')
                    ->label('Uwzględniony w programie'),

                Tables\Filters\TernaryFilter::make('include_in_calculation')
                    ->label('Uwzględniony w kalkulacji'),

                Tables\Filters\TernaryFilter::make('active')
                    ->label('Aktywny'),
            ])
            ->headerActions([
                // 'show_summary' removed as it is now a main tab in EditEvent

                Tables\Actions\Action::make('add_program_point')
                    ->label('Dodaj punkt programu')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->modalHeading('Dodaj punkt programu do imprezy')
                    ->modalDescription('Wybierz punkt programu z biblioteki szablonów')
                    ->modalWidth('7xl')
                    ->modalSubmitActionLabel('Dodaj punkt')
                    ->modalCancelActionLabel('Anuluj')
                    ->form([
                        Forms\Components\Select::make('event_template_program_point_id')
                            ->label('Wybierz punkt programu')
                            ->searchable(['name', 'description'])
                            ->preload()
                            ->required()
                            ->options(function () {
                                return EventTemplateProgramPoint::all()
                                    ->mapWithKeys(function ($point) {
                                        // Przygotowujemy bogate dane do wyświetlenia
                                        $duration = $point->duration_hours . 'h';
                                        if ($point->duration_minutes > 0) {
                                            $duration .= ' ' . $point->duration_minutes . 'min';
                                        }

                                        $tags = $point->tags->pluck('name')->join(', ');
                                        $price = number_format($point->unit_price, 2) . ' ' . ($point->currency->code ?? 'PLN');

                                        $description = strip_tags($point->description ?? '');
                                        $shortDescription = strlen($description) > 100
                                            ? substr($description, 0, 100) . '...'
                                            : $description;

                                        $officeNotes = strip_tags($point->office_notes ?? '');
                                        $shortOfficeNotes = strlen($officeNotes) > 50
                                            ? substr($officeNotes, 0, 50) . '...'
                                            : $officeNotes;

                                        $html = '<div class="space-y-2">';
                                        $html .= '<div class="font-semibold text-gray-900">' . e($point->name) . '</div>';

                                        if ($shortDescription) {
                                            $html .= '<div class="text-sm text-gray-600">📝 ' . e($shortDescription) . '</div>';
                                        }

                                        if ($tags) {
                                            $html .= '<div class="text-xs text-blue-600">🏷️ ' . e($tags) . '</div>';
                                        }

                                        $html .= '<div class="flex gap-4 text-xs text-gray-500">';
                                        $html .= '<span>⏱️ ' . e($duration) . '</span>';
                                        $html .= '<span>💰 ' . e($price) . '</span>';
                                        $html .= '</div>';

                                        if ($shortOfficeNotes) {
                                            $html .= '<div class="text-xs text-orange-600">📋 ' . e($shortOfficeNotes) . '</div>';
                                        }

                                        $html .= '</div>';

                                        return [$point->id => $html];
                                    });
                            })
                            ->allowHtml()
                            ->placeholder('Wybierz punkt programu z biblioteki...')
                            ->helperText('Wybierz punkt programu z dostępnych szablonów')
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if ($state) {
                                    $templatePoint = EventTemplateProgramPoint::find($state);
                                    if ($templatePoint) {
                                        $set('unit_price', $templatePoint->unit_price);
                                        $set('currency_id', $templatePoint->currency_id);
                                        $set('convert_to_pln', (bool) ($templatePoint->convert_to_pln ?? false));
                                        $set('total_price', ($templatePoint->unit_price ?? 0) * ((int) ($get('quantity') ?? 1)));
                                    }
                                }
                            }),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('day')
                                    ->label('Dzień')
                                    ->options(function () {
                                        $maxDay = $this->getOwnerRecord()->eventTemplate->duration_days ?? 3;
                                        $options = [];
                                        for ($i = 1; $i <= $maxDay; $i++) {
                                            $options[$i] = "Dzień {$i}";
                                        }
                                        return $options;
                                    })
                                    ->default(1)
                                    ->required(),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('currency_id')
                                    ->label('Waluta')
                                    ->options(fn () => Currency::orderBy('symbol')
                                        ->get()
                                        ->mapWithKeys(fn (Currency $currency) => [$currency->id => $currency->symbol . ' — ' . $currency->name]))
                                    ->searchable()
                                    ->preload()
                                    ->live(),

                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Cena jednostkowa')
                                    ->numeric()
                                    ->prefix(fn (Forms\Get $get) => Currency::find($get('currency_id'))?->symbol ?? 'PLN')
                                    ->step(0.01)
                                    ->helperText('Zostanie automatycznie wypełniona z szablonu')
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                        $unit = (float) ($get('unit_price') ?? 0);
                                        $qty = (int) ($get('quantity') ?? 1);
                                        $set('total_price', $unit * max(1, $qty));
                                    }),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Ilość')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                                        $unit = (float) ($get('unit_price') ?? 0);
                                        $qty = (int) ($get('quantity') ?? 1);
                                        $set('total_price', $unit * max(1, $qty));
                                    }),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('total_price')
                                    ->label('Cena całk.')
                                    ->numeric()
                                    ->prefix(fn (Forms\Get $get) => Currency::find($get('currency_id'))?->symbol ?? 'PLN')
                                    ->step(0.01)
                                    ->readOnly(),

                                Forms\Components\Toggle::make('convert_to_pln')
                                    ->label('Przelicz na PLN')
                                    ->helperText('Jeśli włączone, koszty będą przeliczane na PLN wg kursu waluty.')
                                    ->default(false),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Toggle::make('pilot_pays')
                                    ->label('Pilot opłaca ten punkt')
                                    ->inline(false)
                                    ->live(),
                                Forms\Components\Select::make('pilot_payment_currency')
                                    ->label('Waluta')
                                    ->options([
                                        'PLN' => 'PLN',
                                        'EUR' => 'EUR',
                                        'USD' => 'USD',
                                        'CZK' => 'CZK',
                                        'HUF' => 'HUF',
                                        'GBP' => 'GBP',
                                    ])
                                    ->default('PLN')
                                    ->required(fn (Forms\Get $get) => (bool) $get('pilot_pays'))
                                    ->visible(fn (Forms\Get $get) => (bool) $get('pilot_pays')),
                                Forms\Components\TextInput::make('pilot_payment_needed')
                                    ->label('Kwota potrzebna')
                                    ->numeric()
                                    ->step(0.01)
                                    ->visible(fn (Forms\Get $get) => (bool) $get('pilot_pays')),
                                Forms\Components\TextInput::make('pilot_payment_given')
                                    ->label('Kwota przekazana pilotowi')
                                    ->numeric()
                                    ->step(0.01)
                                    ->visible(fn (Forms\Get $get) => (bool) $get('pilot_pays')),
                                Forms\Components\Textarea::make('pilot_payment_notes')
                                    ->label('Uwagi (pilot)')
                                    ->columnSpanFull()
                                    ->visible(fn (Forms\Get $get) => (bool) $get('pilot_pays')),
                            ]),

                        Forms\Components\RichEditor::make('notes')
                            ->label('Uwagi specjalne dla tej imprezy')
                            ->placeholder('Dodatkowe uwagi specyficzne dla tej imprezy...')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\Toggle::make('include_in_program')
                                    ->label('Uwzględnij w programie')
                                    ->default(true),

                                Forms\Components\Toggle::make('include_in_calculation')
                                    ->label('Uwzględnij w kalkulacji')
                                    ->default(true),

                                Forms\Components\Toggle::make('is_cost')
                                    ->label('Jest kosztem')
                                    ->default(true),

                                Forms\Components\Toggle::make('active')
                                    ->label('Aktywny')
                                    ->default(true),
                            ]),
                    ])
                    ->action(function (array $data) {
                        // Pobieramy szablon punktu programu
                        $templatePoint = EventTemplateProgramPoint::find($data['event_template_program_point_id']);

                        // Tworzymy nowy punkt programu dla imprezy
                        $unitPrice = $data['unit_price'] ?? $templatePoint->unit_price ?? 0;
                        $quantity = (int) ($data['quantity'] ?? 1);

                        $this->getOwnerRecord()->programPoints()->create([
                            'event_template_program_point_id' => $data['event_template_program_point_id'],
                            'day' => $data['day'],
                            'unit_price' => $unitPrice,
                            'quantity' => $quantity,
                            'total_price' => $unitPrice * $quantity,
                            'currency_id' => $data['currency_id'] ?? $templatePoint->currency_id ?? null,
                            'convert_to_pln' => (bool) ($data['convert_to_pln'] ?? $templatePoint->convert_to_pln ?? false),
                            'notes' => $data['notes'],
                            'include_in_program' => $data['include_in_program'],
                            'include_in_calculation' => $data['include_in_calculation'],
                            'is_cost' => $data['is_cost'],
                            'active' => $data['active'],
                            'pilot_pays' => (bool) ($data['pilot_pays'] ?? false),
                            'pilot_payment_currency' => $data['pilot_payment_currency'] ?? 'PLN',
                            'pilot_payment_needed' => $data['pilot_payment_needed'] ?? null,
                            'pilot_payment_given' => $data['pilot_payment_given'] ?? null,
                            'pilot_payment_notes' => $data['pilot_payment_notes'] ?? null,
                        ]);
                    }),

                Tables\Actions\Action::make('copy_from_template')
                    ->label('Skopiuj z szablonu')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function () {
                        $event = $this->getOwnerRecord();
                        $event->copyProgramPointsFromTemplate();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Skopiuj program z szablonu')
                    ->modalDescription('To działanie skopiuje wszystkie punkty programu z szablonu. Istniejące punkty zostaną zastąpione.')
                    ->visible(fn() => $this->getOwnerRecord()->programPoints()->count() === 0),

                Tables\Actions\Action::make('recalculate_event')
                    ->label('Przelicz dla imprezy')
                    ->icon('heroicon-o-calculator')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Przelicz kalkulacje')
                    ->modalDescription('To działanie przelicza wszystkie koszty dla imprezy na podstawie aktualnych parametrów.')
                    ->action(function () {
                        // Log start
                        \Illuminate\Support\Facades\Log::info('Start recalculate_event action for event ' . $this->getOwnerRecord()->id);
                        
                        try {
                            \Illuminate\Support\Facades\DB::transaction(function () {
                                $event = $this->getOwnerRecord();
                                $calculator = new \App\Services\EventPriceCalculator();
                                $calculator->calculateForEvent($event);
                            });

                            \Filament\Notifications\Notification::make()
                                ->title('Kalkulacja wykonana')
                                ->success()
                                ->send();

                            $this->refreshCalculations();
                            
                            \Illuminate\Support\Facades\Log::info('End recalculate_event action - success');
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::error('Error in recalculate_event: ' . $e->getMessage());
                            \Filament\Notifications\Notification::make()
                                ->title('Błąd kalkulacji')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('create_snapshot')
                    ->label('Utwórz snapshot')
                    ->icon('heroicon-o-camera')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('name')
                            ->label('Nazwa snapshotu')
                            ->required()
                            ->maxLength(255)
                            ->default('Snapshot kalkulacji ' . now()->format('d.m.Y H:i')),

                        \Filament\Forms\Components\Textarea::make('description')
                            ->label('Opis')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Opisz powód utworzenia tego snapshotu'),
                    ])
                    ->action(function (array $data) {
                        $event = $this->getOwnerRecord();
                        $event->createManualSnapshot($data['name'], $data['description']);

                        \Filament\Notifications\Notification::make()
                            ->title('Snapshot utworzony')
                            ->success()
                            ->send();

                        $this->refreshCalculations();
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('tasks')
                    ->label('Zadania')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('warning')
                    ->modalContent(fn(EventProgramPoint $record) => view('livewire.task-manager-wrapper', ['taskable' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false),

                Tables\Actions\Action::make('duplicate')
                    ->label('Duplikuj')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('success')
                    ->action(fn(EventProgramPoint $record) => $record->duplicate()),

                Tables\Actions\Action::make('move_up')
                    ->label('')
                    ->icon('heroicon-o-arrow-up')
                    ->color('gray')
                    ->hidden()
                    ->disabled(function (EventProgramPoint $record): bool {
                        return !EventProgramPoint::where('event_id', $record->event_id)
                            ->where('day', $record->day)
                            ->where('order', '<', $record->order)
                            ->exists();
                    })
                    ->action(function (EventProgramPoint $record): void {
                        $previous = EventProgramPoint::where('event_id', $record->event_id)
                            ->where('day', $record->day)
                            ->where('order', '<', $record->order)
                            ->orderBy('order', 'desc')
                            ->first();

                        if (!$previous) {
                            return;
                        }

                        DB::transaction(function () use ($record, $previous) {
                            $currentOrder = $record->order;
                            $record->update(['order' => $previous->order]);
                            $previous->update(['order' => $currentOrder]);
                        });
                    }),

                Tables\Actions\Action::make('move_down')
                    ->label('')
                    ->icon('heroicon-o-arrow-down')
                    ->color('gray')
                    ->hidden()
                    ->disabled(function (EventProgramPoint $record): bool {
                        return !EventProgramPoint::where('event_id', $record->event_id)
                            ->where('day', $record->day)
                            ->where('order', '>', $record->order)
                            ->exists();
                    })
                    ->action(function (EventProgramPoint $record): void {
                        $next = EventProgramPoint::where('event_id', $record->event_id)
                            ->where('day', $record->day)
                            ->where('order', '>', $record->order)
                            ->orderBy('order')
                            ->first();

                        if (!$next) {
                            return;
                        }

                        DB::transaction(function () use ($record, $next) {
                            $currentOrder = $record->order;
                            $record->update(['order' => $next->order]);
                            $next->update(['order' => $currentOrder]);
                        });
                    }),

                Tables\Actions\Action::make('move_next_day')
                    ->label('+D')
                    ->color('danger')
                    ->hidden()
                    ->disabled(function (EventProgramPoint $record): bool {
                        $maxDay = $this->getOwnerRecord()->eventTemplate->duration_days ?? 1;
                        return $record->day >= $maxDay;
                    })
                    ->action(function (EventProgramPoint $record): void {
                        $maxDay = $this->getOwnerRecord()->eventTemplate->duration_days ?? 1;
                        if ($record->day < $maxDay) {
                            $record->moveToDay($record->day + 1);
                        }
                    }),

                Tables\Actions\Action::make('move_prev_day')
                    ->label('-D')
                    ->color('danger')
                    ->hidden()
                    ->disabled(fn(EventProgramPoint $record): bool => $record->day <= 1)
                    ->action(function (EventProgramPoint $record): void {
                        if ($record->day > 1) {
                            $record->moveToDay($record->day - 1);
                        }
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (EventProgramPoint $record) {
                        $reservation = $record->latestReservation;
                        if ($reservation) {
                            $this->deletionReservationAlert = [
                                'pointName' => $record->templatePoint?->name ?? $record->name ?? 'punkt programu',
                                'reservation' => $reservation,
                            ];
                        } else {
                            $this->deletionReservationAlert = null;
                        }
                    })
                    ->after(function () {
                        if (! $this->deletionReservationAlert) {
                            return;
                        }

                        $reservation = $this->deletionReservationAlert['reservation'];
                        $pointName = $this->deletionReservationAlert['pointName'];

                        Notification::make()
                            ->title('Usunięto punkt programu')
                            ->warning()
                            ->body("Punkt programu '{$pointName}' został usunięty. Ostatnia rezerwacja (ID {$reservation->id}) została utracona — status: {$reservation->status}, zaliczka: " . number_format($reservation->advance_payment ?? 0, 2) . " PLN.")
                            ->send();

                        $this->deletionReservationAlert = null;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Brak punktów programu')
            ->emptyStateDescription('Dodaj punkty programu lub skopiuj je z szablonu.')
            ->emptyStateIcon('heroicon-o-calendar-days');
    }

    public function reorderTable(array $order): void
    {
        try {
            \Illuminate\Support\Facades\Log::info('Początek reorderTable Event z grupowaniem', ['order' => $order]);

            \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
                // Obsługa dwóch formatów payloadu:
                // 1) prosty: [id1, id2, id3]
                // 2) obiektowy: [{id:1, parent_id: null, day:1, order:1}, ...]
                $ids = [];
                $objectPayload = false;
                if (!empty($order) && is_array($order) && isset($order[0]) && is_array($order[0]) && isset($order[0]['id'])) {
                    $objectPayload = true;
                    foreach ($order as $item) {
                        $ids[] = $item['id'];
                    }
                } else {
                    $ids = $order;
                }

                // Pobieramy wszystkie rekordy do przetwarzania
                $records = EventProgramPoint::whereIn('id', $ids)->get()->keyBy('id');

                // Pobieramy aktualną strukturę grup (dni) z tabeli
                $currentDays = $this->getOwnerRecord()
                    ->programPoints()
                    ->select('day')
                    ->distinct()
                    ->orderBy('day')
                    ->pluck('day')
                    ->toArray();

                // Jeśli nie ma dni, tworzymy przynajmniej dzień 1
                if (empty($currentDays)) {
                    $currentDays = [1];
                }

                // Generujemy mapowanie pozycji do dni na podstawie aktualnego sortowania tabeli
                $dayMapping = [];
                $itemsPerDay = [];

                // Najpierw liczymy ile elementów jest w każdym dniu
                foreach ($records as $record) {
                    $itemsPerDay[$record->day] = ($itemsPerDay[$record->day] ?? 0) + 1;
                }

                // Tworzymy mapowanie pozycji globalnej na dzień i pozycję lokalną
                $globalPosition = 0;
                foreach ($currentDays as $day) {
                    $itemsInDay = $itemsPerDay[$day] ?? 0;
                    for ($i = 0; $i < $itemsInDay; $i++) {
                        $dayMapping[$globalPosition] = [
                            'day' => $day,
                            'local_order' => $i + 1
                        ];
                        $globalPosition++;
                    }
                }

                // Aktualizujemy rekordy zgodnie z nową kolejnością
                if ($objectPayload) {
                    // payload zawiera pełne informacje
                    foreach ($order as $item) {
                        if (!is_array($item) || !array_key_exists('id', $item)) {
                            continue;
                        }
                        $recordId = $item['id'];
                        if (!isset($records[$recordId])) {
                            continue;
                        }
                        $newDay = $item['day'] ?? $records[$recordId]->day;
                        $newOrder = $item['order'] ?? $records[$recordId]->order;
                        $newParent = array_key_exists('parent_id', $item) ? ($item['parent_id'] !== '' ? (int)$item['parent_id'] : null) : ($records[$recordId]->parent_id ? (int)$records[$recordId]->parent_id : null);

                        \Illuminate\Support\Facades\Log::info('Aktualizacja rekordu Event (obiektowy)', [
                            'recordId' => $recordId,
                            'oldDay' => $records[$recordId]->day,
                            'newDay' => $newDay,
                            'newOrder' => $newOrder,
                            'newParent' => $newParent,
                        ]);

                        $result = EventProgramPoint::where('id', $recordId)
                            ->update([
                                'day' => $newDay,
                                'order' => $newOrder,
                                'parent_id' => $newParent,
                            ]);

                        \Illuminate\Support\Facades\Log::info('Wynik aktualizacji Event', ['result' => $result]);
                    }
                } else {
                    // stary prosty format - zachowujemy dotychczasową logikę mapowania dni i pozycji
                    foreach ($order as $index => $recordId) {
                        if (isset($records[$recordId]) && isset($dayMapping[$index])) {
                            $newDay = $dayMapping[$index]['day'];
                            $newOrder = $dayMapping[$index]['local_order'];

                            \Illuminate\Support\Facades\Log::info('Aktualizacja rekordu Event', [
                                'recordId' => $recordId,
                                'oldDay' => $records[$recordId]->day,
                                'newDay' => $newDay,
                                'newOrder' => $newOrder,
                                'globalIndex' => $index
                            ]);

                            $result = EventProgramPoint::where('id', $recordId)
                                ->update([
                                    'day' => $newDay,
                                    'order' => $newOrder
                                ]);

                            \Illuminate\Support\Facades\Log::info('Wynik aktualizacji Event', ['result' => $result]);
                        }
                    }
                }
            });

            \Illuminate\Support\Facades\Log::info('Koniec reorderTable Event - sukces');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Błąd podczas przestawiania Event: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function getTableQueryForPage(): Builder
    {
        $query = parent::getTableQueryForPage();

        // Dodajemy puste rekordy dla dni bez punktów programu
        $event = $this->getOwnerRecord();
        $maxDay = $event->eventTemplate->duration_days ?? 3;

        // Sprawdzamy które dni mają punkty programu
        $usedDays = $event->programPoints()->distinct('day')->pluck('day')->toArray();

        // Dla dni bez punktów, tworzymy "phantom" rekordy (nie zapisujemy do bazy)
        // To jest tylko do wyświetlenia pustych grup
        for ($day = 1; $day <= $maxDay; $day++) {
            if (!in_array($day, $usedDays)) {
                // Dla pustych dni Filament automatycznie pokaże pustą grupę
                // gdy użyjemy defaultGroup
            }
        }

        return $query;
    }
}
