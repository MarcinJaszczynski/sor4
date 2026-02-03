<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PilotExpense extends Model
{
    protected $fillable = [
        'event_id',
        'event_program_point_id',
        'user_id',
        'amount',
        'currency',
        'description',
        'document_image',
        'expense_date',
        'status'
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function eventProgramPoint()
    {
        return $this->belongsTo(EventProgramPoint::class, 'event_program_point_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
