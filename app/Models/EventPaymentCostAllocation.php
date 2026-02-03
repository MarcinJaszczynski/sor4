<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventPaymentCostAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'event_payment_id',
        'event_cost_id',
        'contract_id',
        'user_id',
        'amount',
        'note',
        'allocated_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'allocated_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(EventPayment::class, 'event_payment_id');
    }

    public function cost(): BelongsTo
    {
        return $this->belongsTo(EventCost::class, 'event_cost_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
