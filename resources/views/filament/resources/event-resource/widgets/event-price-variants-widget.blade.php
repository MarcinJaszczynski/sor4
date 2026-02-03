<x-filament-widgets::widget>
<div class="space-y-4">
    <h3 class="text-lg font-bold">Warianty cenowe imprezy</h3>
    
    @if(count($detailedCalculations) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800">
                        <th class="px-4 py-3 border-b text-left">Wariant</th>
                        <th class="px-4 py-3 border-b text-right">Koszt programu</th>
                        <th class="px-4 py-3 border-b text-right">Koszt transportu</th>
                        <th class="px-4 py-3 border-b text-right">Narzut</th>
                        <th class="px-4 py-3 border-b text-right">Podatek</th>
                        <th class="px-4 py-3 border-b text-right">Koszt całkowity</th>
                        <th class="px-4 py-3 border-b text-right">Koszt na osobę</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detailedCalculations as $calc)
                        <tr class="@if($calc['qty'] == $record->participant_count) bg-blue-50 dark:bg-blue-900/20 font-semibold @endif">
                            <td class="px-4 py-3 border-b">{{ $calc['name'] }}</td>
                            <td class="px-4 py-3 border-b text-right">{{ number_format($calc['program_cost'], 2) }} PLN</td>
                            <td class="px-4 py-3 border-b text-right">{{ number_format($calc['transport_cost'], 2) }} PLN</td>
                            <td class="px-4 py-3 border-b text-right">{{ number_format($calc['markup_amount'] ?? 0, 2) }} PLN</td>
                            <td class="px-4 py-3 border-b text-right">{{ number_format($calc['tax_amount'] ?? 0, 2) }} PLN</td>
                            <td class="px-4 py-3 border-b text-right font-bold">{{ number_format($calc['total_cost'], 2) }} PLN</td>
                            <td class="px-4 py-3 border-b text-right font-bold">{{ number_format($calc['cost_per_person'], 2) }} PLN</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
            * Wiersz podświetlony odpowiada bieżącej liczbie uczestników ({{ $record->participant_count ?? 0 }})
        </p>
    @else
        <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">Brak danych do wyświetlenia</p>
        </div>
    @endif
</div>
</x-filament-widgets::widget>
