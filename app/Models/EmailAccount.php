<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailAccount extends Model
{
    protected $guarded = [];

    protected $casts = [
        'password' => 'encrypted', // Auto-encryption via Laravel casts
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sharedUsers()
    {
        return $this->belongsToMany(User::class, 'email_account_user_shared');
    }

    public function messages()
    {
        return $this->hasMany(EmailMessage::class);
    }

    // Scopes to limit visibility
    public function scopeForUser($query, $user)
    {
        // Allow passing null to mean current authenticated user
        $userId = $user instanceof \App\Models\User ? $user->id : ($user ?? auth()->id());

        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere('visibility', 'public')
                ->orWhereHas('sharedUsers', function ($sq) use ($userId) {
                    $sq->where('user_id', $userId);
                });
        });
    }
}
