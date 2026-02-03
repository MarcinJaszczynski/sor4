<div class="p-4">
    <div class="bg-gray-50 p-4 rounded-lg mb-6 border border-gray-200">
        <h4 class="text-sm font-medium text-gray-700 mb-3">Dodaj nowe zadanie</h4>
        <div class="grid grid-cols-1 gap-4">
            <div>
                <input type="text" wire:model="title" placeholder="Tytuł zadania" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <textarea wire:model="description" placeholder="Opis (opcjonalnie)" rows="2" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <select wire:model="status_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
                <select wire:model="assignee_ids" multiple class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm h-24">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end">
                <button wire:click="saveTask" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Dodaj zadanie
                </button>
            </div>
        </div>
    </div>

    <div class="space-y-3 max-h-96 overflow-y-auto">
        @forelse($tasks as $task)
            <div class="flex items-start justify-between bg-white border rounded-lg p-3 shadow-sm">
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" style="background-color: {{ $task->status->color ?? '#e5e7eb' }}20; color: {{ $task->status->color ?? '#374151' }}">
                            {{ $task->status->name ?? 'Status' }}
                        </span>
                        <a href="{{ route('filament.admin.resources.tasks.edit', ['record' => $task]) }}" target="_blank" class="hover:underline">
                            <h5 class="text-sm font-medium text-gray-900">{{ $task->title }}</h5>
                        </a>
                    </div>
                    @if($task->description)
                        <p class="text-xs text-gray-500 mt-1">{{ $task->description }}</p>
                    @endif
                    <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
                        @if($task->assignees->isNotEmpty())
                            <span class="flex items-center gap-1" title="{{ $task->assignees->pluck('name')->join(', ') }}">
                                <x-heroicon-o-users class="w-3 h-3" />
                                {{ $task->assignees->pluck('name')->join(', ') }}
                            </span>
                        @elseif($task->assignee)
                            <span class="flex items-center gap-1">
                                <x-heroicon-o-user class="w-3 h-3" />
                                {{ $task->assignee->name }}
                            </span>
                        @endif
                        @if($task->due_date)
                            <span class="flex items-center gap-1">
                                <x-heroicon-o-calendar class="w-3 h-3" />
                                {{ $task->due_date->format('Y-m-d') }}
                            </span>
                        @endif
                    </div>
                </div>
                <button wire:click="deleteTask({{ $task->id }})" wire:confirm="Czy na pewno usunąć to zadanie?" class="text-gray-400 hover:text-red-500 ml-2">
                    <x-heroicon-o-trash class="w-4 h-4" />
                </button>
            </div>
        @empty
            <p class="text-center text-gray-500 text-sm py-4">Brak zadań dla tego elementu.</p>
        @endforelse
    </div>
</div>
