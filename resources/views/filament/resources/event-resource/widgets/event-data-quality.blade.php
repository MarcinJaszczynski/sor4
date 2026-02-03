<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Jakość danych</x-slot>

        @if(empty($issues))
            <div class="text-sm text-gray-500">Brak krytycznych braków danych.</div>
        @else
            <ul class="space-y-2">
                @foreach($issues as $issue)
                    <li class="flex items-start justify-between gap-2">
                        <span class="text-sm text-gray-800 dark:text-gray-100">{{ $issue['label'] }}</span>
                        @if(!empty($issue['url']))
                            <a class="text-sm text-primary-600 hover:underline" href="{{ $issue['url'] }}" target="_blank" rel="noreferrer">Przejdź</a>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
