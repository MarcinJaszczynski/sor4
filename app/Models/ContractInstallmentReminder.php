<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractInstallmentReminder extends Model
{
    protected $fillable = [
        'contract_installment_id',
        'channel',
        'recipient',
        'message',
        'user_id',
        'source',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function installment()
    {
        return $this->belongsTo(ContractInstallment::class, 'contract_installment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
