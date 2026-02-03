<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EmailAttachment extends Model
{
    protected $guarded = [];

    public function message()
    {
        return $this->belongsTo(EmailMessage::class, 'email_message_id');
    }

    public function getUrlAttribute()
    {
        // Prefer secure route when possible
        if (app()->runningInConsole()) {
            return Storage::url($this->file_path);
        }

        try {
            return route('attachments.download', $this);
        } catch (\Exception $e) {
            return Storage::url($this->file_path);
        }
    }
}
