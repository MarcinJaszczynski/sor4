<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_id',
        'event_program_point_id',
        'is_optional',
        'is_included',
        'quantity',
        'custom_price',
        'custom_description',
    ];

    protected $casts = [
        'is_optional' => 'boolean',
        'is_included' => 'boolean',
        'custom_price' => 'decimal:2',
        'quantity' => 'decimal:2',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function programPoint(): BelongsTo
    {
        return $this->belongsTo(EventProgramPoint::class, 'event_program_point_id');
    }
}
