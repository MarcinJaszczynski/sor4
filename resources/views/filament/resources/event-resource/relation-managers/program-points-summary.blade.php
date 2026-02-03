<x-filament::section>
    <div class="space-y-4">
        <h3 class="text-lg font-bold">Podsumowanie kosztów program imprezy</h3>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">Koszt programu</p>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($calculations['program_cost'] ?? 0, 2) }} PLN</p>
            </div>
            <div class="text-center p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">Koszt transportu</p>
                <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ number_format($transportCost ?? 0, 2) }} PLN</p>
            </div>
            <div class="text-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">Koszt całkowity</p>
                <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($calculations['total_cost'] ?? 0, 2) }} PLN</p>
            </div>
            <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">Koszt na osobę</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($calculations['cost_per_person'] ?? 0, 2) }} PLN</p>
            </div>
        </div>

        @if($calculations['total_points'] > 0)
            <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Punkty programu w kalkulacji</p>
                        <p class="text-lg font-bold">{{ $calculations['calculation_points'] }}/{{ $calculations['total_points'] }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Liczba dni</p>
                        <p class="text-lg font-bold">{{ $calculations['days_count'] }}</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament::section>
