<x-filament-widgets::widget>
<div class="space-y-4">
    <h3 class="text-lg font-bold">Warianty ilościowe szablonu</h3>
    
    @if(count($priceVariants) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800">
                        <th class="px-4 py-3 border-b text-left">Wariant</th>
                        <th class="px-4 py-3 border-b text-right">Cena jedn.</th>
                        <th class="px-4 py-3 border-b text-right">Cena całkowita</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($priceVariants as $variant)
                        <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-4 py-3 font-medium">{{ $variant['name'] }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($variant['unit_price'], 2) }} {{ $variant['currency'] }}</td>
                            <td class="px-4 py-3 text-right font-bold">{{ number_format($variant['total_price'], 2) }} {{ $variant['currency'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">Brak wariantów ilościowych</p>
        </div>
    @endif
</div>
</x-filament-widgets::widget>
