@php
    $record = $getRecord();
    $disabled = ! $getLivewire()->canMovePrevDay($record);
@endphp

<button
    type="button"
    class="fi-icon-btn relative flex items-center justify-center rounded-lg outline-none transition duration-75 -m-2 h-9 w-9 text-red-600 hover:text-red-700 focus-visible:ring-2 focus-visible:ring-primary-600 dark:text-red-400 dark:hover:text-red-300"
    @if($disabled) disabled @endif
    wire:click="movePrevDayRecord({{ $record->getKey() }})"
    title="Poprzedni dzień"
>
    <span class="sr-only">Poprzedni dzień</span>
    <span aria-hidden="true">-D</span>
</button>
