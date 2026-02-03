<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Kalendarz (najbliższe 14 dni)
        </x-slot>

        <div class="space-y-2">
            @php
                $shown = 0;
            @endphp

            @forelse ($days as $day)
                @php
                    $hasItems = !empty($day['events']) || !empty($day['tasks']);
                    if (! $hasItems) {
                        continue;
                    }
                    $shown++;
                    if ($shown > 10) {
                        break;
                    }
                @endphp

                <div class="rounded-md border border-gray-200 p-2 {{ $day['date']->isToday() ? 'bg-primary-50' : 'bg-white' }}">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-semibold">
                            {{ $day['date']->isToday() ? 'Dziś' : $day['date']->translatedFormat('D') }}
                            <span class="text-gray-500 font-normal">{{ $day['date']->format('d.m') }}</span>
                        </div>
                        <span class="text-xs text-gray-500">{{ count($day['events']) + count($day['tasks']) }}</span>
                    </div>

                    <ul class="mt-2 space-y-1">
                        @foreach (array_slice($day['events'], 0, 3) as $event)
                            <li class="text-xs flex items-start gap-2">
                                <span class="mt-0.5 inline-block w-2 h-2 rounded-full bg-teal-500 flex-shrink-0"></span>
                                <a class="text-primary-600 hover:underline" href="{{ \App\Filament\Resources\EventResource::getUrl('edit', ['record' => $event]) }}">
                                    {{ $event->name }}
                                </a>
                            </li>
                        @endforeach
                        @foreach (array_slice($day['tasks'], 0, 3) as $task)
                            <li class="text-xs flex items-start gap-2">
                                <span class="mt-0.5 inline-block w-2 h-2 rounded-full bg-indigo-500 flex-shrink-0"></span>
                                <a class="text-primary-600 hover:underline" href="{{ \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $task]) }}">
                                    {{ $task->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    @if (count($day['events']) + count($day['tasks']) > 6)
                        <div class="mt-2 text-xs text-gray-400">+{{ (count($day['events']) + count($day['tasks'])) - 6 }} więcej</div>
                    @endif
                </div>
            @empty
                <div class="text-xs text-gray-400">Brak wpisów w najbliższych dniach</div>
            @endforelse

            @if ($shown === 0)
                <div class="text-xs text-gray-400">Brak wpisów w najbliższych dniach</div>
            @endif
        </div>

        <div class="mt-3 text-right">
            <a class="text-xs text-primary-600 hover:underline" href="{{ \App\Filament\Pages\Calendar::getUrl() }}">
                Otwórz pełny kalendarz
            </a>
            <span class="mx-1 text-xs text-gray-400">·</span>
            <a class="text-xs text-primary-600 hover:underline" href="{{ \App\Filament\Pages\Schedule::getUrl() }}">
                Otwórz grafik (Gantt)
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
