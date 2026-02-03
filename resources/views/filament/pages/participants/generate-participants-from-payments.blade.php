<x-filament-panels::page>
    <x-filament::card>
        {{ $this->form }}

        <div class="flex justify-end mt-4">
            <x-filament::button wire:click="generate">
                Generuj
            </x-filament::button>
        </div>
    </x-filament::card>

    @if(!empty($lastResult))
        <x-filament::section>
            <x-slot name="heading">
                Wynik
            </x-slot>

            @if(!($lastResult['ok'] ?? false))
                <div class="p-3 rounded border bg-red-50 text-red-800 border-red-200">
                    {{ $lastResult['error'] ?? 'Nieznany błąd' }}
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-3 rounded border bg-white dark:bg-gray-900">
                        <div class="text-xs text-gray-500">Utworzone</div>
                        <div class="text-lg font-bold">{{ $lastResult['created'] ?? 0 }}</div>
                    </div>
                    <div class="p-3 rounded border bg-white dark:bg-gray-900">
                        <div class="text-xs text-gray-500">Pominięte</div>
                        <div class="text-lg font-bold">{{ $lastResult['skipped'] ?? 0 }}</div>
                    </div>
                </div>

                @if(!empty($lastResult['errors']))
                    <div class="mt-4 p-3 rounded border bg-orange-50 text-orange-900 border-orange-200">
                        <div class="font-semibold mb-2">Błędy (pierwsze {{ count($lastResult['errors']) }})</div>
                        <ul class="list-disc list-inside text-sm">
                            @foreach($lastResult['errors'] as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endif
        </x-filament::section>
    @endif
</x-filament-panels::page>
