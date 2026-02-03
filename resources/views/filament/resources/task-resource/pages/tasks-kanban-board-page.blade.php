<x-filament-panels::page class="min-h-screen">
    {{-- Enhanced Kanban Board inspired by filament-kanban --}}

    @if(($this->onlyInstallments ?? false) === true)
        <div class="mb-4 rounded-lg border border-blue-200 dark:border-blue-900 bg-blue-50/60 dark:bg-blue-900/20 p-4 text-sm text-blue-900 dark:text-blue-100">
            <div class="font-semibold">Widok: Zadania (raty)</div>
            <div class="mt-1 text-blue-800/90 dark:text-blue-100/90">
                To są zadania wewnętrzne generowane z rat (dla opiekuna). Przypomnienia do klienta wyślesz z
                <a class="underline font-semibold" href="{{ \App\Filament\Pages\Finance\InstallmentControl::getUrl(['scope' => 'overdue', 'event_code' => $this->eventCode]) }}" target="_blank">Kontroli rat</a>
                (akcja „Przypomnienie” / „Wyślij przypomnienia”).
            </div>
        </div>
    @endif
    
    {{-- Advanced Filters & Controls --}}
    <div class="mb-6 flex flex-col sm:flex-row justify-between gap-4 p-4 rounded-lg f-kanban-controls">
        <div class="flex flex-wrap gap-2">
            <input 
                wire:model.live="searchTerm" 
                type="text" 
                placeholder="🔍 Szukaj zadań..."
                class="fi-input block w-full border py-2 px-3 text-base outline-none transition duration-75 placeholder:text-gray-400 sm:text-sm sm:leading-6 rounded-lg shadow-sm"
            />
            
            <select wire:model.live="filterBy" class="fi-select-input block w-full border py-2 pe-8 ps-3 text-base outline-none transition duration-75 sm:text-sm sm:leading-6 rounded-lg shadow-sm">
                <option value="">🎯 Wszystkie zadania</option>
                <option value="author">📝 Moje zadania</option>
                <option value="assignee">👤 Przypisane do mnie</option>
            </select>
            
            <select wire:model.live="priorityFilter" class="fi-select-input block w-full border py-2 pe-8 ps-3 text-base outline-none transition duration-75 sm:text-sm sm:leading-6 rounded-lg shadow-sm">
                <option value="">🔥 Wszystkie priorytety</option>
                <option value="high">🔴 Wysoki</option>
                <option value="medium">🟡 Średni</option>
                <option value="low">🟢 Niski</option>
            </select>
        </div>
        
        <div class="flex gap-2 items-center">
            <button wire:click="refreshBoard" 
                class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm">
                <x-heroicon-m-arrow-path class="w-4 h-4" />
                Odśwież
            </button>
            <button wire:click="openQuickAddModal(null)"
                class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm">
                <x-heroicon-m-plus class="w-4 h-4" />
                Dodaj zadanie
            </button>
        </div>
    </div>

    {{-- Enhanced Kanban Board using filament-kanban style --}}
    <div 
        x-data="kanbanBoard()" 
        x-init="init()" 
        class="md:flex overflow-x-auto overflow-y-hidden gap-4 pb-4 p-4 rounded-xl f-kanban-root"
        style="min-height: 70vh;"
        wire:ignore
    >
        @foreach ($statuses as $status)
            <div class="kanban-column md:w-[24rem] flex-shrink-0 mb-5 md:min-h-full flex flex-col f-kanban-column"> 
                
                {{-- Enhanced Column Header inspired by filament-kanban --}}
                <h3 class="kanban-column-header mb-3 px-3 py-2 font-bold text-base flex items-center justify-between rounded-lg f-kanban-column-header">
                    <div class="flex items-center gap-2">
                        <span class="text-blue-600 dark:text-blue-400">●</span>
                        <span class="dark:text-gray-100">{{ $status->name }}</span>
                        <span class="text-xs font-black px-2 py-1 rounded-full f-kanban-count">
                            {{ $tasks->where('status_id', $status->id)->count() }}
                        </span>
                    </div>
                    
                    <div class="flex items-center gap-1">
                        {{-- Sort dropdown --}}
                        <div class="relative" x-data="{ open: false }">
                            <button 
                                @click="open = !open"
                                class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-700"
                                title="Sortuj zadania">
                                <x-heroicon-m-bars-3-bottom-left class="w-4 h-4" />
                            </button>
                            
                            <div x-show="open" @click.away="open = false" class="absolute right-0 top-8 z-50 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg min-w-48">
                                <div class="py-1">
                                    <button wire:click="sortColumn({{ $status->id }}, 'priority_desc')" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">📈 Priorytet (wys-nis)</button>
                                    <button wire:click="sortColumn({{ $status->id }}, 'priority_asc')" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">📉 Priorytet (nis-wys)</button>
                                    <button wire:click="sortColumn({{ $status->id }}, 'due_date_asc')" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">📅 Data (najwcześniej)</button>
                                    <button wire:click="sortColumn({{ $status->id }}, 'due_date_desc')" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">📅 Data (najpóźniej)</button>
                                    <button wire:click="sortColumn({{ $status->id }}, 'title_asc')" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">🔤 Tytuł (A-Z)</button>
                                    <button wire:click="sortColumn({{ $status->id }}, 'title_desc')" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">🔤 Tytuł (Z-A)</button>
                                    <button wire:click="sortColumn({{ $status->id }}, 'created_desc')" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">🕒 Najnowsze</button>
                                    <button wire:click="sortColumn({{ $status->id }}, 'created_asc')" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">🕒 Najstarsze</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </h3>

                {{-- Tasks Container using filament-kanban styling --}}
                <div 
                    class="tasks-container flex flex-col flex-1 gap-3 p-0"
                    data-status-id="{{ $status->id }}"
                >
                    @foreach ($tasks->where('status_id', $status->id)->sortByDesc('last_activity_at') as $task)
                        @php
                            $editUrl = \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $task]);
                        @endphp
                        <div 
                            id="{{ $task->id }}" 
                            class="task-card record group f-kanban-task px-4 py-4 cursor-grab transition hover:shadow-xl transform hover:-translate-y-1 relative" 
                            onclick="if(document.body.classList.contains('grabbing')) return; window.location.href='{{ $editUrl }}';"
                            @if($task->updated_at && now()->diffInSeconds($task->updated_at, true) < 3)
                                x-data
                                x-init="
                                    $el.classList.add('animate-pulse-twice', 'bg-blue-600', 'border-blue-400', 'text-white')
                                    $el.classList.remove('bg-white', 'dark:bg-gray-800', 'border-gray-200', 'dark:border-gray-700')
                                    setTimeout(() => {
                                        $el.classList.remove('bg-blue-600', 'border-blue-400', 'text-white')
                                        $el.classList.add('bg-white', 'dark:bg-gray-800', 'border-gray-200', 'dark:border-gray-700')
                                    }, 3000)
                                "
                            @endif
                        >
                            {{-- Task Header --}}
                            <div class="flex items-start justify-between mb-3">
                                <h4 class="font-bold text-base leading-5 flex-1 pr-2">
                                    {{ $task->title }}
                                </h4>
                                
                                {{-- Priority Badge --}}
                                <span class="priority-badge flex-shrink-0 text-xs px-2 py-1 rounded-full font-bold border f-kanban-priority" 
                                    style="background-color: {{ match ($task->priority) {
                                        'low' => '#059669',
                                        'medium' => '#D97706', 
                                        'high' => '#DC2626',
                                        default => '#6B7280'
                                    } }}; color: white; border-color: {{ match ($task->priority) {
                                        'low' => '#047857',
                                        'medium' => '#B45309',
                                        'high' => '#B91C1C', 
                                        default => '#4B5563'
                                    } }};">
                                    {{ match ($task->priority) {
                                        'low' => 'LOW',
                                        'medium' => 'MED',
                                        'high' => 'HIGH',
                                        default => 'NONE'
                                    } }}
                                </span>
                            </div>

                            {{-- Task Description --}}
                            @if($task->description)
                                <p class="text-sm text-gray-300 dark:text-gray-400 mb-3 line-clamp-2 font-normal">
                                    {{ Str::limit(strip_tags($task->description), 100) }}
                                </p>
                            @endif

                            @if(!empty($task->last_activity_label))
                                <div class="mt-2 text-[11px] text-gray-400">
                                    {{ $task->last_activity_label }}
                                    @if($task->last_activity_at)
                                        • {{ $task->last_activity_at->format('H:i d.m') }}
                                    @endif
                                </div>
                            @endif

                            {{-- Task Meta Info --}}
                            <div class="space-y-2 mb-3">
                                @if($task->assignee)
                                    <div class="flex items-center text-sm px-3 py-2 rounded-lg f-kanban-meta">
                                        <x-heroicon-m-user class="w-4 h-4 mr-2 text-gray-400 dark:text-gray-500" />
                                        <span class="font-medium text-white dark:text-gray-200">{{ $task->assignee->name }}</span>
                                    </div>
                                @endif

                                @if($task->due_date)
                                    <div class="flex items-center text-sm px-3 py-2 rounded-lg {{ $task->due_date->isPast() ? 'f-kanban-meta-danger' : 'f-kanban-meta-default' }}">
                                        <x-heroicon-m-clock class="w-4 h-4 mr-2" />
                                        <span class="font-medium">{{ $task->due_date->format('d.m.Y H:i') }}</span>
                                        @if($task->due_date->isPast())
                                            <span class="ml-2 text-red-100 dark:text-red-200 font-bold">PRZETERMINOWANE ⚠️</span>
                                        @elseif($task->due_date->diffInDays() <= 1)
                                            <span class="ml-2 text-orange-200 dark:text-orange-300 font-bold">PILNE 🔥</span>
                                        @endif
                                    </div>
                                @endif

                                {{-- Subtasks Progress --}}
                                @if($task->subtasks && $task->subtasks->count() > 0)
                                    @php
                                        $completedSubtasks = $task->subtasks->where('status.name', 'Zakończone')->count();
                                        $totalSubtasks = $task->subtasks->count();
                                        $progressPercent = $totalSubtasks > 0 ? round(($completedSubtasks / $totalSubtasks) * 100) : 0;
                                    @endphp
                                    <div class="bg-gray-600 dark:bg-gray-700 px-3 py-2 rounded-lg border border-gray-500 dark:border-gray-600">
                                        <div class="flex items-center justify-between text-sm text-gray-200 dark:text-gray-300 mb-2">
                                            <div class="flex items-center">
                                                <x-heroicon-m-list-bullet class="w-4 h-4 mr-2 text-gray-400" />
                                                <span class="font-medium">Podzadania: {{ $completedSubtasks }}/{{ $totalSubtasks }}</span>
                                            </div>
                                            <span class="text-xs font-bold text-green-300 dark:text-green-400">{{ $progressPercent }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-500 dark:bg-gray-600 rounded-full h-2 border border-gray-400 dark:border-gray-500">
                                            <div class="bg-green-500 dark:bg-green-400 h-2 rounded-full transition-all duration-300" style="width: {{ $progressPercent }}%"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Task Footer --}}
                            <div class="flex items-center justify-between pt-3 border-t border-gray-600 dark:border-gray-700">
                                <div class="flex items-center gap-2">
                                    @if($task->attachments && $task->attachments->count() > 0)
                                        <span class="text-xs text-gray-300 dark:text-gray-400 flex items-center bg-gray-600 dark:bg-gray-700 px-2 py-1 rounded-md border border-gray-500 dark:border-gray-600">
                                            <x-heroicon-m-paper-clip class="w-3 h-3 mr-1" />
                                            <span class="font-medium">{{ $task->attachments->count() }}</span>
                                        </span>
                                    @endif
                                    
                                    @if($task->comments && $task->comments->count() > 0)
                                        <span class="text-xs text-gray-300 dark:text-gray-400 flex items-center bg-gray-600 dark:bg-gray-700 px-2 py-1 rounded-md border border-gray-500 dark:border-gray-600">
                                            <x-heroicon-m-chat-bubble-left class="w-3 h-3 mr-1" />
                                            <span class="font-medium">{{ $task->comments->count() }}</span>
                                        </span>
                                    @endif

                                    {{-- Task Actions Quick Access --}}
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button 
                                            wire:click.stop="showSubtasks({{ $task->id }})"
                                            class="text-gray-400 dark:text-gray-500 hover:text-blue-400 dark:hover:text-blue-300 transition-colors p-1 rounded hover:bg-gray-600 dark:hover:bg-gray-700"
                                            title="Pokaż podzadania">
                                            <x-heroicon-m-squares-plus class="w-4 h-4" />
                                        </button>
                                        
                                        <button 
                                            wire:click.stop="showComments({{ $task->id }})"
                                            class="text-gray-400 dark:text-gray-500 hover:text-green-400 dark:hover:text-green-300 transition-colors p-1 rounded hover:bg-gray-600 dark:hover:bg-gray-700"
                                            title="Komentarze">
                                            <x-heroicon-m-chat-bubble-left-ellipsis class="w-4 h-4" />
                                        </button>
                                        
                                        <button 
                                            wire:click.stop="showAttachments({{ $task->id }})"
                                            class="text-gray-400 dark:text-gray-500 hover:text-purple-400 dark:hover:text-purple-300 transition-colors p-1 rounded hover:bg-gray-600 dark:hover:bg-gray-700"
                                            title="Załączniki">
                                            <x-heroicon-m-paper-clip class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                                
                                {{-- Delete Button --}}
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button 
                                        wire:click.stop="deleteTask({{ $task->id }})"
                                        onclick="return confirm('Czy na pewno chcesz usunąć to zadanie?')"
                                        class="text-gray-400 dark:text-gray-500 hover:text-red-400 dark:hover:text-red-300 transition-colors p-1 rounded hover:bg-gray-600 dark:hover:bg-gray-700"
                                        title="Usuń zadanie">
                                        <x-heroicon-m-trash class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>

                            {{-- Rozwijana lista konwersacji po najechaniu --}}
                            <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" class="absolute right-2 top-2 z-40">
                                <button type="button" class="text-xs text-blue-400 hover:text-blue-200 focus:outline-none" @mouseenter="open = true">
                                    💬
                                </button>
                                <div x-show="open" x-transition class="mt-2 w-72 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg shadow-lg p-3 text-sm text-gray-800 dark:text-gray-100" style="display: none;">
                                    <div class="font-semibold mb-2 text-blue-700 dark:text-blue-300">Wiadomości / Komentarze</div>
                                    @if($task->comments && $task->comments->count() > 0)
                                        <ul class="divide-y divide-gray-200 dark:divide-gray-700 max-h-48 overflow-y-auto">
                                            @foreach($task->comments as $comment)
                                                <li class="py-2">
                                                    @php
                                                        $recipientName = $comment->recipient?->name;
                                                        if (! $recipientName) {
                                                            if ($task->assignee_id && (int) $comment->author_id !== (int) $task->assignee_id) {
                                                                $recipientName = $task->assignee?->name;
                                                            }
                                                            if (! $recipientName && $task->author_id && (int) $comment->author_id !== (int) $task->author_id) {
                                                                $recipientName = $task->author?->name;
                                                            }
                                                            $recipientName = $recipientName ?: 'Zespół';
                                                        }
                                                    @endphp
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-bold">{{ $comment->author->name ?? 'Anonim' }}</span>
                                                        <span class="text-xs text-gray-400">→ {{ $recipientName }}</span>
                                                        <span class="text-xs text-gray-500">{{ $comment->created_at->format('d.m.Y H:i') }}</span>
                                                    </div>
                                                    @if($comment->recipients && $comment->recipients->count() > 1)
                                                        <div class="text-[11px] text-gray-400">Adresaci: {{ $comment->recipients->pluck('name')->join(', ') }}</div>
                                                    @endif
                                                    <div class="text-xs text-gray-700 dark:text-gray-300 mt-1">
                                                        {{ $comment->content }}
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <div class="text-gray-400 text-xs">Brak wiadomości</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                    
                    {{-- Empty State --}}
                    @if($tasks->where('status_id', $status->id)->count() === 0)
                        <div class="text-center py-8 text-gray-400 dark:text-gray-500">
                            <x-heroicon-o-inbox class="w-8 h-8 mx-auto mb-2 text-gray-500 dark:text-gray-600" />
                            <p class="text-sm font-medium">Brak zadań</p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Quick Add Task Modal --}}
    <x-filament::modal id="quick-add-modal" width="2xl">
        <x-slot name="header">
            <x-filament::modal.heading>
                Szybko dodaj zadanie
            </x-filament::modal.heading>
        </x-slot>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tytuł zadania *</label>
                <input 
                    wire:model="quickTaskTitle"
                    type="text" 
                    class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                    placeholder="Wprowadź tytuł zadania"
                    required
                />
                @error('quickTaskTitle') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Opis</label>
                <textarea 
                    wire:model="quickTaskDescription"
                    rows="3" 
                    class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                    placeholder="Wprowadź opis zadania (opcjonalnie)"
                ></textarea>
                @error('quickTaskDescription') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priorytet</label>
                    <select 
                        wire:model="quickTaskPriority"
                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                    >
                        <option value="low">🟢 Niski</option>
                        <option value="medium" selected>🟡 Średni</option>
                        <option value="high">🔴 Wysoki</option>
                    </select>
                    @error('quickTaskPriority') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Przypisz do</label>
                    <select 
                        wire:model="quickTaskAssigneeId"
                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                    >
                        <option value="">Nie przypisano</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('quickTaskAssigneeId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex justify-end gap-2">
                <x-filament::button color="gray" wire:click="cancelQuickAdd">
                    Anuluj
                </x-filament::button>
                <x-filament::button color="primary" wire:click="createQuickTask">
                    Utwórz zadanie
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>

    {{-- Edit Task Modal --}}
    <x-filament::modal id="edit-task-modal" width="4xl">
        <x-slot name="header">
            <x-filament::modal.heading>
                Edytuj zadanie
            </x-filament::modal.heading>
        </x-slot>

        @if($editingTask)
        <div class="space-y-6">
            {{-- Basic Task Info --}}
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tytuł zadania</label>
                    <input 
                        wire:model="editModalData.title"
                        type="text" 
                        class="block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:text-gray-300"
                        placeholder="Wprowadź tytuł zadania"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Opis</label>
                    <textarea 
                        wire:model="editModalData.description"
                        rows="4" 
                        class="block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:text-gray-300"
                        placeholder="Wprowadź opis zadania"
                    ></textarea>
                </div>
            </div>

            {{-- Task Properties --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priorytet</label>
                    <select 
                        wire:model="editModalData.priority"
                        class="block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:text-gray-300"
                    >
                        <option value="">Wybierz priorytet</option>
                        <option value="low">🟢 Niski</option>
                        <option value="medium">🟡 Średni</option>
                        <option value="high">🔴 Wysoki</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select 
                        wire:model="editModalData.status_id"
                        class="block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:text-gray-300"
                    >
                        <option value="">Wybierz status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Termin wykonania</label>
                    <input 
                        wire:model="editModalData.due_date"
                        type="datetime-local" 
                        class="block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:text-gray-300"
                    />
                </div>
            </div>

            {{-- Task Assignment --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Autor</label>
                    <div class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 sm:text-sm">
                        {{ $editingTask->author->name ?? 'Nieznany' }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Przypisany do</label>
                    <select 
                        wire:model="editModalData.assignee_id"
                        class="block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:text-gray-300"
                    >
                        <option value="">Nie przypisane</option>
                        @foreach(\App\Models\User::all() as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Szybkie akcje</h4>
                <div class="flex flex-wrap gap-2">
                    <button 
                        wire:click.stop="showSubtasks({{ $editingTask->id ?? 0 }})"
                        class="px-3 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded text-sm hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors">
                        🔧 Podzadania ({{ $editingTask->subtasks->count() ?? 0 }})
                    </button>
                    <button 
                        wire:click.stop="showComments({{ $editingTask->id ?? 0 }})"
                        class="px-3 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded text-sm hover:bg-green-200 dark:hover:bg-green-800 transition-colors">
                        💬 Komentarze ({{ $editingTask->comments->count() ?? 0 }})
                    </button>
                    <button 
                        wire:click.stop="showAttachments({{ $editingTask->id ?? 0 }})"
                        class="px-3 py-1 bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 rounded text-sm hover:bg-purple-200 dark:hover:bg-purple-800 transition-colors">
                        📎 Załączniki ({{ $editingTask->attachments->count() ?? 0 }})
                    </button>
                </div>
            </div>
        </div>
        @endif

        <x-slot name="footer">
            <x-filament::button wire:click="saveTask">
                Zapisz zmiany
            </x-filament::button>

            <x-filament::button color="gray" x-on:click="isOpen = false">
                Anuluj
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    {{-- Subtasks Modal --}}
    <x-filament::modal id="subtasks-modal" width="5xl">
        <x-slot name="header">
            <x-filament::modal.heading>
                Podzadania: {{ $currentTaskForDetails?->title ?? '' }}
            </x-filament::modal.heading>
        </x-slot>

        @if($currentTaskForDetails)
        <div class="space-y-4">
            {{-- Add/Edit Subtask --}}
            <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                @if($editingSubtask)
                    {{-- Edycja podzadania --}}
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tytuł podzadania *</label>
                            <input 
                                wire:model="editSubtaskData.title"
                                type="text" 
                                class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                placeholder="Wprowadź tytuł podzadania"
                                required
                            />
                            @error('editSubtaskData.title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Opis</label>
                            <textarea 
                                wire:model="editSubtaskData.description"
                                rows="3" 
                                class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                placeholder="Wprowadź opis podzadania (opcjonalnie)"
                            ></textarea>
                            @error('editSubtaskData.description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priorytet</label>
                                <select 
                                    wire:model="editSubtaskData.priority"
                                    class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                >
                                    <option value="low">🟢 Niski</option>
                                    <option value="medium">🟡 Średni</option>
                                    <option value="high">🔴 Wysoki</option>
                                </select>
                                @error('editSubtaskData.priority') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Przypisz do</label>
                                <select 
                                    wire:model="editSubtaskData.assignee_id"
                                    class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                >
                                    <option value="">Nie przypisano</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @error('editSubtaskData.assignee_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                                <select 
                                    wire:model="editSubtaskData.status_id"
                                    class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                >
                                    <option value="">Wybierz status</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                                    @endforeach
                                </select>
                                @error('editSubtaskData.status_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Termin wykonania</label>
                                <input 
                                    wire:model="editSubtaskData.due_date"
                                    type="datetime-local" 
                                    class="block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                />
                                @error('editSubtaskData.due_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="flex justify-end gap-2">
                            <x-filament::button color="gray" wire:click="cancelEditSubtask">Wróć do listy</x-filament::button>
                            <x-filament::button color="primary" wire:click="saveSubtask">Zapisz podzadanie</x-filament::button>
                        </div>
                    </div>
                @elseif($showAdvancedSubtaskForm)
                    {{-- Zaawansowane dodawanie podzadania --}}
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tytuł podzadania *</label>
                            <input 
                                wire:model="editSubtaskData.title"
                                type="text" 
                                class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                placeholder="Wprowadź tytuł podzadania"
                                required
                            />
                            @error('editSubtaskData.title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Opis</label>
                            <textarea 
                                wire:model="editSubtaskData.description"
                                rows="3" 
                                class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                placeholder="Wprowadź opis podzadania (opcjonalnie)"
                            ></textarea>
                            @error('editSubtaskData.description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priorytet</label>
                                <select 
                                    wire:model="editSubtaskData.priority"
                                    class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                >
                                    <option value="low">🟢 Niski</option>
                                    <option value="medium" selected>🟡 Średni</option>
                                    <option value="high">🔴 Wysoki</option>
                                </select>
                                @error('editSubtaskData.priority') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Przypisz do</label>
                                <select 
                                    wire:model="editSubtaskData.assignee_id"
                                    class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                >
                                    <option value="">Nie przypisano</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @error('editSubtaskData.assignee_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                                <select 
                                    wire:model="editSubtaskData.status_id"
                                    class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                >
                                    <option value="">Wybierz status</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                                    @endforeach
                                </select>
                                @error('editSubtaskData.status_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Termin wykonania</label>
                                <input 
                                    wire:model="editSubtaskData.due_date"
                                    type="datetime-local" 
                                    class="block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                />
                                @error('editSubtaskData.due_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="flex justify-end gap-2">
                            <x-filament::button color="gray" wire:click="cancelAdvancedSubtaskForm">Wróć do listy</x-filament::button>
                            <x-filament::button color="primary" wire:click="addAdvancedSubtask">Dodaj podzadanie</x-filament::button>
                        </div>
                    </div>
                @else
                    <div class="flex gap-2 items-center">
                        <input 
                            wire:model="newSubtaskTitle"
                            wire:keydown.enter="addSubtask"
                            type="text" 
                            placeholder="Dodaj nowe podzadanie..."
                            class="flex-1 block border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                        />
                        <button 
                            wire:click="addSubtask"
                            class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition-colors">
                            Dodaj
                        </button>
                        <button 
                            wire:click="showAdvancedSubtaskForm"
                            class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition-colors">
                            Zaawansowane dodawanie
                        </button>
                    </div>
                @endif

                {{-- Lista istniejących podzadań (widoczna zawsze pod formularzem) --}}
                <div class="mt-6">
                    <h4 class="font-semibold text-gray-700 dark:text-gray-200 mb-2">Lista podzadań</h4>
                    @if($currentTaskForDetails && $currentTaskForDetails->subtasks && $currentTaskForDetails->subtasks->count() > 0)
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($currentTaskForDetails->subtasks as $subtask)
                                <li class="py-2 flex items-center justify-between">
                                    <div>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $subtask->title }}</span>
                                        <span class="text-xs text-gray-500 ml-2">{{ $subtask->status->name ?? '' }}</span>
                                        @if($subtask->assignee)
                                            <span class="text-xs text-blue-600 dark:text-blue-300 ml-2">👤 {{ $subtask->assignee->name }}</span>
                                        @endif
                                        @if($subtask->due_date)
                                            <span class="text-xs text-purple-600 dark:text-purple-300 ml-2">📅 {{ $subtask->due_date->format('d.m.Y H:i') }}</span>
                                        @endif
                                    </div>
                                    <div class="flex gap-2">
                                        <button wire:click="editSubtask({{ $subtask->id }})" class="text-blue-500 hover:underline text-xs">Edytuj</button>
                                        <button wire:click="deleteSubtask({{ $subtask->id }})" class="text-red-500 hover:underline text-xs">Usuń</button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-gray-400 text-sm">Brak podzadań</div>
                    @endif
                </div>
            </div>
        @endif

        <x-slot name="footer">
            <x-filament::button color="gray" x-on:click="isOpen = false">
                Zamknij
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    {{-- Comments Modal --}}
    <x-filament::modal id="comments-modal" width="4xl">
        <x-slot name="header">
            <x-filament::modal.heading>
                Komentarze: {{ $currentTaskForDetails?->title ?? '' }}
            </x-filament::modal.heading>
        </x-slot>

        @if($currentTaskForDetails)
        <div class="space-y-4">
            {{-- Add New Comment --}}
            <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                <div class="space-y-2">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="text-xs text-gray-600 dark:text-gray-300">
                            {{ Auth::user()?->name ?? 'Ty' }}
                            <span class="mx-1">→</span>
                            <span>Adresaci wybrani poniżej</span>
                        </div>
                        <div class="sm:w-72">
                            <select wire:model="newCommentRecipientIds" multiple
                                    class="w-full block border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm text-black dark:text-gray-200 bg-white dark:bg-gray-700">
                                @if($currentTaskForDetails?->assignee)
                                    <option value="{{ $currentTaskForDetails->assignee->id }}">{{ $currentTaskForDetails->assignee->name }}</option>
                                @endif
                                @if($currentTaskForDetails?->author)
                                    <option value="{{ $currentTaskForDetails->author->id }}">{{ $currentTaskForDetails->author->name }}</option>
                                @endif
                            </select>
                        </div>
                    </div>
                    <textarea 
                        wire:model="newComment"
                        placeholder="Dodaj komentarz..."
                        rows="3"
                        class="w-full block border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm text-black dark:text-gray-200 bg-white dark:bg-gray-700"
                    ></textarea>
                    <div class="flex justify-end">
                        <button 
                            wire:click="addComment"
                            class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition-colors">
                            Dodaj komentarz
                        </button>
                    </div>
                </div>
            </div>

            {{-- Comments List (threaded chat) --}}
            <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                <div class="space-y-4 max-h-96 overflow-y-auto">
                    @forelse($currentTaskForDetails->comments ?? [] as $comment)
                        @php
                            $recipientsLabel = $comment->recipients?->pluck('name')->join(', ');
                        @endphp
                        <div class="rounded-lg border border-gray-200 dark:border-gray-600 p-3">
                            <div class="text-xs text-gray-500 mb-1">
                                Autor zadania: {{ $currentTaskForDetails?->author?->name ?? '—' }} · Zleceniobiorca: {{ $currentTaskForDetails?->assignee?->name ?? '—' }}
                            </div>
                            @if($currentTaskForDetails?->taskable instanceof \App\Models\Event)
                                <div class="text-xs text-gray-500 mb-1">
                                    Impreza: {{ $currentTaskForDetails->taskable?->name }}@if($currentTaskForDetails->taskable?->public_code) ({{ $currentTaskForDetails->taskable?->public_code }})@endif
                                </div>
                            @endif
                            <div class="flex items-center gap-2 text-sm font-semibold">
                                <span>{{ $comment->author?->name ?? 'Nieznany autor' }}</span>
                                <span class="text-xs text-gray-400">→ {{ $recipientsLabel ?: 'Zespół' }}</span>
                                <span class="text-xs text-gray-400">· {{ $comment->created_at?->diffForHumans() }}</span>
                            </div>
                            <div class="mt-1 text-sm text-gray-600 dark:text-gray-200 whitespace-pre-wrap">
                                {{ $comment->content }}
                            </div>

                            <details class="mt-2 text-xs">
                                <summary class="cursor-pointer text-primary-600">Odpowiedzi ({{ $comment->replies?->count() ?? 0 }})</summary>
                                <div class="mt-2 space-y-2">
                                    @foreach($comment->replies ?? [] as $reply)
                                        <div class="rounded-md border border-gray-200 dark:border-gray-600 p-2">
                                            <div class="flex items-center gap-2 text-xs font-semibold">
                                                <span>{{ $reply->author?->name ?? 'Nieznany autor' }}</span>
                                                <span class="text-xs text-gray-400">→ {{ $reply->recipients?->pluck('name')->join(', ') ?: 'Zespół' }}</span>
                                                <span class="text-xs text-gray-400">· {{ $reply->created_at?->diffForHumans() }}</span>
                                            </div>
                                            <div class="mt-1 text-xs text-gray-600 dark:text-gray-200 whitespace-pre-wrap">
                                                {{ $reply->content }}
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="mt-2">
                                        <select wire:model="replyRecipientIds.{{ $comment->id }}" multiple
                                                class="w-full block border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm text-black dark:text-gray-200 bg-white dark:bg-gray-700">
                                            @if($currentTaskForDetails?->assignee)
                                                <option value="{{ $currentTaskForDetails->assignee->id }}">{{ $currentTaskForDetails->assignee->name }}</option>
                                            @endif
                                            @if($currentTaskForDetails?->author)
                                                <option value="{{ $currentTaskForDetails->author->id }}">{{ $currentTaskForDetails->author->name }}</option>
                                            @endif
                                        </select>
                                        <textarea wire:model="replyContents.{{ $comment->id }}" rows="2"
                                                  class="mt-2 w-full block border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm text-black dark:text-gray-200 bg-white dark:bg-gray-700"
                                                  placeholder="Napisz odpowiedź..."></textarea>
                                        <div class="mt-2 text-right">
                                            <button wire:click="addReply({{ $comment->id }})" class="px-3 py-1.5 text-xs bg-primary-600 text-white rounded-md hover:bg-primary-700 transition-colors">
                                                Wyślij odpowiedź
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </details>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <x-heroicon-o-chat-bubble-left-ellipsis class="w-12 h-12 mx-auto mb-2 text-gray-300" />
                            <p>Brak komentarzy</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        @endif

        <x-slot name="footer">
            <x-filament::button color="gray" x-on:click="isOpen = false">
                Zamknij
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    {{-- Attachments Modal --}}
    <x-filament::modal id="attachments-modal" width="4xl">
        <x-slot name="header">
            <x-filament::modal.heading>
                Załączniki: {{ $currentTaskForDetails?->title ?? '' }}
            </x-filament::modal.heading>
        </x-slot>

        @if($currentTaskForDetails)
        <div class="space-y-4">
            {{-- Attachments List --}}
            <div class="space-y-3 max-h-96 overflow-y-auto">
                @forelse($currentTaskForDetails->attachments ?? [] as $attachment)
                    <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <x-heroicon-m-document class="w-8 h-8 text-gray-400" />                    
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $attachment->filename }}</h4>
                                    <p class="text-sm text-gray-500">{{ $attachment->created_at->format('d.m.Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($attachment->file_path)                        
                                <a 
                                    href="{{ asset('storage/' . $attachment->file_path) }}" 
                                    target="_blank"
                                    class="px-3 py-1 bg-blue-100 text-blue-800 rounded text-sm hover:bg-blue-200 transition-colors">
                                    Pobierz
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        <x-heroicon-o-paper-clip class="w-12 h-12 mx-auto mb-2 text-gray-300" />
                        <p>Brak załączników</p>
                    </div>
                @endforelse
            </div>
        </div>
        @endif

        <x-slot name="footer">
            <x-filament::button color="gray" x-on:click="isOpen = false">
                Zamknij
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    {{-- Enhanced JavaScript with filament-kanban integration --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded');
            console.log('SortableJS loaded:', typeof Sortable !== 'undefined');
            console.log('Livewire loaded:', typeof window.Livewire !== 'undefined');
        });

        function kanbanBoard() {
            return {
                init() {
                    if (window.Livewire && @this) {
                        console.log('Kanban board initializing...');
                        // Small delay to ensure DOM is fully rendered
                        setTimeout(() => {
                            this.initDragAndDrop();
                            this.initKeyboardShortcuts();
                        }, 100);
                    }
                },
                initDragAndDrop() {
                    console.log('Initializing drag and drop...');
                    
                    // Initialize Sortable for each tasks container
                    const containers = document.querySelectorAll('.tasks-container[data-status-id]');
                    console.log('Found containers:', containers.length);
                    
                    containers.forEach((container, index) => {
                        console.log(`Initializing container ${index + 1}:`, container);
                        const sortable = new Sortable(container, {
                            group: 'kanban-tasks',
                            animation: 200,
                            ghostClass: 'sortable-ghost',
                            dragClass: 'sortable-drag',
                            chosenClass: 'sortable-chosen',
                            handle: '.record',
                            forceFallback: false,
                            fallbackTolerance: 0,
                            
                            onStart: (evt) => {
                                console.log('Drag started:', evt.item.id);
                                document.body.classList.add("grabbing");
                                evt.item.classList.add('shadow-2xl', 'z-50');
                            },
                            
                            onEnd: (evt) => {
                                console.log('Drag ended:', {
                                    taskId: evt.item.id,
                                    fromStatus: evt.from.dataset.statusId,
                                    toStatus: evt.to.dataset.statusId,
                                    newIndex: evt.newIndex,
                                    oldIndex: evt.oldIndex
                                });
                                
                                document.body.classList.remove("grabbing");
                                evt.item.classList.remove('shadow-2xl', 'z-50');
                                
                                const taskId = evt.item.id;
                                const newStatusId = evt.to.dataset.statusId;
                                const newOrder = evt.newIndex;
                                
                                // Visual feedback
                                evt.item.classList.add('animate-pulse');
                                setTimeout(() => {
                                    evt.item.classList.remove('animate-pulse');
                                }, 1000);
                                
                                // Call Livewire method
                                if (window.Livewire && @this) {
                                    console.log('Calling updateTaskStatus...');
                                    @this.call('updateTaskStatus', taskId, newStatusId, newOrder)
                                        .then((result) => {
                                            console.log('Task status updated successfully:', result);
                                        })
                                        .catch((error) => {
                                            console.error('Error updating task status:', error);
                                            // Show user-friendly error
                                            alert('Wystąpił błąd podczas przenoszenia zadania. Strona zostanie odświeżona.');
                                            window.location.reload();
                                        });
                                } else {
                                    console.error('Livewire not available');
                                }
                            },
                            
                            onMove: (evt) => {
                                console.log('Moving item');
                                return true;
                            },
                            
                            onClone: (evt) => {
                                console.log('Cloning item');
                            }
                        });
                        
                        console.log(`Sortable initialized for container ${index + 1}:`, sortable);
                    });
                    
                    console.log('Drag and drop initialization completed');
                },
                initKeyboardShortcuts() {
                    document.addEventListener('keydown', (e) => {
                        // Ctrl+N = New task
                        if (e.ctrlKey && e.key === 'n') {
                            e.preventDefault();
                            window.location.href = '{{ \App\Filament\Resources\TaskResource::getUrl("create") }}';
                        } 
                        // R = Refresh
                        else if (e.key === 'r' && !e.ctrlKey && !e.altKey) {
                            e.preventDefault();
                            @this.refreshBoard();
                        }
                    });
                }
            }
        }
    </script>

    {{-- Custom Styles inspired by filament-kanban --}}
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .grabbing {
            cursor: grabbing !important;
        }
        
        /* Sortable.js specific styles */
        .sortable-ghost {
            opacity: 0.4 !important;
            background: rgba(59, 130, 246, 0.1) !important;
            border: 2px dashed #3b82f6 !important;
            border-radius: 8px;
        }
        
        .sortable-drag {
            opacity: 0.9 !important;
            background: #ffffff !important;
            border: 2px solid #3b82f6 !important;
            transform: rotate(3deg) scale(1.05) !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2) !important;
            z-index: 9999 !important;
        }
        
        .dark .sortable-drag {
            background: #374151 !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5) !important;
        }
        
        .sortable-chosen {
            transform: scale(1.02) !important;
        }
        
        .sortable-fallback {
            opacity: 0.8 !important;
            background: #ffffff !important;
            border: 2px dashed #3b82f6 !important;
        }
        
        .dark .sortable-fallback {
            background: #374151 !important;
        }
        
        /* Tasks container should accept drops */
        .tasks-container {
            min-height: 100px;
        }
        
        .tasks-container:empty {
            background: repeating-linear-gradient(
                45deg,
                rgba(107, 114, 128, 0.1),
                rgba(107, 114, 128, 0.1) 10px,
                transparent 10px,
                transparent 20px
            );
            border: 2px dashed rgba(107, 114, 128, 0.3);
            border-radius: 8px;
        }
        
        .tasks-container:empty::after {
            content: "Upuść zadanie tutaj";
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100px;
            color: rgba(107, 114, 128, 0.5);
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        /* Column headers - Light default */
        .kanban-column-header {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            border: 1px solid #d1d5db;
            color: #374151;
        }
        
        /* Column headers - Dark mode */
        .dark .kanban-column-header {
            background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
            border: 1px solid #4b5563;
            color: #f9fafb;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.8);
        }
        
        /* Task cards - Light default */
        .task-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            color: #1f2937;
        }
        
        /* Task cards - Dark mode */
        .dark .task-card {
            background: #374151;
            border: 1px solid #4b5563;
            color: #f9fafb;
        }
        
        .task-card:hover {
            border-color: #3b82f6;
            background: #f9fafb;
        }
        
        .dark .task-card:hover {
            border-color: #60a5fa;
            background: #1f2937;
        }
        
        /* Priority badges */
        .priority-badge {
            border-color: rgba(0, 0, 0, 0.1) !important;
            color: #ffffff !important;
        }
        
        .dark .priority-badge {
            border-color: rgba(255, 255, 255, 0.2) !important;
        }
        
        /* Records - Light default */
        .record {
            background: #ffffff;
            border: 1px solid #e5e7eb;
        }
        
        /* Records - Dark mode */
        .dark .record {
            background: #1f2937;
            border: 1px solid #374151;
        }
        
        .record:hover {
            background: rgba(59, 130, 246, 0.1);
            border-color: #3b82f6;
        }
        
        .dark .record:hover {
            background: rgba(59, 130, 246, 0.2);
            border-color: #60a5fa;
        }
        
        /* Better text contrast for dark theme */
        .dark h3, .dark h4 {
            color: #f9fafb;
        }
        
        .dark .text-xs {
            color: #d1d5db;
        }
        
        @media (max-width: 768px) {
            .md\:w-\[24rem\] {
                width: 280px;
            }
        }
    </style>
</x-filament-panels::page>
