<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class EventPayment extends Model
{
    protected $fillable = [
        'event_id',
        'created_by_user_id',
        'contract_id',
        'amount',
        'currency',
        'payment_date',
        'payment_type_id',
        'description',
        'invoice_number',
        'is_advance',
        'documents',
        'source',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'is_advance' => 'boolean',
        'documents' => 'array',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function paymentType()
    {
        return $this->belongsTo(\App\Models\PaymentType::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(EventPaymentCostAllocation::class, 'event_payment_id');
    }

    public function getAllocatedAmountAttribute(): float
    {
        return (float) ($this->allocations_sum_amount ?? $this->allocations()->sum('amount') ?? 0);
    }

    public function getUnallocatedAmountAttribute(): float
    {
        return max(0.0, (float) $this->amount - (float) $this->allocated_amount);
    }
}
