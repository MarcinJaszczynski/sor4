<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        return $this->canAccessTask($user, $task);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        return $this->canAccessTask($user, $task);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        return $this->canAccessTask($user, $task);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return $this->canAccessTask($user, $task);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return $this->canAccessTask($user, $task);
    }

    /**
     * Check if user can access task (directly assigned or via parent/subtask hierarchy)
     */
    protected function canAccessTask(User $user, Task $task): bool
    {
        // Bezpośrednie przypisanie
        if ($task->author_id === $user->id || $task->assignee_id === $user->id) {
            return true;
        }

        // Przypisanie przez relację many-to-many
        if ($task->assignees()->where('users.id', $user->id)->exists()) {
            return true;
        }

        // Dostęp przez zadanie nadrzędne (jeśli user ma dostęp do parenta, może zobaczyć subtask)
        if ($task->parent_id) {
            $parent = Task::find($task->parent_id);
            if ($parent && $this->canAccessTask($user, $parent)) {
                return true;
            }
        }

        // Dostęp przez podzadania (jeśli user ma dostęp do subtaska, może zobaczyć parent)
        $hasAccessViaSubtask = $task->subtasks()
            ->where(function ($q) use ($user) {
                $q->where('author_id', $user->id)
                    ->orWhere('assignee_id', $user->id)
                    ->orWhereHas('assignees', fn ($aq) => $aq->where('users.id', $user->id));
            })
            ->exists();

        if ($hasAccessViaSubtask) {
            return true;
        }

        return false;
    }
}
