<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventDocument extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'file_path',
        'type',
        'description',
        'is_visible_office',
        'is_visible_driver',
        'is_visible_hotel',
        'is_visible_pilot',
        'is_visible_client',
    ];

    protected $casts = [
        'is_visible_office' => 'boolean',
        'is_visible_driver' => 'boolean',
        'is_visible_hotel' => 'boolean',
        'is_visible_pilot' => 'boolean',
        'is_visible_client' => 'boolean',
    ];

    public function event(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
