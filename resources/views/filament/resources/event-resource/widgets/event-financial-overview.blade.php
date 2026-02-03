@php
    $activeRelation = request()->get('activeRelationManager');
    $showFinanceOverview = ($activeRelation === '2');
@endphp

<x-filament::widget>
    @if($showFinanceOverview)
    <x-filament::card>
        <h2 class="text-lg font-bold mb-4">Finanse Imprezy</h2>

        @if(!$record)
            <p class="text-gray-500">Brak danych imprezy.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <!-- WPŁATY -->
                <div class="border rounded-lg p-3 bg-gray-50 dark:bg-gray-800">
                    <h3 class="font-bold text-custom-600 mb-2 flex items-center gap-2">
                        <x-heroicon-o-banknotes class="w-5 h-5 text-green-600"/>
                        WPŁATY OD KLIENTÓW
                    </h3>
                    
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Planowane (Umowy):</span>
                            <span class="font-bold">{{ number_format($expectedIncome, 2, ',', ' ') }} PLN</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Otrzymane:</span>
                            <span class="font-bold text-green-600">{{ number_format($receivedIncome, 2, ',', ' ') }} PLN</span>
                        </div>
                         <div class="flex justify-between border-t mt-2 pt-2">
                            <span class="text-gray-600">Pozostało:</span>
                            <span class="font-bold {{ $expectedIncome - $receivedIncome > 0 ? 'text-red-500' : 'text-green-500' }}">
                                {{ number_format($expectedIncome - $receivedIncome, 2, ',', ' ') }} PLN
                            </span>
                        </div>
                    </div>

                    @if(!empty($pilotCashTotals))
                        <div class="mt-3 pt-3 border-t">
                            <div class="text-xs text-gray-500 mb-2">Gotówka dla pilota (wg walut)</div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($pilotCashTotals as $currency => $total)
                                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">
                                        {{ number_format($total, 2, ',', ' ') }} {{ $currency }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(!empty($incomeAlerts))
                        <div class="mt-4 p-2 bg-red-100 border border-red-300 rounded text-red-800 text-sm">
                            <ul class="list-disc list-inside">
                                @foreach($incomeAlerts as $alert)
                                    <li>{{ $alert }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <!-- WYDATKI -->
                <div class="border rounded-lg p-3 bg-gray-50 dark:bg-gray-800">
                    <h3 class="font-bold text-red-600 mb-2 flex items-center gap-2">
                        <x-heroicon-o-credit-card class="w-5 h-5 text-red-600"/>
                        WYDATKI (Koszty)
                    </h3>
                    
                     <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Zaksięgowane (EventCost):</span>
                            <span class="font-bold">{{ number_format($plannedCosts, 2, ',', ' ') }} PLN</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Opłacone:</span>
                            <span class="font-bold text-green-600">{{ number_format($paidCosts, 2, ',', ' ') }} PLN</span>
                        </div>
                         <div class="flex justify-between border-t mt-2 pt-2">
                            <span class="text-gray-600">Do zapłaty:</span>
                            <span class="font-bold {{ $plannedCosts - $paidCosts > 0 ? 'text-red-500' : 'text-green-500' }}">
                                {{ number_format($plannedCosts - $paidCosts, 2, ',', ' ') }} PLN
                            </span>
                        </div>
                    </div>

                    @if(!empty($expenseAlerts))
                        <div class="mt-4 p-2 bg-red-100 border border-red-300 rounded text-red-800 text-sm">
                            <ul class="list-disc list-inside">
                                @foreach($expenseAlerts as $alert)
                                    <li>{{ $alert }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <!-- WYNIK / SALDO -->
                <div class="md:col-span-2 xl:col-span-3 border rounded-lg p-3 bg-gray-50 dark:bg-gray-800">
                    <h3 class="font-bold text-gray-700 dark:text-gray-200 mb-2 flex items-center gap-2">
                        <x-heroicon-o-chart-bar class="w-5 h-5 text-blue-600"/>
                        PODSUMOWANIE (P&L)
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                            <div class="text-xs text-gray-500">Oczekiwana marża</div>
                            <div class="mt-1 text-lg font-bold {{ ($expectedProfit ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($expectedProfit ?? 0, 2, ',', ' ') }} PLN
                            </div>
                            <div class="text-xs text-gray-500">(Planowane wpłaty - planowane koszty)</div>
                        </div>

                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                            <div class="text-xs text-gray-500">Realna marża (na dziś)</div>
                            <div class="mt-1 text-lg font-bold {{ ($realProfit ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($realProfit ?? 0, 2, ',', ' ') }} PLN
                            </div>
                            <div class="text-xs text-gray-500">(Otrzymane wpłaty - opłacone koszty)</div>
                        </div>

                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                            <div class="text-xs text-gray-500">Saldo gotówkowe (na dziś)</div>
                            <div class="mt-1 text-lg font-bold {{ ($cashBalance ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($cashBalance ?? 0, 2, ',', ' ') }} PLN
                            </div>
                            <div class="text-xs text-gray-500">(Otrzymane - opłacone)</div>
                        </div>
                    </div>
                </div>

                {{-- KONTROLA WPŁAT / ALOKACJE - UKRYTE (funkcjonalność nieużywana) --}}
                @if(false)
                <div class="md:col-span-2 border rounded-lg p-4 bg-gray-50 dark:bg-gray-800">
                    <h3 class="font-bold text-gray-700 dark:text-gray-200 mb-2 flex items-center gap-2">
                        <x-heroicon-o-shield-check class="w-5 h-5 text-teal-600"/>
                        KONTROLA WPŁAT I POKRYCIA KOSZTÓW
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                            <div class="text-xs text-gray-700 dark:text-gray-300">Zaalokowane na koszty (PLN)</div>
                            <div class="mt-1 text-lg font-bold text-gray-900 dark:text-gray-100">
                                {{ number_format($allocatedToCosts ?? 0, 2, ',', ' ') }} PLN
                            </div>
                            <div class="text-xs text-gray-700 dark:text-gray-300">(Suma alokacji wpłat → koszty)</div>
                        </div>

                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                            <div class="text-xs text-gray-700 dark:text-gray-300">Niezaalokowane z wpłat (PLN)</div>
                            <div class="mt-1 text-lg font-bold {{ ($unallocatedPayments ?? 0) > 0 ? 'text-orange-600' : 'text-green-600' }}">
                                {{ number_format($unallocatedPayments ?? 0, 2, ',', ' ') }} PLN
                            </div>
                            <div class="text-xs text-gray-700 dark:text-gray-300">(Otrzymane − zaalokowane)</div>
                        </div>

                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                            <div class="text-xs text-gray-700 dark:text-gray-300">Koszty niepokryte alokacjami (PLN)</div>
                            <div class="mt-1 text-lg font-bold {{ ($uncoveredCosts ?? 0) > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($uncoveredCosts ?? 0, 2, ',', ' ') }} PLN
                            </div>
                            <div class="text-xs text-gray-700 dark:text-gray-300">(Planowane koszty − alokacje)</div>
                        </div>
                    </div>

                    @if(!empty($allocationAlerts))
                        <div class="mt-4 p-2 bg-orange-100 border border-orange-300 rounded text-orange-900 text-sm">
                            <ul class="list-disc list-inside">
                                @foreach($allocationAlerts as $alert)
                                    <li>{{ $alert }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(!empty($contractsSummary))
                        <div class="mt-4">
                            <div class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Rozbicie wpłat per umowa</div>
                            <div class="overflow-x-auto border rounded-lg bg-white dark:bg-gray-900">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                        <tr>
                                            <th class="text-left px-3 py-2">Umowa</th>
                                            <th class="text-right px-3 py-2">Uczestnicy</th>
                                            <th class="text-right px-3 py-2">Plan</th>
                                            <th class="text-right px-3 py-2">Wpłacono</th>
                                            <th class="text-right px-3 py-2">Brakuje</th>
                                            <th class="text-left px-3 py-2">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @foreach($contractsSummary as $c)
                                            <tr>
                                                <td class="px-3 py-2 text-gray-800 dark:text-gray-100">{{ $c['contract_number'] ?? ('ID: '.$c['id']) }}</td>
                                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-200">{{ $c['participants_count'] ?? 0 }}</td>
                                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-200">{{ number_format($c['expected'] ?? 0, 2, ',', ' ') }} PLN</td>
                                                <td class="px-3 py-2 text-right text-green-700 dark:text-green-400">{{ number_format($c['paid'] ?? 0, 2, ',', ' ') }} PLN</td>
                                                <td class="px-3 py-2 text-right {{ ($c['missing'] ?? 0) > 0 ? 'text-red-600' : 'text-green-600' }}">{{ number_format($c['missing'] ?? 0, 2, ',', ' ') }} PLN</td>
                                                <td class="px-3 py-2">
                                                    @if(!empty($c['is_fully_paid']))
                                                        <span class="text-xs font-semibold px-2 py-1 rounded bg-green-100 text-green-800">OK</span>
                                                    @else
                                                        <span class="text-xs font-semibold px-2 py-1 rounded bg-red-100 text-red-800">DO ZAPŁATY</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
                @endif
                {{-- Koniec ukrytej sekcji alokacji --}}

                    <div class="mt-6">
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-2">Harmonogram rat (umowy)</div>

                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                                <div class="text-xs text-gray-700 dark:text-gray-300">Przeterminowane raty (PLN)</div>
                                <div class="mt-1 text-lg font-bold {{ ($overdueInstallmentsAmount ?? 0) > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ number_format($overdueInstallmentsAmount ?? 0, 2, ',', ' ') }} PLN
                                </div>
                                <div class="text-xs text-gray-700 dark:text-gray-300">(termin < dziś, nieopłacone)</div>
                            </div>

                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3">
                                <div class="text-xs text-gray-700 dark:text-gray-300">Raty do 14 dni (PLN)</div>
                                <div class="mt-1 text-lg font-bold {{ ($dueSoonInstallmentsAmount ?? 0) > 0 ? 'text-orange-600' : 'text-green-600' }}">
                                    {{ number_format($dueSoonInstallmentsAmount ?? 0, 2, ',', ' ') }} PLN
                                </div>
                                <div class="text-xs text-gray-700 dark:text-gray-300">(termin w ciągu 14 dni, nieopłacone)</div>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <a
                                class="inline-flex items-center px-3 py-2 rounded-md text-sm font-semibold bg-red-600 text-white hover:bg-red-700"
                                href="{{ \App\Filament\Pages\Finance\InstallmentControl::getUrl(['scope' => 'overdue', 'days' => 14, 'event_code' => $record->public_code]) }}"
                                target="_blank"
                                rel="noreferrer"
                            >
                                Kontrola rat (przeterminowane)
                            </a>
                            <a
                                class="inline-flex items-center px-3 py-2 rounded-md text-sm font-semibold bg-orange-600 text-white hover:bg-orange-700"
                                href="{{ \App\Filament\Pages\Finance\InstallmentControl::getUrl(['scope' => 'soon', 'days' => 14, 'event_code' => $record->public_code]) }}"
                                target="_blank"
                                rel="noreferrer"
                            >
                                Kontrola rat (do 14 dni)
                            </a>
                        </div>

                        @if(!empty($overdueInstallmentsList))
                            <div class="mt-3 overflow-x-auto border rounded-lg bg-white dark:bg-gray-900">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                        <tr>
                                            <th class="text-left px-3 py-2">Umowa</th>
                                            <th class="text-left px-3 py-2">Termin</th>
                                            <th class="text-right px-3 py-2">Kwota</th>
                                            <th class="text-left px-3 py-2">Link</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @foreach($overdueInstallmentsList as $i)
                                            <tr>
                                                <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ $i['contract_number'] ?? '-' }}</td>
                                                <td class="px-3 py-2 text-gray-800 dark:text-gray-200">{{ optional($i['due_date'])->format('Y-m-d') }}</td>
                                                <td class="px-3 py-2 text-right text-red-600 font-semibold">{{ number_format($i['amount'] ?? 0, 2, ',', ' ') }} PLN</td>
                                                <td class="px-3 py-2">
                                                    @if(!empty($i['url']))
                                                        <a class="text-primary-600 hover:underline" href="{{ $i['url'] }}" target="_blank" rel="noreferrer">Umowa</a>
                                                    @else
                                                        <span class="text-gray-600">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </x-filament::card>
    @else
        <div></div>
    @endif
</x-filament::widget>
