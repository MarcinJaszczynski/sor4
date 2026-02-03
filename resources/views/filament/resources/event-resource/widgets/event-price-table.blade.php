<x-filament-widgets::widget>
<div class="overflow-x-auto">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-bold">Kosztorys imprezy</h3>
        <x-filament::button wire:click="refreshCalculations" color="primary" size="sm">
            Odśwież kalkulacje
        </x-filament::button>
    </div>

    @if($record)
        <!-- Informacje o imprezie -->
        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <h4 class="text-md font-semibold mb-3">Informacje o imprezie</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="font-medium text-gray-600 dark:text-gray-400">Nazwa:</span>
                    <p class="font-semibold">{{ $calculations['event_data']['name'] ?? 'Brak' }}</p>
                </div>
                <div>
                    <span class="font-medium text-gray-600 dark:text-gray-400">Klient:</span>
                    <p>{{ $calculations['event_data']['client_name'] ?? 'Brak' }}</p>
                </div>
                <div>
                    <span class="font-medium text-gray-600 dark:text-gray-400">Liczba uczestników:</span>
                    <p class="font-semibold">{{ $calculations['event_data']['participant_count'] ?? 0 }}</p>
                </div>
                <div>
                    <span class="font-medium text-gray-600 dark:text-gray-400">Status:</span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                        @if($calculations['event_data']['status'] === 'draft') bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200
                        @elseif($calculations['event_data']['status'] === 'confirmed') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                        @elseif($calculations['event_data']['status'] === 'in_progress') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
                        @elseif($calculations['event_data']['status'] === 'completed') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300
                        @elseif($calculations['event_data']['status'] === 'cancelled') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300
                        @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 @endif">
                        {{ match($calculations['event_data']['status'] ?? '') {
                            'draft' => 'Szkic',
                            'confirmed' => 'Potwierdzona',
                            'in_progress' => 'W trakcie',
                            'completed' => 'Zakończona',
                            'cancelled' => 'Anulowana',
                            default => ucfirst($calculations['event_data']['status'] ?? 'Nieznany')
                        } }}
                    </span>
                </div>
                <div>
                    <span class="font-medium text-gray-600 dark:text-gray-400">Data rozpoczęcia:</span>
                    <p>{{ $calculations['event_data']['start_date'] ? \Carbon\Carbon::parse($calculations['event_data']['start_date'])->format('d.m.Y') : 'Brak' }}</p>
                </div>
                <div>
                    <span class="font-medium text-gray-600 dark:text-gray-400">Data zakończenia:</span>
                    <p>{{ $calculations['event_data']['end_date'] ? \Carbon\Carbon::parse($calculations['event_data']['end_date'])->format('d.m.Y') : 'Jednodniowa' }}</p>
                </div>
                <div>
                    <span class="font-medium text-gray-600 dark:text-gray-400">Szablon:</span>
                    <p>{{ $calculations['event_data']['template_name'] ?? 'Brak' }}</p>
                </div>
                <div>
                    <span class="font-medium text-gray-600 dark:text-gray-400">Liczba dni:</span>
                    <p class="font-semibold">{{ $calculations['days_count'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Podsumowanie kosztów -->
        <div class="mb-6">
            <h4 class="text-md font-semibold mb-3">Podsumowanie kosztów</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Koszt programu</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($calculations['total_program_cost'] ?? 0, 2) }} PLN</p>
                </div>
                <div class="text-center p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Koszt transportu</p>
                    <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ number_format($transportCost ?? 0, 2) }} PLN</p>
                </div>
                <div class="text-center p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Koszt zakwaterowania</p>
                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($calculations['accommodation_cost'] ?? 0, 2) }} PLN</p>
                </div>
                <div class="text-center p-4 bg-teal-50 dark:bg-teal-900/20 rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Narzut</p>
                    <p class="text-2xl font-bold text-teal-600 dark:text-teal-400">{{ number_format($calculations['markup_amount'] ?? 0, 2) }} PLN</p>
                </div>
                <div class="text-center p-4 bg-cyan-50 dark:bg-cyan-900/20 rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Podatek</p>
                    <p class="text-2xl font-bold text-cyan-600 dark:text-cyan-400">{{ number_format($calculations['tax_amount'] ?? 0, 2) }} PLN</p>
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
                <div class="text-center p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Punkty w kalkulacji</p>
                    <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $calculations['calculation_points'] ?? 0 }}/{{ $calculations['total_points'] ?? 0 }}</p>
                </div>
            </div>

            @if(!empty($calculations['currencies']))
                <div class="mt-4 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
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
        </div>

        <!-- Szczegóły transportu -->
        @if($record->bus)
            <div class="mb-6 p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                <h4 class="text-md font-semibold mb-3">Szczegóły transportu</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-gray-600 dark:text-gray-400">Autokar:</span>
                        <p>{{ $calculations['event_data']['bus_name'] ?? 'Brak' }}</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600 dark:text-gray-400">Transfer (km):</span>
                        <p>{{ $calculations['event_data']['transfer_km'] ?? 0 }} km</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600 dark:text-gray-400">Program (km):</span>
                        <p>{{ $calculations['event_data']['program_km'] ?? 0 }} km</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-600 dark:text-gray-400">Koszt transportu:</span>
                        <p class="font-semibold">{{ number_format($transportCost ?? 0, 2) }} PLN</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Kalkulacje dla różnych wariantów uczestników -->
        @if(count($detailedCalculations) > 0)
            <div class="mb-6">
                <h4 class="text-md font-semibold mb-3">Kalkulacje dla różnych wariantów uczestników</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 mb-6">
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
                                <tr class="@if($calc['qty'] == $calculations['event_data']['participant_count']) bg-blue-50 dark:bg-blue-900/20 font-semibold @endif">
                                    <td class="px-4 py-3 border-b">{{ $calc['name'] }}</td>
                                    <td class="px-4 py-3 border-b text-right">{{ number_format($calc['program_cost'], 2) }} PLN</td>
                                    <td class="px-4 py-3 border-b text-right">{{ number_format($calc['transport_cost'], 2) }} PLN</td>
                                    <td class="px-4 py-3 border-b text-right">{{ number_format($calc['accommodation_cost'] ?? 0, 2) }} PLN</td>
                                    <td class="px-4 py-3 border-b text-right">{{ number_format($calc['markup_amount'] ?? 0, 2) }} PLN</td>
                                    <td class="px-4 py-3 border-b text-right">{{ number_format($calc['tax_amount'] ?? 0, 2) }} PLN</td>
                                    <td class="px-4 py-3 border-b text-right font-bold">{{ number_format($calc['total_cost'], 2) }} PLN</td>
                                    <td class="px-4 py-3 border-b text-right font-bold">{{ number_format($calc['cost_per_person'], 2) }} PLN</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                
                    @foreach($detailedCalculations as $calc)
                        <div class="mb-6 border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-800">
                            <h5 class="font-bold text-gray-800 dark:text-gray-200 mb-3 text-lg">
                                Wariant: {{ $calc['qty'] }} uczestników (plus {{ $record->gratis_count ?? 0 }} gratis, {{ $record->pilot_count ?? 0 }} obsługa, {{ $record->bus_drivers_count ?? 0 }} kierowców), razem: {{ $calc['qty'] + ($record->gratis_count ?? 0) + ($record->pilot_count ?? 0) + ($record->bus_drivers_count ?? 0) }} osób
                            </h5>

                            <div class="mb-4">
                                <h6 class="font-medium text-green-700 dark:text-green-400 mb-2">Noclegi:</h6>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                   Koszt: {{ number_format($calc['accommodation_cost'] ?? 0, 2) }} PLN
                                </p>
                            </div>

                            <div class="mb-4">
                                <h6 class="font-medium text-blue-600 dark:text-blue-400 mb-2">Waluta: PLN (PLN)</h6>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-sm">
                                        <thead>
                                            <tr class="bg-gray-100 dark:bg-gray-800">
                                                <th class="px-3 py-2 border-b text-left">Punkt programu</th>
                                                <th class="px-3 py-2 border-b text-right">Cena jedn. (za grupę)</th>
                                                <th class="px-3 py-2 border-b text-right">dla grupy</th>
                                                <th class="px-3 py-2 border-b text-right">Razem</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $totalProgramCost = 0;
                                            @endphp

                                            {{-- Dynamiczne punkty programu --}}
                                            @if(!empty($calc['program_points_breakdown']))
                                                @foreach($calc['program_points_breakdown'] as $point)
                                                    <tr class="">
                                                        <td class="px-3 py-2 border-b font-medium">
                                                            {{ $point['name'] }}
                                                        </td>
                                                        <td class="px-3 py-2 border-b text-right">
                                                            {{ number_format($point['unit_price'], 2) }} {{ $point['currency'] }}
                                                        </td>
                                                        <td class="px-3 py-2 border-b text-right">
                                                            @if($point['count_basis'] === 'per_group')
                                                                {{ ceil($point['count_value'] / ($point['quantity'] > 0 ? $point['quantity'] : 1)) }} grup
                                                            @else
                                                                {{ $point['count_value'] }} osób
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-2 border-b text-right font-semibold">
                                                            {{ number_format($point['total_cost_pln'], 2) }} PLN
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif

                                            {{-- Ubezpieczenie --}}
                                            @if(($calc['insurance_cost'] ?? 0) > 0)
                                                <tr class="">
                                                    <td class="px-3 py-2 border-b font-medium">
                                                        Ubezpieczenie (NNW)
                                                    </td>
                                                    <td class="px-3 py-2 border-b text-right">
                                                        -
                                                    </td>
                                                    <td class="px-3 py-2 border-b text-right">
                                                         {{ $calc['qty'] + ($record->gratis_count ?? 0) + ($record->pilot_count ?? 0) + ($record->bus_drivers_count ?? 0) }} osób
                                                    </td>
                                                    <td class="px-3 py-2 border-b text-right font-semibold">
                                                        {{ number_format($calc['insurance_cost'], 2) }} PLN
                                                    </td>
                                                </tr>
                                            @endif

                                            <tr>
                                                <td class="px-3 py-2 border-b font-medium">Koszt transportu (autokar)</td>
                                                <td class="px-3 py-2 border-b text-right">-</td>
                                                <td class="px-3 py-2 border-b text-right">-</td>
                                                <td class="px-3 py-2 border-b text-right font-semibold">{{ number_format($calc['transport_cost'], 2) }} PLN</td>
                                            </tr>

                                            {{-- Podsumowania --}}
                                            <tr class="bg-gray-100 dark:bg-gray-700 font-bold">
                                                <td class="px-3 py-2 border-t-2 border-gray-400" colspan="3">SUMA dla PLN (bez narzutu):</td>
                                                <td class="px-3 py-2 border-t-2 border-gray-400 text-right">
                                                    {{ number_format(($calc['program_cost'] + $calc['transport_cost'] + ($calc['insurance_cost'] ?? 0)), 2) }} PLN
                                                </td>
                                            </tr>
                                            
                                            <tr class="bg-yellow-100 dark:bg-yellow-900/30 font-semibold">
                                                <td class="px-3 py-2" colspan="3">
                                                    Narzut ({{ $record->markup?->value ?? 0 }}%):
                                                </td>
                                                <td class="px-3 py-2 text-right">
                                                    {{ number_format($calc['markup_amount'] ?? 0, 2) }} PLN
                                                </td>
                                            </tr>

                                            <tr class="bg-orange-50 dark:bg-orange-900/20 font-medium">
                                                <td class="px-3 py-2" colspan="3">
                                                    Podatki (VAT marża/inne):
                                                </td>
                                                <td class="px-3 py-2 text-right">
                                                     {{ number_format($calc['tax_amount'] ?? 0, 2) }} PLN
                                                </td>
                                            </tr>

                                            <tr class="bg-orange-100 dark:bg-orange-900/40 font-semibold">
                                                <td class="px-3 py-2" colspan="3">Suma podatków:</td>
                                                <td class="px-3 py-2 text-right">
                                                    {{ number_format($calc['tax_amount'] ?? 0, 2) }} PLN
                                                </td>
                                            </tr>

                                            <tr class="bg-green-100 dark:bg-green-900/40 font-bold">
                                                <td class="px-3 py-2" colspan="3">SUMA KOŃCOWA dla PLN:</td>
                                                <td class="px-3 py-2 text-right">
                                                    {{ number_format($calc['total_cost'], 2) }} PLN
                                                </td>
                                            </tr>
                                            <tr class="bg-blue-100 dark:bg-blue-900/40 font-bold">
                                                <td class="px-3 py-2" colspan="3">Cena za osobę (uczestnik):</td>
                                                <td class="px-3 py-2 text-right">
                                                    {{ number_format($calc['cost_per_person'], 2) }} PLN
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                    * Wiersz podświetlony na niebiesko odpowiada bieżącej liczbie uczestników ({{ $calculations['event_data']['participant_count'] ?? 0 }})
                </p>
            </div>
        @endif

        <!-- Koszty według dni (usunięte zgodnie z prośbą) -->

        <!-- Tabela wszystkich punktów programu (usunięta zgodnie z prośbą) -->
        
    <!-- Inline price edit modal (Livewire bound) -->
    <div x-data="{ editing: @entangle('editingPrice') }"
         x-cloak
         x-show="editing !== null"
         class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black opacity-50" @click="editing = null"></div>
        <div class="bg-white dark:bg-gray-900 rounded-lg p-6 z-50 w-full max-w-2xl">
                <h3 class="text-lg font-semibold mb-4">Edytuj cenę</h3>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="text-sm text-gray-600">Cena za osobę (PLN)</label>
                        <input type="number" step="0.01" wire:model.defer="editingPrice.price_per_person" class="w-full mt-1 rounded border-gray-200 dark:border-gray-700">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Koszt transportu (PLN)</label>
                        <input type="number" step="0.01" wire:model.defer="editingPrice.transport_cost" class="w-full mt-1 rounded border-gray-200 dark:border-gray-700">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Cena z podatkiem (PLN)</label>
                        <input type="number" step="0.01" wire:model.defer="editingPrice.price_with_tax" class="w-full mt-1 rounded border-gray-200 dark:border-gray-700">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Rozbicie VAT (JSON)</label>
                        <input type="text" wire:model.defer="editingPrice.tax_breakdown" placeholder='{"vat": 23, "amount": 100}' class="w-full mt-1 rounded border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-500 mt-1">Podaj JSON z rozbiciem VAT lub zostaw puste.</p>
                    </div>
                </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <x-filament::button color="secondary" size="sm" @click="editing = null">Anuluj</x-filament::button>
                        <x-filament::button color="primary" size="sm" wire:click.prevent="saveEditingPrice" x-bind:disabled="editing === null">Zapisz</x-filament::button>
                    </div>
            </div>
        </div>
    @else
        <div class="text-center py-8">
            <p class="text-gray-600 dark:text-gray-400">Brak danych do wyświetlenia</p>
        </div>
    @endif
</div>
</x-filament-widgets::widget>
