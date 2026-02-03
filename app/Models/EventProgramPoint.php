<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EventProgramPoint extends Model
{
    use HasFactory;

    protected $touches = ['event'];

    protected $fillable = [
        'event_id',
        'event_template_program_point_id',
        'name',
        'description',
        'office_notes',
        'pilot_notes',
        'day',
        'order',
        'duration_hours',
        'duration_minutes',
        'start_time',
        'end_time',
        'featured_image',
        'gallery_images',
        'unit_price',
        'quantity',
        'total_price',
        'notes',
        'include_in_program',
        'include_in_calculation',
        'active',
        'show_title_style',
        'show_description',
        'group_size',
        'currency_id',
        'convert_to_pln',
        'parent_id',
        'is_cost',
        'assigned_contractor_id',
        'pilot_pays',
        'pilot_payment_currency',
        'pilot_payment_needed',
        'pilot_payment_given',
        'pilot_payment_notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'include_in_program' => 'boolean',
        'include_in_calculation' => 'boolean',
        'active' => 'boolean',
        'show_title_style' => 'boolean',
        'show_description' => 'boolean',
        'gallery_images' => 'array',
        'convert_to_pln' => 'boolean',
        'is_cost' => 'boolean',
        'start_time' => 'string',
        'end_time' => 'string',
        'assigned_contractor_id' => 'integer',
        'pilot_pays' => 'boolean',
        'pilot_payment_needed' => 'decimal:2',
        'pilot_payment_given' => 'decimal:2',
    ];

    public function tasks(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Parent program point (for hierarchical structure)
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Child program points
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order');
    }

    protected static function booted()
    {
        static::creating(function ($point) {
            // Auto-assign order when creating if not provided
            if (empty($point->order)) {
                $max = self::where('event_id', $point->event_id)
                    ->where('day', $point->day ?? 1)
                    ->max('order');
                $point->order = ($max !== null) ? ($max + 1) : 1;
            }

            // Defaults for new points
            if ($point->is_cost === null) {
                $point->is_cost = true;
            }
            if ($point->include_in_calculation === null) {
                $point->include_in_calculation = true;
            }
            if ($point->unit_price === null) {
                $point->unit_price = 0;
            }
            if ($point->total_price === null) {
                $point->total_price = 0;
            }
            if (empty($point->currency_id)) {
                $pln = \App\Models\Currency::where('symbol', 'PLN')->first();
                $point->currency_id = $pln?->id ?? 1;
            }
        });

        static::saving(function ($point) {
            // Automatycznie oblicz total_price
            $point->total_price = ($point->unit_price ?? 0) * ($point->quantity ?? 1);

            $startTime = $point->start_time ? Carbon::createFromTimeString($point->start_time) : null;
            $endTime = $point->end_time ? Carbon::createFromTimeString($point->end_time) : null;
            $hasDuration = ($point->duration_hours !== null) || ($point->duration_minutes !== null);
            $durationMinutes = ((int) ($point->duration_hours ?? 0) * 60) + (int) ($point->duration_minutes ?? 0);

            if ($startTime) {
                if (!$endTime && $hasDuration && $durationMinutes > 0) {
                    $endTime = $startTime->copy()->addMinutes($durationMinutes);
                    $point->end_time = $endTime->format('H:i');
                }

                if ($endTime) {
                    $diffMinutes = $endTime->diffInMinutes($startTime, false);
                    if ($diffMinutes < 0) {
                        $diffMinutes = $endTime->copy()->addDay()->diffInMinutes($startTime);
                    }

                    $point->duration_hours = intdiv($diffMinutes, 60);
                    $point->duration_minutes = $diffMinutes % 60;
                }
            }
        });

        static::saved(function ($point) {
            if ($point->is_cost) {
                $cost = $point->cost;
                if ($cost) {
                    $cost->update([
                        'name' => $point->name,
                        'amount' => $point->total_price,
                        'currency_id' => $point->currency_id ?? $point->templatePoint->currency_id ?? null,
                    ]);
                } else {
                    $point->cost()->create([
                        'event_id' => $point->event_id,
                        'name' => $point->name,
                        'amount' => $point->total_price,
                        'currency_id' => $point->currency_id ?? $point->templatePoint->currency_id ?? null,
                        'payment_date' => now(),
                        'is_paid' => false,
                    ]);
                }
            } else {
                if ($point->cost) {
                    $point->cost->delete();
                }
            }
        });

        static::updated(function ($point) {
            $event = $point->event;
            $changes = $point->getChanges();

            foreach ($changes as $field => $newValue) {
                $oldValue = $point->getOriginal($field);
                $event->logHistory(
                    'program_changed',
                    "program_point.{$field}",
                    $oldValue,
                    $newValue,
                    "Zmieniono {$field} w punkcie programu: {$point->templatePoint->name}"
                );
            }

            // Przelicz całkowity koszt imprezy
            $event->calculateTotalCost();
        });

        static::created(function ($point) {
            $event = $point->event;
            $event->logHistory(
                'program_added',
                null,
                null,
                $point->toArray(),
                "Dodano punkt programu: {$point->templatePoint->name}"
            );

            // Przelicz całkowity koszt imprezy
            $event->calculateTotalCost();
        });

        static::deleted(function ($point) {
            $event = $point->event;
            $event->logHistory(
                'program_removed',
                null,
                $point->toArray(),
                null,
                "Usunięto punkt programu: {$point->templatePoint->name}"
            );

            // Przelicz całkowity koszt imprezy
            $event->calculateTotalCost();
        });
    }

    /**
     * Impreza
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Szablon punktu programu
     */
    public function templatePoint(): BelongsTo
    {
        return $this->belongsTo(EventTemplateProgramPoint::class, 'event_template_program_point_id');
    }

    public function assignedContractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class, 'assigned_contractor_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'event_program_point_id');
    }

    public function latestReservation(): HasOne
    {
        return $this->hasOne(Reservation::class, 'event_program_point_id')->latestOfMany();
    }

    public function cost()
    {
        return $this->hasOne(EventCost::class, 'event_program_point_id');
    }

    /**
     * Oblicz koszt całkowity na podstawie ceny jednostkowej i ilości
     */
    public function calculateTotalPrice(): void
    {
        $this->total_price = ($this->unit_price ?? 0) * ($this->quantity ?? 1);
        $this->save();
    }

    /**
     * Duplikuj punkt programu
     */
    public function duplicate(): self
    {
        return self::create([
            'event_id' => $this->event_id,
            'event_template_program_point_id' => $this->event_template_program_point_id,
            'day' => $this->day,
            'order' => $this->getNextOrderInDay(),
            'unit_price' => $this->unit_price,
            'quantity' => $this->quantity,
            'total_price' => $this->total_price,
            'currency_id' => $this->currency_id,
            'convert_to_pln' => $this->convert_to_pln,
            'group_size' => $this->group_size,
            'notes' => $this->notes,
            'include_in_program' => $this->include_in_program,
            'include_in_calculation' => $this->include_in_calculation,
            'active' => $this->active,
            'is_cost' => $this->is_cost,
            'show_title_style' => $this->show_title_style,
            'show_description' => $this->show_description,
        ]);
    }

    /**
     * Pobierz następny numer kolejności w danym dniu
     */
    private function getNextOrderInDay(): int
    {
        $maxOrder = self::where('event_id', $this->event_id)
            ->where('day', $this->day)
            ->max('order');

        return ($maxOrder ?? 0) + 1;
    }

    /**
     * Przenieś do innego dnia
     */
    public function moveToDay(int $newDay): void
    {
        $oldDay = $this->day;
        $this->day = $newDay;
        $this->order = $this->getNextOrderInDay();
        $this->save();

        $this->event->logHistory(
            'program_moved',
            'program_point.day',
            $oldDay,
            $newDay,
            "Przeniesiono punkt programu '{$this->templatePoint->name}' z dnia {$oldDay} do dnia {$newDay}"
        );
    }
}
