<div class="pp-dnd-root">
    <div class="pp-dnd-list">
        @foreach($points as $p)
            <div class="pp-row" data-pp-id="{{ $p['id'] }}" data-parent-id="{{ $p['parent_id'] }}" data-day="{{ $p['day'] }}" data-order="{{ $p['order'] }}">
                <div class="flex items-center justify-between p-2 border rounded mb-1 bg-white">
                    <div class="flex items-center gap-4">
                        <div class="font-medium">{{ $p['name'] }}</div>
                        <div class="text-xs text-gray-500">(Dzień {{ $p['day'] }})</div>
                        <div>
                            <input type="number" min="1" step="1" value="{{ $p['day'] }}" class="w-20 text-sm rounded border-gray-200" wire:change="setDay({{ $p['id'] }}, $event.target.value)">
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" wire:click.prevent="moveUp({{ $p['id'] }})" class="pp-move-up">↑</button>
                        <button type="button" wire:click.prevent="moveDown({{ $p['id'] }})" class="pp-move-down">↓</button>
                        <button type="button" wire:click.prevent="openTaskModal({{ $p['id'] }})" class="text-sm text-primary-600 hover:text-primary-500">
                            Zadania
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-2">
        <button type="button" class="filament-button-primary pp-dnd-save">Zapisz kolejność</button>
    </div>

    {{-- Task Modal --}}
    @if($showTaskModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-lg font-semibold">Zadania dla punktu programu</h3>
                <button wire:click="closeTaskModal" class="text-gray-500 hover:text-gray-700">&times;</button>
            </div>
            
            <div class="p-4">
                {{-- New Task Form --}}
                <div class="mb-6 p-4 bg-gray-50 rounded border">
                    <h4 class="font-medium mb-2">Dodaj nowe zadanie</h4>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tytuł</label>
                            <input type="text" wire:model.defer="taskModalData.title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            @error('taskModalData.title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select wire:model.defer="taskModalData.status_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Priorytet</label>
                                <select wire:model.defer="taskModalData.priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                    <option value="low">Niski</option>
                                    <option value="medium">Średni</option>
                                    <option value="high">Wysoki</option>
                                    <option value="urgent">Pilny</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Przypisany do</label>
                                <select wire:model.defer="taskModalData.assignee_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                    <option value="">-- Brak --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Termin</label>
                                <input type="date" wire:model.defer="taskModalData.due_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Opis</label>
                            <textarea wire:model.defer="taskModalData.description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button wire:click="saveTask" class="bg-primary-600 text-white px-4 py-2 rounded hover:bg-primary-700 text-sm">Dodaj zadanie</button>
                        </div>
                    </div>
                </div>

                {{-- Existing Tasks List --}}
                <div>
                    <h4 class="font-medium mb-2">Lista zadań</h4>
                    @if(count($tasksForCurrentPoint) > 0)
                        <div class="space-y-2">
                            @foreach($tasksForCurrentPoint as $task)
                                <div class="flex items-center justify-between p-3 bg-white border rounded shadow-sm">
                                    <div>
                                        <div class="font-medium">{{ $task->title }}</div>
                                        <div class="text-xs text-gray-500">
                                            Status: <span class="font-semibold" style="color: {{ $task->status->color ?? '#000' }}">{{ $task->status->name ?? '-' }}</span> | 
                                            Priorytet: {{ $task->priority }} | 
                                            Przypisani: {{ $task->assignees->isNotEmpty() ? $task->assignees->pluck('name')->join(', ') : ($task->assignee->name ?? 'Brak') }}
                                        </div>
                                    </div>
                                    <button wire:click="deleteTask({{ $task->id }})" class="text-red-600 hover:text-red-800 text-sm">Usuń</button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">Brak zadań dla tego punktu.</p>
                    @endif
                </div>
            </div>
            
            <div class="p-4 border-t bg-gray-50 flex justify-end">
                <button wire:click="closeTaskModal" class="px-4 py-2 bg-white border rounded text-gray-700 hover:bg-gray-50">Zamknij</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Load SortableJS from CDN for nicer drag & drop (graceful if already loaded) --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</div>
