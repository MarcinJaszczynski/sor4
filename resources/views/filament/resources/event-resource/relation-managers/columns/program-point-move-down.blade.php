@php
    $record = $getRecord();
    $disabled = ! $getLivewire()->canMoveDown($record);
@endphp

<button
    type="button"
    class="fi-icon-btn relative flex items-center justify-center rounded-lg outline-none transition duration-75 -m-2 h-9 w-9 text-gray-500 hover:text-gray-700 focus-visible:ring-2 focus-visible:ring-primary-600 dark:text-gray-400 dark:hover:text-gray-200"
    @if($disabled) disabled @endif
    wire:click="moveDownRecord({{ $record->getKey() }})"
    title="W dół"
>
    <span class="sr-only">W dół</span>
    <span aria-hidden="true">↓</span>
</button>
