<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'event_id',
        'contract_template_id',
        'contract_number',
        'status',
        'content',
        'date_issued',
        'place_issued',
        'uuid',
        'total_amount',
        'locked_price_per_person',
    ];

    protected $casts = [
        'date_issued' => 'date',
        'total_amount' => 'decimal:2',
        'locked_price_per_person' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function addendums()
    {
        return $this->hasMany(ContractAddendum::class);
    }

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function payments()
    {
        return $this->hasMany(EventPayment::class);
    }

    public function installments()
    {
        return $this->hasMany(ContractInstallment::class);
    }

    public function getPaidAmountAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getIsFullyPaidAttribute()
    {
        return $this->paid_amount >= $this->total_amount && $this->total_amount > 0;
    }

    public function tasks()
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function contractTemplate()
    {
        return $this->belongsTo(ContractTemplate::class);
    }
}
