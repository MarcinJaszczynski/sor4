<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $fillable = [
        'contract_id',
        'uuid',
        'first_name',
        'last_name',
        'email',
        'phone',
        'pesel',
        'birth_date',
        'is_minor',
        'diet_info',
        'seat_number',
        'gender',
        'nationality',
        'document_type',
        'document_number',
        'document_expiry_date',
        'room_type',
        'room_notes',
        'status',
        'cancellation_fee',
        'cancellation_date',
        'cancellation_reason',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'document_expiry_date' => 'date',
        'is_minor' => 'boolean',
        'cancellation_date' => 'date',
        'cancellation_fee' => 'decimal:2',
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

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
