<x-filament-widgets::widget>
<div class="space-y-4">
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
        <!-- Status aktywności -->
        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p class="text-xs text-gray-600 dark:text-gray-400">Status</p>
            <p class="text-sm font-bold">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $stats['is_active'] ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' }}">
                    {{ $stats['is_active'] ? 'Aktywny' : 'Nieaktywny' }}
                </span>
            </p>
        </div>

        <!-- Dni -->
        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <p class="text-xs text-gray-600 dark:text-gray-400">Dni</p>
            <p class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $stats['duration_days'] }}</p>
        </div>

        <!-- Punkty programu -->
        <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
            <p class="text-xs text-gray-600 dark:text-gray-400">Punkty</p>
            <p class="text-lg font-bold text-green-600 dark:text-green-400">{{ $stats['total_points'] }}</p>
        </div>

        <!-- W kalkulacji -->
        <div class="p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
            <p class="text-xs text-gray-600 dark:text-gray-400">Kalkulacja</p>
            <p class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ $stats['calculation_points'] }}/{{ $stats['total_points'] }}</p>
        </div>

        <!-- Transfer km -->
        <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
            <p class="text-xs text-gray-600 dark:text-gray-400">Transfer</p>
            <p class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ $stats['transfer_km'] }} km</p>
        </div>

        <!-- Program km -->
        <div class="p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
            <p class="text-xs text-gray-600 dark:text-gray-400">Program</p>
            <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['program_km'] }} km</p>
        </div>
    </div>
</div>
</x-filament-widgets::widget>
