<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailMessage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date' => 'datetime',
        'to_address' => 'array',
        'cc_address' => 'array',
        'bcc_address' => 'array',
        'is_read' => 'boolean',
        'is_starred' => 'boolean',
    ];

    public function account()
    {
        return $this->belongsTo(EmailAccount::class, 'email_account_id');
    }

    public function attachments()
    {
        return $this->hasMany(EmailAttachment::class);
    }

    public function sharedUsers()
    {
        return $this->belongsToMany(User::class, 'email_message_user_shared');
    }

    // Get related entities (Events, Tasks)
    public function relatedEvents()
    {
        return $this->morphedByMany(Event::class, 'emailable');
    }

    public function relatedTasks()
    {
        return $this->morphedByMany(Task::class, 'emailable');
    }
}
