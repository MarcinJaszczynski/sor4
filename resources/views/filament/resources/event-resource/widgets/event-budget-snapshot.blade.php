<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Budżet imprezy (plan vs wykonanie)</x-slot>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
            <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-800">
                <div class="text-xs text-gray-500">Wpłaty</div>
                <div class="font-semibold">{{ number_format($payments ?? 0, 2, ',', ' ') }} PLN</div>
            </div>
            <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-800">
                <div class="text-xs text-gray-500">Koszty plan</div>
                <div class="font-semibold">{{ number_format($plannedCosts ?? 0, 2, ',', ' ') }} PLN</div>
            </div>
            <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-800">
                <div class="text-xs text-gray-500">Koszty zapłacone</div>
                <div class="font-semibold">{{ number_format($paidCosts ?? 0, 2, ',', ' ') }} PLN</div>
            </div>
            <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-800">
                <div class="text-xs text-gray-500">Zaalokowane na koszty</div>
                <div class="font-semibold">{{ number_format($allocated ?? 0, 2, ',', ' ') }} PLN</div>
            </div>
            <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-800">
                <div class="text-xs text-gray-500">Marża (plan)</div>
                <div class="font-semibold {{ ($marginPlan ?? 0) < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                    {{ number_format($marginPlan ?? 0, 2, ',', ' ') }} PLN
                </div>
            </div>
            <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-800">
                <div class="text-xs text-gray-500">Marża (real)</div>
                <div class="font-semibold {{ ($marginReal ?? 0) < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                    {{ number_format($marginReal ?? 0, 2, ',', ' ') }} PLN
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
