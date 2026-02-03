<x-filament-panels::page>
    <x-filament::section>
        <div class="space-y-6">
            {{ $this->form }}

            <div class="text-right">
                <x-filament::button wire:click="import">
                    Analizuj plik
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>

    @if(!empty($reconciliationResults))
        <x-filament::section>
            <h2 class="text-lg font-bold mb-4">Wyniki analizy</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">Data</th>
                            <th scope="col" class="px-6 py-3">Nadawca</th>
                            <th scope="col" class="px-6 py-3">Tytuł</th>
                            <th scope="col" class="px-6 py-3">Kwota</th>
                            <th scope="col" class="px-6 py-3">Status dopasowania</th>
                            <th scope="col" class="px-6 py-3">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reconciliationResults as $result)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-6 py-4">{{ $result['date'] }}</td>
                                <td class="px-6 py-4">{{ $result['sender'] }}</td>
                                <td class="px-6 py-4">{{ $result['title'] }}</td>
                                <td class="px-6 py-4 font-bold">{{ number_format($result['amount'], 2) }} PLN</td>
                                <td class="px-6 py-4">
                                    @if($result['match_found'])
                                        <span class="bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">
                                            Umowa: {{ $result['contract_id'] }}
                                        </span>
                                    @else
                                        <span class="bg-red-100 text-red-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">
                                            Nie rozpoznano
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($result['match_found'])
                                        <button class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Zaksięguj</button>
                                    @else
                                        <button class="font-medium text-gray-600 dark:text-gray-500 hover:underline">Przypisz ręcznie</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
