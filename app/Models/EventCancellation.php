<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventCancellation extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'qty',
        'amount',
        'cancellation_date',
        'reason',
    ];

    protected $casts = [
        'cancellation_date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::created(function ($cancellation) {
            $event = $cancellation->event;
            if ($event) {
                $event->participant_count = max(0, $event->participant_count - $cancellation->qty);
                $event->saveQuietly(); // saveQuietly to avoid triggering other event updates if any
            }
        });

        static::deleted(function ($cancellation) {
            $event = $cancellation->event;
            if ($event) {
                $event->participant_count += $cancellation->qty;
                $event->saveQuietly();
            }
        });

        static::updated(function ($cancellation) {
            // Handle qty change
            $originalQty = $cancellation->getOriginal('qty');
            $newQty = $cancellation->qty;
            
            if ($originalQty !== $newQty) {
                $event = $cancellation->event;
                if ($event) {
                    $diff = $newQty - $originalQty;
                    // If new is higher (e.g. 5 -> 7), diff is 2. We need to decrease participants by 2 more.
                    // If new is lower (e.g. 5 -> 3), diff is -2. We need to increase participants by 2.
                    $event->participant_count = max(0, $event->participant_count - $diff);
                    $event->saveQuietly();
                }
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
