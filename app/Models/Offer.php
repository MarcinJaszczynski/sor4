<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_id',
        'offer_template_id',
        'user_id',
        'name',
        'status',
        'participant_count',
        'introduction',
        'summary',
        'terms',
        'cost_per_person',
        'price_per_person',
        'total_price',
        'margin_percent',
        'valid_until',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'cost_per_person' => 'decimal:2',
        'price_per_person' => 'decimal:2',
        'total_price' => 'decimal:2',
        'margin_percent' => 'decimal:2',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(OfferTemplate::class, 'offer_template_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OfferItem::class);
    }
}
