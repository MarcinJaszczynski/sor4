<div class="space-y-6">
    <!-- Podsumowanie kosztów -->
    <div class="space-y-4">
        <h4 class="text-md font-semibold">Podsumowanie kosztów</h4>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">Koszt programu</p>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($calculations['program_cost'] ?? $calculations['total_program_cost'] ?? 0, 2) }} PLN</p>
            </div>
            <div class="text-center p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">Koszt transportu</p>
                <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ number_format($calculations['transport_cost'] ?? 0, 2) }} PLN</p>
            </div>
            <div class="text-center p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">Koszt zakwaterowania</p>
                <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($calculations['accommodation_cost'] ?? 0, 2) }} PLN</p>
            </div>
            <div class="text-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">Koszt całkowity</p>
                <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($calculations['total_cost'] ?? 0, 2) }} PLN</p>
                @if(!empty($calculations['currencies']))
                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        +
                        @foreach($calculations['currencies'] as $code => $amount)
                            <span class="mr-2">{{ number_format((float) $amount, 2) }} {{ $code }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">Koszt na osobę</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($calculations['cost_per_person'] ?? 0, 2) }} PLN</p>
                @if(!empty($calculations['currencies_per_person']))
                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        +
                        @foreach($calculations['currencies_per_person'] as $code => $amount)
                            <span class="mr-2">{{ number_format((float) $amount, 2) }} {{ $code }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        @if(!empty($calculations['currencies']))
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Koszty w walutach obcych (nieprzeliczone)</p>
                <div class="flex flex-wrap gap-3">
                    @foreach($calculations['currencies'] as $code => $amount)
                        <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-3 py-1 text-sm font-medium text-gray-800 dark:text-gray-200">
                            {{ number_format((float) $amount, 2) }} {{ $code }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <p class="text-xs text-gray-600 dark:text-gray-400">Narzut</p>
                <p class="text-lg font-bold">
                    {{ number_format($calculations['markup_amount'] ?? 0, 2) }} PLN
                    @if(($calculations['is_min_markup_applied'] ?? false) && ($calculations['min_markup_amount'] ?? 0) > 0)
                        <span class="ml-1 text-xs text-orange-600">(min {{ number_format($calculations['min_markup_amount'], 2) }} PLN)</span>
                    @endif
                </p>
            </div>
            <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <p class="text-xs text-gray-600 dark:text-gray-400">Podatek</p>
                <p class="text-lg font-bold">{{ number_format($calculations['tax_amount'] ?? 0, 2) }} PLN</p>
            </div>
            <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <p class="text-xs text-gray-600 dark:text-gray-400">Liczba osób w kosztach</p>
                <p class="text-lg font-bold">{{ $calculations['total_count_for_costs'] ?? ($calculations['participant_count'] ?? 0) }}</p>
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <p class="text-xs text-gray-600 dark:text-gray-400">Uczestnicy</p>
                <p class="text-lg font-bold">{{ $calculations['participants_count'] ?? 0 }}</p>
            </div>
            <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <p class="text-xs text-gray-600 dark:text-gray-400">Gratis</p>
                <p class="text-lg font-bold">{{ $calculations['gratis_count'] ?? 0 }}</p>
            </div>
            <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <p class="text-xs text-gray-600 dark:text-gray-400">Opiekunowie</p>
                <p class="text-lg font-bold">{{ $calculations['staff_count'] ?? 0 }}</p>
            </div>
            <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <p class="text-xs text-gray-600 dark:text-gray-400">Piloci</p>
                <p class="text-lg font-bold">{{ $calculations['guide_count'] ?? 0 }}</p>
            </div>
            <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <p class="text-xs text-gray-600 dark:text-gray-400">Kierowcy</p>
                <p class="text-lg font-bold">{{ $calculations['driver_count'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- Warianty cenowe -->
    @if(count($detailedCalculations) > 0)
        <div class="space-y-4">
            <h4 class="text-md font-semibold">Warianty cenowe dla różnych liczb uczestników</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <th class="px-4 py-3 border-b text-left">Wariant</th>
                            <th class="px-4 py-3 border-b text-right">Koszt programu</th>
                            <th class="px-4 py-3 border-b text-right">Koszt transportu</th>
                            <th class="px-4 py-3 border-b text-right">Koszt zakwaterowania</th>
                            <th class="px-4 py-3 border-b text-right">Narzut</th>
                            <th class="px-4 py-3 border-b text-right">Podatek</th>
                            <th class="px-4 py-3 border-b text-right">Koszt całkowity</th>
                            <th class="px-4 py-3 border-b text-right">Koszt na osobę</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($detailedCalculations as $calc)
                            <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-800 @if($calc['qty'] == $calculations['participant_count']) bg-blue-50 dark:bg-blue-900/20 font-semibold @endif">
                                <td class="px-4 py-3">{{ $calc['name'] }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($calc['program_cost'], 2) }} PLN</td>
                                <td class="px-4 py-3 text-right">{{ number_format($calc['transport_cost'], 2) }} PLN</td>
                                <td class="px-4 py-3 text-right">{{ number_format($calc['accommodation_cost'] ?? 0, 2) }} PLN</td>
                                <td class="px-4 py-3 text-right">{{ number_format($calc['markup_amount'] ?? 0, 2) }} PLN</td>
                                <td class="px-4 py-3 text-right">{{ number_format($calc['tax_amount'] ?? 0, 2) }} PLN</td>
                                <td class="px-4 py-3 text-right font-bold">{{ number_format($calc['total_cost'], 2) }} PLN</td>
                                <td class="px-4 py-3 text-right font-bold">{{ number_format($calc['cost_per_person'], 2) }} PLN</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                * Wiersz podświetlony odpowiada bieżącej liczbie uczestników ({{ $calculations['participant_count'] ?? 0 }})
            </p>
        </div>
    @endif
</div>
