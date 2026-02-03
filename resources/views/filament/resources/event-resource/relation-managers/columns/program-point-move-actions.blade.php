@php
    $record = $getRecord();
    $disabledUp = ! $getLivewire()->canMoveUp($record);
    $disabledDown = ! $getLivewire()->canMoveDown($record);
@endphp

<div class="flex flex-col items-center gap-2">
    <div class="flex w-full disabled:pointer-events-none justify-center text-center">
        <button
            type="button"
            class="fi-icon-btn relative flex items-center justify-center rounded-lg outline-none transition duration-75 -m-2 h-9 w-9 text-gray-500 hover:text-gray-700 focus-visible:ring-2 focus-visible:ring-primary-600 dark:text-gray-400 dark:hover:text-gray-200"
            @if($disabledUp) disabled @endif
            wire:click="moveUpRecord({{ $record->getKey() }})"
            title="W górę"
        >
            <span class="sr-only">W górę</span>
            <span aria-hidden="true">↑</span>
        </button>
    </div>

    <div class="flex w-full disabled:pointer-events-none justify-center text-center">
        <button
            type="button"
            class="fi-icon-btn relative flex items-center justify-center rounded-lg outline-none transition duration-75 -m-2 h-9 w-9 text-gray-500 hover:text-gray-700 focus-visible:ring-2 focus-visible:ring-primary-600 dark:text-gray-400 dark:hover:text-gray-200"
            @if($disabledDown) disabled @endif
            wire:click="moveDownRecord({{ $record->getKey() }})"
            title="W dół"
        >
            <span class="sr-only">W dół</span>
            <span aria-hidden="true">↓</span>
        </button>
    </div>
</div>
