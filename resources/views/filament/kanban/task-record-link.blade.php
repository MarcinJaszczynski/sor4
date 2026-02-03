@php
    /** @var \App\Models\Task $record */
    $editUrl = \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $record]);
@endphp

<div
    id="{{ $record->getKey() }}"
    class="record bg-white dark:bg-gray-700 rounded-lg px-4 py-2 cursor-grab font-medium text-gray-600 dark:text-gray-200"
    onclick="if(document.body.classList.contains('grabbing')) return; window.location.href='{{ $editUrl }}';"
    @if($record->timestamps && now()->diffInSeconds($record->{$record::UPDATED_AT}, true) < 3)
        x-data
        x-init="
            $el.classList.add('animate-pulse-twice', 'bg-primary-100', 'dark:bg-primary-800')
            $el.classList.remove('bg-white', 'dark:bg-gray-700')
            setTimeout(() => {
                $el.classList.remove('bg-primary-100', 'dark:bg-primary-800')
                $el.classList.add('bg-white', 'dark:bg-gray-700')
            }, 3000)
        "
    @endif
>
    <div class="flex items-center justify-between gap-2">
        <div class="min-w-0 truncate">
            {{ $record->{static::$recordTitleAttribute} }}
        </div>

        <a
            href="{{ $editUrl }}"
            class="shrink-0 text-xs text-primary-600 hover:underline"
            onclick="event.stopPropagation();"
        >
            Edytuj
        </a>
    </div>

    @if(!empty($record->last_activity_label))
        <div class="mt-2 text-[11px] text-gray-500">
            {{ $record->last_activity_label }}
            @if($record->last_activity_at)
                • {{ $record->last_activity_at->format('H:i d.m') }}
            @endif
        </div>
    @endif
</div>
