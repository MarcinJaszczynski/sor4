<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\EventProgramPoint;
use App\Models\EventTemplateProgramPoint;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class EventInstanceProgramTreeEditor extends Component
{
    public Event $event;
    public $programByDays = [];
    public bool $loaded = false;
    public $showModal = false;
    public $editPoint = null;
    public $modalData = [
        'id' => null,
        'program_point_id' => '',
        'day' => 1,
        'notes' => '',
        'include_in_program' => true,
        'include_in_calculation' => true,
        'active' => true,
    ];
    public $searchProgramPoint = '';
    public bool $searchDropdownOpen = false;
    
    // Task Management
    public $showTaskModal = false;
    public $currentPointIdForTasks = null;
    public $tasksForCurrentPoint = [];
    public $taskModalData = [
        'title' => '',
        'description' => '',
        'status_id' => null,
        'priority' => 'medium',
        'assignee_id' => null,
        'due_date' => null,
    ];

    // Prevent re-rendering after drag & drop updates
    public $skipRender = false;

    protected function rules()
    {
        return [
            'modalData.program_point_id' => 'required|exists:event_template_program_points,id',
            'modalData.day' => 'required|integer|min:1|max:' . ($this->event->duration_days + 1),
            'modalData.notes' => 'nullable|string',
            'modalData.include_in_program' => 'boolean',
            'modalData.include_in_calculation' => 'boolean',
            'modalData.active' => 'boolean',
        ];
    }

    protected $messages = [
        'modalData.program_point_id.required' => 'Pole punkt programu jest wymagane.',
        'modalData.program_point_id.exists' => 'Wybrany punkt programu jest nieprawidłowy.',
        'modalData.day.required' => 'Pole dzień jest wymagane.',
        'modalData.day.integer' => 'Dzień musi być liczbą.',
        'modalData.day.min' => 'Dzień nie może być mniejszy niż 1.',
        'modalData.day.max' => 'Dzień nie może być większy niż liczba dni imprezy plus fakultatywne.',
        'taskModalData.title.required' => 'Tytuł zadania jest wymagany.',
        'taskModalData.title.max' => 'Tytuł zadania nie może być dłuższy niż 255 znaków.',
        'taskModalData.status_id.required' => 'Status zadania jest wymagany.',
        'taskModalData.status_id.exists' => 'Wybrany status jest nieprawidłowy.',
    ];

    public function mount(Event $event)
    {
        $this->event = $event;
    }

    public function load()
    {
        // Called via wire:init from the blade to defer heavy load
        $this->loadProgramByDays();
        $this->loaded = true;
    }

    public function loadProgramByDays()
    {
        // Pobierz tylko główne punkty (parent_id IS NULL)
        $programPoints = $this->event->programPoints()
            ->whereNull('parent_id')
            ->with(['children', 'templatePoint.tags']) // children są ładowane z relacji modelu
            ->orderBy('day', 'asc')
            ->orderBy('order', 'asc')
            ->get();

        $grouped = $programPoints->groupBy('day');
        $days = [];
        
        // Dodaj dni standardowe
        for ($i = 1; $i <= $this->event->duration_days; $i++) {
            $points = $grouped[$i] ?? collect();
            $days[] = [
                'day' => $i,
                'points' => $points->map(function ($point) {
                    $children = $point->children->map(function ($child) {
                        return [
                            'id' => $child->id, // To jest ID EventProgramPoint
                            'name' => $child->name,
                            'description' => $child->description,
                            'office_notes' => $child->office_notes,
                            'duration_hours' => $child->duration_hours,
                            'duration_minutes' => $child->duration_minutes,
                            'featured_image' => $child->featured_image,
                            'gallery_images' => $child->gallery_images,
                            'unit_price' => $child->unit_price,
                            'group_size' => $child->group_size,
                            'tags' => $child->templatePoint && $child->templatePoint->tags ? $child->templatePoint->tags->toArray() : [],
                            'include_in_program' => (bool)$child->include_in_program,
                            'include_in_calculation' => (bool)$child->include_in_calculation,
                            'active' => (bool)$child->active,
                            'show_title_style' => (bool)$child->show_title_style,
                            'show_description' => (bool)$child->show_description,
                        ];
                    })->toArray();
                    return [
                        'id' => $point->id, // To jest ID EventProgramPoint
                        'name' => $point->name,
                        'description' => $point->description,
                        'office_notes' => $point->office_notes,
                        'duration_hours' => $point->duration_hours,
                        'duration_minutes' => $point->duration_minutes,
                        'featured_image' => $point->featured_image,
                        'gallery_images' => $point->gallery_images,
                        'unit_price' => $point->unit_price,
                        'group_size' => $point->group_size,
                        'tags' => $point->templatePoint && $point->templatePoint->tags ? $point->templatePoint->tags->toArray() : [],
                        'day' => $point->day,
                        'order' => $point->order,
                        'pivot_notes' => $point->notes, // Używamy pola notes jako odpowiednik pivot_notes
                        'program_point_id' => $point->event_template_program_point_id,
                        'include_in_program' => (bool)$point->include_in_program,
                        'include_in_calculation' => (bool)$point->include_in_calculation,
                        'active' => (bool)$point->active,
                        'show_title_style' => (bool)$point->show_title_style,
                        'show_description' => (bool)$point->show_description,
                        'children' => $children,
                    ];
                })->sortBy('order')->values()->toArray()
            ];
        }
        
        // Dodaj dzień fakultatywny (duration_days + 1)
        $facultativeDay = $this->event->duration_days + 1;
        $facultativePoints = $grouped[$facultativeDay] ?? collect();
        $days[] = [
            'day' => $facultativeDay,
            'points' => $facultativePoints->map(function ($point) {
                $children = $point->children->map(function ($child) {
                    return [
                        'id' => $child->id,
                        'name' => $child->name,
                        'description' => $child->description,
                        'office_notes' => $child->office_notes,
                        'duration_hours' => $child->duration_hours,
                        'duration_minutes' => $child->duration_minutes,
                        'featured_image' => $child->featured_image,
                        'gallery_images' => $child->gallery_images,
                        'unit_price' => $child->unit_price,
                        'group_size' => $child->group_size,
                        'tags' => $child->templatePoint && $child->templatePoint->tags ? $child->templatePoint->tags->toArray() : [],
                        'include_in_program' => (bool)$child->include_in_program,
                        'include_in_calculation' => (bool)$child->include_in_calculation,
                        'active' => (bool)$child->active,
                        'show_title_style' => (bool)$child->show_title_style,
                        'show_description' => (bool)$child->show_description,
                    ];
                })->toArray();
                return [
                    'id' => $point->id,
                    'name' => $point->name,
                    'description' => $point->description,
                    'office_notes' => $point->office_notes,
                    'duration_hours' => $point->duration_hours,
                    'duration_minutes' => $point->duration_minutes,
                    'featured_image' => $point->featured_image,
                    'gallery_images' => $point->gallery_images,
                    'unit_price' => $point->unit_price,
                    'group_size' => $point->group_size,
                    'tags' => $point->templatePoint && $point->templatePoint->tags ? $point->templatePoint->tags->toArray() : [],
                    'day' => $point->day,
                    'order' => $point->order,
                    'pivot_notes' => $point->notes,
                    'program_point_id' => $point->event_template_program_point_id,
                    'include_in_program' => (bool)$point->include_in_program,
                    'include_in_calculation' => (bool)$point->include_in_calculation,
                    'active' => (bool)$point->active,
                    'show_title_style' => (bool)$point->show_title_style,
                    'show_description' => (bool)$point->show_description,
                    'children' => $children,
                ];
            })->sortBy('order')->values()->toArray()
        ];
        
        $this->programByDays = $days;
    }

    public function render()
    {
        $availablePoints = EventTemplateProgramPoint::with('tags')
            ->when($this->searchProgramPoint && strlen($this->searchProgramPoint) >= 3, function ($query) {
                $searchTerm = trim($this->searchProgramPoint);

                $fragments = collect(preg_split('/[,\s]+/', $searchTerm))
                    ->map(fn($f) => trim($f))
                    ->filter(fn($f) => strlen($f) >= 2);

                foreach ($fragments as $frag) {
                    $query->where(function ($q) use ($frag) {
                        $q->whereRaw('UPPER(name) LIKE UPPER(?)', ["%$frag%"])
                            ->orWhereRaw('UPPER(description) LIKE UPPER(?)', ["%$frag%"])
                            ->orWhereRaw('UPPER(office_notes) LIKE UPPER(?)', ["%$frag%"])
                            ->orWhereHas('tags', function ($tagQuery) use ($frag) {
                                $tagQuery->whereRaw('UPPER(name) LIKE UPPER(?)', ["%$frag%"]);
                            });
                    });
                }
            })
            ->orderBy('name')
            ->limit(50)
            ->get();

        if (is_null($availablePoints)) {
            $availablePoints = collect();
        }

        return view('livewire.event-instance-program-tree-editor', [
            'programByDays' => $this->programByDays,
            'event' => $this->event,
            'availableProgramPoints' => $availablePoints,
            'duration_days' => $this->event->duration_days + 1,
            'statuses' => TaskStatus::orderBy('order')->get(),
            'users' => User::all(),
        ]);
    }

    public function selectProgramPoint($id)
    {
        $this->modalData['program_point_id'] = $id;
        $this->searchProgramPoint = '';
        $this->searchDropdownOpen = false;
        $this->dispatch('point-selected', ['id' => $id]);
    }

    public function updateSearch($searchTerm)
    {
        $this->searchProgramPoint = $searchTerm;
        $this->searchDropdownOpen = strlen(trim($searchTerm)) >= 3;
    }

    public function showAddModal()
    {
        $this->resetModalData();
        $this->editPoint = null;
        $this->modalData['day'] = 1;
        $this->showModal = true;
    }

    public function showEditModal($pointId)
    {
        $point = EventProgramPoint::find($pointId);
        if ($point) {
            $this->editPoint = $pointId;
            $this->modalData = [
                'id' => $pointId,
                'program_point_id' => $point->event_template_program_point_id,
                'day' => $point->day,
                'notes' => $point->notes,
                'include_in_program' => (bool)$point->include_in_program,
                'include_in_calculation' => (bool)$point->include_in_calculation,
                'active' => (bool)$point->active,
            ];
            $this->showModal = true;
        } else {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Nie znaleziono punktu programu.']);
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetModalData();
    }

    private function resetModalData()
    {
        $this->modalData = [
            'id' => null,
            'program_point_id' => '',
            'day' => 1,
            'notes' => '',
            'include_in_program' => true,
            'include_in_calculation' => true,
            'active' => true,
        ];
        $this->searchDropdownOpen = false;
        $this->resetErrorBag();
    }

    public function savePoint()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            if ($this->editPoint) {
                $point = EventProgramPoint::find($this->editPoint);
                if (!$point) {
                    throw new \Exception('Nie znaleziono punktu programu do edycji.');
                }
                
                // Jeśli zmieniamy szablon punktu, musimy zaktualizować też dane
                if ($point->event_template_program_point_id != $this->modalData['program_point_id']) {
                    $templatePoint = EventTemplateProgramPoint::find($this->modalData['program_point_id']);
                    if ($templatePoint) {
                        $point->name = $templatePoint->name;
                        $point->description = $templatePoint->description;
                        $point->office_notes = $templatePoint->office_notes;
                        $point->pilot_notes = $templatePoint->pilot_notes;
                        $point->duration_hours = $templatePoint->duration_hours;
                        $point->duration_minutes = $templatePoint->duration_minutes;
                        $point->featured_image = $templatePoint->featured_image;
                        $point->gallery_images = $templatePoint->gallery_images;
                        $point->unit_price = $templatePoint->unit_price; // TODO: Konwersja waluty?
                    }
                }

                $point->event_template_program_point_id = $this->modalData['program_point_id'];
                $point->notes = $this->modalData['notes'];
                $point->include_in_program = $this->modalData['include_in_program'];
                $point->include_in_calculation = $this->modalData['include_in_calculation'];
                $point->active = $this->modalData['active'];
                $point->save();

            } else {
                $templatePoint = EventTemplateProgramPoint::with('children')->find($this->modalData['program_point_id']);
                if (!$templatePoint) {
                    throw new \Exception('Nie znaleziono szablonu punktu programu.');
                }

                $maxOrder = EventProgramPoint::where('event_id', $this->event->id)
                    ->where('day', $this->modalData['day'])
                    ->max('order');

                $mainPoint = EventProgramPoint::create([
                    'event_id' => $this->event->id,
                    'event_template_program_point_id' => $templatePoint->id,
                    'name' => $templatePoint->name,
                    'description' => $templatePoint->description,
                    'office_notes' => $templatePoint->office_notes,
                    'pilot_notes' => $templatePoint->pilot_notes,
                    'day' => $this->modalData['day'],
                    'order' => ($maxOrder ?? 0) + 1,
                    'duration_hours' => $templatePoint->duration_hours,
                    'duration_minutes' => $templatePoint->duration_minutes,
                    'featured_image' => $templatePoint->featured_image,
                    'gallery_images' => $templatePoint->gallery_images,
                    'unit_price' => $templatePoint->unit_price, // TODO: Konwersja waluty?
                    'quantity' => 1,
                    'notes' => $this->modalData['notes'],
                    'include_in_program' => $this->modalData['include_in_program'],
                    'include_in_calculation' => $this->modalData['include_in_calculation'],
                    'active' => $this->modalData['active'],
                    'show_title_style' => true,
                    'show_description' => true,
                ]);

                // Kopiuj dzieci
                if ($templatePoint->children && $templatePoint->children->count() > 0) {
                    $childOrder = 1;
                    foreach ($templatePoint->children as $child) {
                        EventProgramPoint::create([
                            'event_id' => $this->event->id,
                            'event_template_program_point_id' => $child->id,
                            'name' => $child->name,
                            'description' => $child->description,
                            'office_notes' => $child->office_notes,
                            'pilot_notes' => $child->pilot_notes,
                            'day' => $this->modalData['day'],
                            'order' => $childOrder,
                            'duration_hours' => $child->duration_hours,
                            'duration_minutes' => $child->duration_minutes,
                            'featured_image' => $child->featured_image,
                            'gallery_images' => $child->gallery_images,
                            'unit_price' => $child->unit_price,
                            'quantity' => 1,
                            'include_in_program' => true,
                            'include_in_calculation' => true,
                            'active' => true,
                            'show_title_style' => true,
                            'show_description' => true,
                            'parent_id' => $mainPoint->id,
                        ]);
                        $childOrder++;
                    }
                }
            }

            DB::commit();
            $this->loadProgramByDays();
            $this->closeModal();
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Punkt programu zapisany pomyślnie!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Błąd zapisu punktu programu: " . $e->getMessage());
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Wystąpił błąd podczas zapisu: ' . $e->getMessage()]);
        }
    }

    public function deletePoint($pointId)
    {
        try {
            $point = EventProgramPoint::find($pointId);
            if (!$point) {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Nie znaleziono punktu programu do usunięcia.']);
                return;
            }

            DB::beginTransaction();

            $dayOfDeletedPoint = $point->day;
            $orderOfDeletedPoint = $point->order;
            $eventId = $point->event_id;

            // Usuń punkt (dzieci usuną się kaskadowo jeśli jest foreign key cascade, ale tutaj robimy soft delete lub manual)
            // Zakładam że EventProgramPoint ma relację children i przy delete() Eloquent może nie usunąć dzieci jeśli nie ma cascade w bazie.
            // Ale w modelu EventProgramPoint nie ma cascade w migracji? Sprawdźmy.
            // Dla pewności usuńmy dzieci ręcznie jeśli to główny punkt
            if (is_null($point->parent_id)) {
                EventProgramPoint::where('parent_id', $point->id)->delete();
            }
            
            $point->delete();

            // Aktualizuj kolejność pozostałych punktów w tym dniu (tylko główne)
            if (is_null($point->parent_id)) {
                EventProgramPoint::where('event_id', $eventId)
                    ->where('day', $dayOfDeletedPoint)
                    ->whereNull('parent_id')
                    ->where('order', '>', $orderOfDeletedPoint)
                    ->decrement('order');
            }

            DB::commit();
            $this->loadProgramByDays();
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Punkt programu usunięty.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Błąd usuwania punktu programu: " . $e->getMessage());
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Wystąpił błąd podczas usuwania punktu programu: ' . $e->getMessage()]);
        }
    }

    public function updateProgramOrder($list)
    {
        try {
            DB::beginTransaction();
            foreach ($list as $dayNumber => $pointIds) {
                foreach ($pointIds as $index => $pointId) {
                    EventProgramPoint::where('id', $pointId)
                        ->update([
                            'order' => $index,
                            'day' => $dayNumber,
                            'updated_at' => now(),
                        ]);
                }
            }
            DB::commit();
            $this->loadProgramByDays();
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Kolejność zaktualizowana.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Błąd aktualizacji kolejności: " . $e->getMessage());
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Błąd aktualizacji kolejności: ' . $e->getMessage()]);
        }
    }

    public function duplicatePoint($pointId)
    {
        try {
            DB::beginTransaction();
            $point = EventProgramPoint::with('children')->find($pointId);
            if ($point) {
                $newPoint = $point->duplicate();
                
                // Duplikuj dzieci
                if ($point->children) {
                    foreach ($point->children as $child) {
                        $newChild = $child->duplicate();
                        $newChild->parent_id = $newPoint->id;
                        $newChild->save();
                    }
                }

                DB::commit();
                $this->loadProgramByDays();
                $this->dispatch('notify', ['type' => 'success', 'message' => 'Punkt programu zduplikowany.']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Błąd duplikowania punktu programu: " . $e->getMessage());
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Wystąpił błąd podczas duplikowania punktu programu.']);
        }
    }

    public function togglePivotProperty($pointId, $property)
    {
        $allowed = ['include_in_program', 'include_in_calculation', 'active', 'show_title_style', 'show_description'];
        if (!in_array($property, $allowed)) return;

        $point = EventProgramPoint::find($pointId);
        if ($point) {
            $point->$property = !$point->$property;
            $point->save();
            $this->loadProgramByDays();
            $this->dispatch('program-updated', "Właściwość została zaktualizowana");
        }
    }

    public function toggleChildPivotProperty($childId, $property)
    {
        // To samo co wyżej, bo w EventProgramPoint dzieci są w tej samej tabeli
        $this->togglePivotProperty($childId, $property);
    }

    // --- Task Management Methods ---

    public function openTaskModal($pointId)
    {
        $this->currentPointIdForTasks = $pointId;
        $this->loadTasksForPoint();
        $this->resetTaskModalData();
        $this->showTaskModal = true;
    }

    public function closeTaskModal()
    {
        $this->showTaskModal = false;
        $this->currentPointIdForTasks = null;
        $this->resetTaskModalData();
    }

    public function loadTasksForPoint()
    {
        if ($this->currentPointIdForTasks) {
            $this->tasksForCurrentPoint = Task::where('taskable_type', EventProgramPoint::class)
                ->where('taskable_id', $this->currentPointIdForTasks)
                ->visibleTo(Auth::id())
                ->with(['status', 'assignee'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $this->tasksForCurrentPoint = [];
        }
    }

    public function resetTaskModalData()
    {
        $this->taskModalData = [
            'title' => '',
            'description' => '',
            'status_id' => TaskStatus::where('is_default', true)->value('id') ?? TaskStatus::first()?->id,
            'priority' => 'medium',
            'assignee_id' => Auth::id(),
            'due_date' => null,
        ];
    }

    public function saveTask()
    {
        $this->validate([
            'taskModalData.title' => 'required|string|max:255',
            'taskModalData.status_id' => 'required|exists:task_statuses,id',
        ]);

        Task::create([
            'title' => $this->taskModalData['title'],
            'description' => $this->taskModalData['description'],
            'status_id' => $this->taskModalData['status_id'],
            'priority' => $this->taskModalData['priority'],
            'assignee_id' => $this->taskModalData['assignee_id'],
            'due_date' => $this->taskModalData['due_date'],
            'author_id' => Auth::id(),
            'taskable_type' => EventProgramPoint::class,
            'taskable_id' => $this->currentPointIdForTasks,
        ]);

        $this->loadTasksForPoint();
        $this->resetTaskModalData();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Zadanie dodane']);
    }

    public function deleteTask($taskId)
    {
        $task = Task::find($taskId);
        if ($task && $task->taskable_type === EventProgramPoint::class && $task->taskable_id == $this->currentPointIdForTasks) {
            $task->delete();
            $this->loadTasksForPoint();
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Zadanie usunięte']);
        }
    }
}
