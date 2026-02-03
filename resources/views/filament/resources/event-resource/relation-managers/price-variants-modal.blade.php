<div class="space-y-4">
    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
        <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-300 mb-2">{{ $record->templatePoint?->name ?? 'Punkt programu' }}</h4>
        <p class="text-xs text-blue-700 dark:text-blue-400">Cena jednostkowa: {{ number_format($record->unit_price, 2) }} PLN</p>
        <p class="text-xs text-blue-700 dark:text-blue-400">Ilość: {{ $record->quantity }}</p>
    </div>

    @if(count($calculations) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800">
                        <th class="px-4 py-3 border-b text-left text-sm font-semibold">Wariant</th>
                        <th class="px-4 py-3 border-b text-right text-sm font-semibold">Koszt programu</th>
                        <th class="px-4 py-3 border-b text-right text-sm font-semibold">Koszt na osobę</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($calculations as $calc)
                        <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-4 py-3 text-sm font-medium">{{ $calc['name'] }}</td>
                            <td class="px-4 py-3 text-right text-sm">{{ number_format($calc['program_cost'], 2) }} PLN</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold">{{ number_format($calc['cost_per_person'], 2) }} PLN</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">Brak danych do wyświetlenia</p>
        </div>
    @endif
</div>
