@props(['status'])

<div class="w-72 shrink-0 mb-5 min-h-full flex flex-col mx-1">
    @include(static::$headerView)

    <div
        data-status-id="{{ $status['id'] }}"
        class="flex flex-col flex-1 gap-2 p-2 rounded-xl border {{ $status['bg_class'] ?? 'bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-800' }}"
    >
        @foreach($status['records'] as $record)
            @include(static::$recordView)
        @endforeach
    </div>
</div>
