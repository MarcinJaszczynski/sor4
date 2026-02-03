<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Illuminate\Support\Facades\Auth;

class Task extends Model implements Sortable
{
    use HasFactory, SoftDeletes, SortableTrait;

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'status_id',
        'priority',
        'author_id',
        'assignee_id',
        'parent_id',
        'order',
        'taskable_id',
        'taskable_type',
        'last_activity_at',
        'last_activity_type',
        'last_activity_label',
        'last_activity_user_id',
    ];

    public function emails(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(EmailMessage::class, 'emailable', 'emailables');
    }

    protected $casts = [
        'due_date' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    public $sortable = [
        'order_column_name' => 'order',
        'sort_when_creating' => true,
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @deprecated Use assignees() instead for multiple assignment support
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function assignees(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TaskStatus::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TaskHistory::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    public function tasks(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function taskable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function lastActivityUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_activity_user_id');
    }

    /**
     * Scope to show only tasks visible to a specific user
     * (author, assignee, in assignees, or related via parent/subtask hierarchy)
     */
    public function scopeVisibleTo($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            // Zadania bezpośrednio przypisane do użytkownika
            $q->where('author_id', $userId)
                ->orWhere('assignee_id', $userId)
                ->orWhereHas('assignees', fn ($aq) => $aq->where('users.id', $userId))
                // Zadania nadrzędne do zadań użytkownika (widzi parent swoich zadań)
                ->orWhereHas('subtasks', function ($sq) use ($userId) {
                    $sq->where('author_id', $userId)
                        ->orWhere('assignee_id', $userId)
                        ->orWhereHas('assignees', fn ($aq) => $aq->where('users.id', $userId));
                })
                // Podzadania do zadań użytkownika (widzi subtasks swoich zadań)
                ->orWhereHas('parent', function ($pq) use ($userId) {
                    $pq->where('author_id', $userId)
                        ->orWhere('assignee_id', $userId)
                        ->orWhereHas('assignees', fn ($aq) => $aq->where('users.id', $userId));
                });
        });
    }

    protected static function booted()
    {
        static::updating(function (Task $task) {
            $dirty = $task->getDirty();
            if (empty($dirty)) {
                return;
            }

            $fieldsToLog = ['status_id', 'due_date', 'title', 'description', 'priority', 'assignee_id'];
            $userId = Auth::id();

            foreach ($dirty as $field => $newValue) {
                if (! in_array($field, $fieldsToLog, true)) {
                    continue;
                }

                $oldValue = $task->getOriginal($field);

                TaskHistory::create([
                    'task_id' => $task->id,
                    'user_id' => $userId,
                    'field' => $field,
                    'old_value' => is_scalar($oldValue) || is_null($oldValue) ? $oldValue : json_encode($oldValue),
                    'new_value' => is_scalar($newValue) || is_null($newValue) ? $newValue : json_encode($newValue),
                    'description' => $field === 'status_id'
                        ? 'Status zmieniony'
                        : null,
                ]);
            }
        });
    }
} 