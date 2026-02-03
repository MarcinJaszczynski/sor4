<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractAddendum extends Model
{
    protected $fillable = [
        'contract_id',
        'addendum_number',
        'date_issued',
        'content',
        'changes_summary',
        'amount_change',
        'new_total_amount',
        'status',
        'locked_price_per_person',
    ];

    protected $casts = [
        'date_issued' => 'date',
        'amount_change' => 'decimal:2',
        'new_total_amount' => 'decimal:2',
        'locked_price_per_person' => 'decimal:2',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
