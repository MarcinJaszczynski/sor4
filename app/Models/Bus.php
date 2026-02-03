<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{
    protected $fillable = [
        'name',
        'description',
        'capacity',
        'package_price_per_day',
        'package_km_per_day',
        'extra_km_price',
        'currency',
        'convert_to_pln',
        'contractor_id',
        'is_real',
    ];

    protected $casts = [
        'is_real' => 'boolean',
    ];

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }
}
