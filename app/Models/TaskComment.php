<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class TaskComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'task_id',
        'user_id',
        'recipient_id',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_comment_user', 'task_comment_id', 'user_id')
            ->withTimestamps();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(TaskComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TaskComment::class, 'parent_id');
    }

    public function scopeVisibleTo(Builder $query, int $userId): Builder
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere('recipient_id', $userId)
                ->orWhereHas('recipients', fn ($rq) => $rq->where('users.id', $userId))
                ->orWhereHas('task', fn ($tq) => $tq->where('author_id', $userId)->orWhere('assignee_id', $userId));
        });
    }

    public function getAuthorIdAttribute(): ?int
    {
        return $this->user_id;
    }

    public function setAuthorIdAttribute(?int $value): void
    {
        $this->attributes['user_id'] = $value;
    }
} 