@php
    $record = $getRecord();
    $disabledPrev = ! $getLivewire()->canMovePrevDay($record);
    $disabledNext = ! $getLivewire()->canMoveNextDay($record);
@endphp

<div class="flex flex-col items-center gap-2">
    <div class="flex w-full disabled:pointer-events-none justify-center text-center">
        <button
            type="button"
            class="fi-icon-btn relative flex items-center justify-center rounded-lg outline-none transition duration-75 -m-2 h-9 w-9 text-red-600 hover:text-red-700 focus-visible:ring-2 focus-visible:ring-primary-600 dark:text-red-400 dark:hover:text-red-300"
            @if($disabledPrev) disabled @endif
            wire:click="movePrevDayRecord({{ $record->getKey() }})"
            title="Poprzedni dzień"
        >
            <span class="sr-only">Poprzedni dzień</span>
            <span aria-hidden="true">-D</span>
        </button>
    </div>

    <div class="flex w-full disabled:pointer-events-none justify-center text-center">
        <button
            type="button"
            class="fi-icon-btn relative flex items-center justify-center rounded-lg outline-none transition duration-75 -m-2 h-9 w-9 text-red-600 hover:text-red-700 focus-visible:ring-2 focus-visible:ring-primary-600 dark:text-red-400 dark:hover:text-red-300"
            @if($disabledNext) disabled @endif
            wire:click="moveNextDayRecord({{ $record->getKey() }})"
            title="Następny dzień"
        >
            <span class="sr-only">Następny dzień</span>
            <span aria-hidden="true">+D</span>
        </button>
    </div>
</div>
