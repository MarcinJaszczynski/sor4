<?php

namespace App\Livewire;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TaskManager extends Component
{
    public $taskableType;
    public $taskableId;
    public $tasks = [];
    
    public $title = '';
    public $description = '';
    public $status_id;
    public $priority = 'medium';
    public $assignee_ids = [];
    public $due_date;

    protected $rules = [
        'title' => 'required|string|max:255',
        'status_id' => 'required|exists:task_statuses,id',
        'assignee_ids' => 'array',
    ];

    public function mount($taskable)
    {
        $this->taskableType = get_class($taskable);
        $this->taskableId = $taskable->id;
        $this->resetForm();
        $this->loadTasks();
    }

    public function loadTasks()
    {
        $this->tasks = Task::where('taskable_type', $this->taskableType)
            ->where('taskable_id', $this->taskableId)
            ->visibleTo(Auth::id())
            ->with(['status', 'assignees'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function resetForm()
    {
        $this->title = '';
        $this->description = '';
        $this->status_id = TaskStatus::where('is_default', true)->value('id') ?? TaskStatus::first()?->id;
        $this->priority = 'medium';
        $this->assignee_ids = [Auth::id()];
        $this->due_date = null;
    }

    public function saveTask()
    {
        $this->validate();

        $task = Task::create([
            'title' => $this->title,
            'description' => $this->description,
            'status_id' => $this->status_id,
            'priority' => $this->priority,
            // 'assignee_id' => $this->assignee_id, // Deprecated
            'due_date' => $this->due_date,
            'author_id' => Auth::id(),
            'taskable_type' => $this->taskableType,
            'taskable_id' => $this->taskableId,
        ]);

        if (!empty($this->assignee_ids)) {
            $task->assignees()->sync($this->assignee_ids);
        }

        $this->resetForm();
        $this->loadTasks();
        $this->dispatch('task-added');
    }

    public function deleteTask($taskId)
    {
        $task = Task::find($taskId);
        if ($task && $task->taskable_type === $this->taskableType && $task->taskable_id == $this->taskableId) {
            $task->delete();
            $this->loadTasks();
        }
    }

    public function render()
    {
        return view('livewire.task-manager', [
            'statuses' => TaskStatus::all(),
            'users' => User::all(),
        ]);
    }
}
