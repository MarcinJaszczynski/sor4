<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventCost extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'category',
        'contractor_id',
        'amount',
        'paid_amount',
        'currency_id',
        'payer_id',
        'payment_type_id',
        'payment_date',
        'invoice_number',
        'is_paid',
        'event_program_point_id',
        'documents',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'is_paid' => 'boolean',
        'documents' => 'array',
    ];

    public function tasks(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function contractor()
    {
        return $this->belongsTo(\App\Models\Contractor::class);
    }

    public function currency()
    {
        return $this->belongsTo(\App\Models\Currency::class);
    }

    public function payer()
    {
        return $this->belongsTo(\App\Models\Payer::class);
    }

    public function paymentType()
    {
        return $this->belongsTo(\App\Models\PaymentType::class);
    }

    public function programPoint()
    {
        return $this->belongsTo(EventProgramPoint::class, 'event_program_point_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(EventPaymentCostAllocation::class, 'event_cost_id');
    }

    public function getAmountPlnAttribute(): float
    {
        $amount = (float) ($this->amount ?? 0);
        $currencyId = $this->currency_id;

        if (! $currencyId) {
            return $amount;
        }

        if (in_array($currencyId, Currency::plnIds(), true)) {
            return $amount;
        }

        $rate = (float) (optional($this->currency)->exchange_rate ?? 1);
        if ($rate <= 0) {
            $rate = 1;
        }

        return $amount * $rate;
    }

    public function getAllocatedAmountAttribute(): float
    {
        return (float) ($this->allocations_sum_amount ?? $this->allocations()->sum('amount') ?? 0);
    }

    public function getRemainingAmountPlnAttribute(): float
    {
        return max(0.0, (float) $this->amount_pln - (float) $this->allocated_amount);
    }
}
