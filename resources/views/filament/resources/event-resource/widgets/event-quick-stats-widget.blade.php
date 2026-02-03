<x-filament-widgets::widget>
<div class="space-y-4">
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3">
        <!-- Status -->
        <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p class="text-xs text-gray-600 dark:text-gray-400">Status</p>
            <p class="text-sm font-bold">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                    @if($stats['status'] === 'draft') bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200
                    @elseif($stats['status'] === 'confirmed') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                    @elseif($stats['status'] === 'in_progress') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
                    @elseif($stats['status'] === 'completed') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300
                    @elseif($stats['status'] === 'cancelled') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300
                    @else bg-gray-100 text-gray-800 @endif">
                    {{ match($stats['status'] ?? '') {
                        'draft' => 'Szkic',
                        'confirmed' => 'Potwierdzona',
                        'in_progress' => 'W trakcie',
                        'completed' => 'Zakończona',
                        'cancelled' => 'Anulowana',
                        default => ucfirst($stats['status'] ?? 'Nieznany')
                    } }}
                </span>
            </p>
        </div>

        <!-- Uczestnicy -->
        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <p class="text-xs text-gray-600 dark:text-gray-400">Uczestnicy</p>
            <p class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $stats['participant_count'] }}</p>
        </div>

        <!-- Dni -->
        <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
            <p class="text-xs text-gray-600 dark:text-gray-400">Dni</p>
            <p class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ $stats['duration_days'] }}</p>
        </div>

        <!-- Punkty programu -->
        <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
            <p class="text-xs text-gray-600 dark:text-gray-400">Punkty/Kalkulacja</p>
            <p class="text-sm font-bold text-green-600 dark:text-green-400">{{ $stats['calculation_points'] }}/{{ $stats['total_points'] }}</p>
        </div>

        <!-- Koszt programu -->
        <div class="p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
            <p class="text-xs text-gray-600 dark:text-gray-400">Program</p>
            <p class="text-sm font-bold text-orange-600 dark:text-orange-400">{{ number_format($stats['program_cost'], 0) }} PLN</p>
        </div>

        <!-- Koszt transportu -->
        <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
            <p class="text-xs text-gray-600 dark:text-gray-400">Transport</p>
            <p class="text-sm font-bold text-red-600 dark:text-red-400">{{ number_format($stats['transport_cost'], 0) }} PLN</p>
        </div>

        <!-- Koszt całkowity -->
        <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
            <p class="text-xs text-gray-600 dark:text-gray-400">Całkowity</p>
            <p class="text-sm font-bold text-purple-600 dark:text-purple-400">{{ number_format($stats['total_cost'], 0) }} PLN</p>
        </div>

        <!-- Koszt na osobę -->
        <div class="p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
            <p class="text-xs text-gray-600 dark:text-gray-400">Na osobę</p>
            <p class="text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($stats['cost_per_person'], 0) }} PLN</p>
        </div>
    </div>
</div>
</x-filament-widgets::widget>
