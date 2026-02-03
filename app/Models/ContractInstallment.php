<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ContractInstallment extends Model
{
    protected $fillable = [
        'contract_id',
        'due_date',
        'amount',
        'is_paid',
        'paid_at',
        'note',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'date',
        'amount' => 'decimal:2',
        'is_paid' => 'boolean',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function reminders()
    {
        return $this->hasMany(ContractInstallmentReminder::class, 'contract_installment_id');
    }

    public function latestReminder(): HasOne
    {
        return $this->hasOne(ContractInstallmentReminder::class, 'contract_installment_id')
            ->latestOfMany('sent_at');
    }
}
