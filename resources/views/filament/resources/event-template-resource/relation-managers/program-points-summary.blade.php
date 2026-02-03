<x-filament::section>
    <div class="space-y-4">
        <h3 class="text-lg font-bold">Podsumowanie programu szablonu</h3>
        
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">Wszystkie punkty</p>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $calculations['total_points'] ?? 0 }}</p>
            </div>
            
            <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">W programie</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $calculations['program_points'] ?? 0 }}</p>
            </div>

            <div class="text-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">W kalkulacji</p>
                <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $calculations['calculation_points'] ?? 0 }}</p>
            </div>
        </div>

        @if($calculations['total_points'] > 0)
            <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Liczba dni w szablonie</p>
                        <p class="text-lg font-bold">{{ $calculations['duration_days'] }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Wykorzystanych dni</p>
                        <p class="text-lg font-bold">{{ $calculations['days_count'] }}</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament::section>
