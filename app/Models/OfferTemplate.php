<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfferTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'view_name',
        'introduction',
        'terms',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];
}
