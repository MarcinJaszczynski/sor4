<div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
        Szczegółowy budżet imprezy
    </h3>

    <div class="mt-4 overflow-x-auto">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-4 py-3">Kategoria kosztu</th>
                    <th scope="col" class="px-4 py-3 text-right">Planowane (Budżet)</th>
                    <th scope="col" class="px-4 py-3 text-right">Poniesione (Rzeczywiste)</th>
                    <th scope="col" class="px-4 py-3 text-right">Różnica</th>
                    <th scope="col" class="px-4 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($budgetRows as $row)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $row['name'] }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            {{ number_format($row['planned'], 2, ',', ' ') }} {{ $currency }}
                        </td>
                        <td class="px-4 py-3 text-right font-medium">
                            {{ number_format($row['actual'], 2, ',', ' ') }} {{ $currency }}
                        </td>
                        <td class="px-4 py-3 text-right {{ $row['diff'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($row['diff'], 2, ',', ' ') }} {{ $currency }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($row['diff'] >= 0)
                                <span class="bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">OK</span>
                            @else
                                <span class="bg-red-100 text-red-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Przekroczono</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="font-bold text-gray-900 dark:text-white border-t-2 border-gray-200">
                <tr>
                    <td class="px-4 py-3">SUMA</td>
                    <td class="px-4 py-3 text-right">{{ number_format($totalPlanned, 2, ',', ' ') }} {{ $currency }}</td>
                    <td class="px-4 py-3 text-right">{{ number_format($totalActual, 2, ',', ' ') }} {{ $currency }}</td>
                    <td class="px-4 py-3 text-right {{ $totalDiff >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($totalDiff, 2, ',', ' ') }} {{ $currency }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
