<div class="space-y-6 mt-6">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold">Warianty kalkulacji</h3>
        <button wire:click="calculate" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
            Przelicz teraz
        </button>
    </div>

    <!-- Variants Input Table -->
    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="min-w-full text-sm divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left font-medium text-gray-700">Wariant</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-700">Uczestnicy (płatni)</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-700">Gratis</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-700">Obsługa</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-700">Kierowcy</th>
                    <th class="px-3 py-2 text-right font-medium text-gray-700">Akcje</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach($variants as $index => $variant)
                    <tr>
                        <td class="px-3 py-2 text-gray-500">#{{ $index + 1 }}</td>
                        <td class="px-3 py-2">
                            <input type="number" wire:model="variants.{{ $index }}.participant_count" wire:change="calculate" class="w-20 rounded border-gray-300 text-sm">
                        </td>
                        <td class="px-3 py-2">
                             <input type="number" wire:model="variants.{{ $index }}.gratis_count" wire:change="calculate" class="w-16 rounded border-gray-300 text-sm">
                        </td>
                        <td class="px-3 py-2">
                             <input type="number" wire:model="variants.{{ $index }}.staff_count" wire:change="calculate" class="w-16 rounded border-gray-300 text-sm">
                        </td>
                        <td class="px-3 py-2">
                             <input type="number" wire:model="variants.{{ $index }}.driver_count" wire:change="calculate" class="w-16 rounded border-gray-300 text-sm">
                        </td>
                        <td class="px-3 py-2 text-right">
                            <button wire:click="removeVariant({{ $index }})" class="text-red-600 hover:text-red-800 text-xs">Usuń</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-2 bg-gray-50 border-t border-gray-200">
            <button wire:click="addVariant" class="text-sm text-blue-600 font-medium hover:underline">+ Dodaj kolejny wariant</button>
        </div>
    </div>

    <!-- Results -->
    <div class="space-y-8">
        <!-- Explanations Box -->
        <div class="p-3 bg-blue-50 border border-blue-200 rounded text-xs text-blue-900">
            <h4 class="font-semibold mb-2">Szczegółowa kalkulacja kosztów</h4>
            <b>Wyjaśnienie:</b> Koszt całkowity liczony jest dla sumy: <b>uczestnicy + gratis + obsługa + kierowcy</b>.<br>
            <b>Cena za osobę</b> to koszt całkowity podzielony przez liczbę uczestników (bez gratis, obsługi i kierowców).<br>
            <b>Wielkość grupy</b> oznacza ile osób przypada na jedną jednostkę ceny punktu programu.
        </div>

        @foreach($calculationResults as $index => $result)
            @php 
                $variant = $variants[$index] ?? [];
                $totalPeople = ($variant['participant_count'] ?? 0) + ($variant['gratis_count'] ?? 0) + ($variant['staff_count'] ?? 0) + ($variant['driver_count'] ?? 0);
            @endphp
            
            <div class="border border-gray-200 rounded-lg p-5 bg-white shadow-sm">
                <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3">
                    <h4 class="font-semibold text-lg text-gray-800">
                        Wariant: {{ $variant['participant_count'] ?? 0 }} uczestników
                        <span class="text-sm font-normal text-gray-500 ml-2">
                            (plus {{ $variant['gratis_count'] ?? 0 }} gratis, {{ $variant['staff_count'] ?? 0 }} obsługa, {{ $variant['driver_count'] ?? 1 }} kierowców),
                            razem: {{ $result['total_count_for_costs'] }} osób
                        </span>
                    </h4>
                </div>

                {{-- Noclegi / Struktura hoteli --}}
                 @if(!empty($result['hotel_structure'] ?? []))
                    <div class="mb-6">
                        <h6 class="font-bold text-gray-700 mb-2">Noclegi:</h6>
                        @foreach($result['hotel_structure'] as $hotelDay)
                            <div class="mb-4">
                                <div class="font-medium text-sm mb-1 bg-gray-100 px-2 py-1 rounded">Nocleg {{ $hotelDay['day'] }}:</div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-xs text-left border">
                                        <thead>
                                            <tr class="bg-gray-50 text-gray-600">
                                                <th class="px-2 py-1 border">Pokój</th>
                                                <th class="px-2 py-1 border text-right">Uczestnicy</th>
                                                <th class="px-2 py-1 border text-right">Gratis</th>
                                                <th class="px-2 py-1 border text-right">Obsługa</th>
                                                <th class="px-2 py-1 border text-right">Kierowcy</th>
                                                <th class="px-2 py-1 border text-right">Cena (za pokój)</th>
                                                <th class="px-2 py-1 border text-right">Łącznie</th>
                                                <th class="px-2 py-1 border text-right">Waluta</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $daySum = 0; @endphp
                                            @foreach($hotelDay['rooms'] ?? [] as $r)
                                                @php $daySum += $r['total']; @endphp
                                                <tr>
                                                    <td class="px-2 py-1 border">{{ $r['name'] }}</td>
                                                    <td class="px-2 py-1 border text-right">{{ $r['qty'] }}</td>
                                                    <td class="px-2 py-1 border text-right">{{ $r['gratis'] }}</td>
                                                    <td class="px-2 py-1 border text-right">{{ $r['staff'] }}</td>
                                                    <td class="px-2 py-1 border text-right">{{ $r['driver'] }}</td>
                                                    <td class="px-2 py-1 border text-right">{{ number_format($r['price'], 2) }}</td>
                                                    <td class="px-2 py-1 border text-right">{{ number_format($r['total'], 2) }}</td>
                                                    <td class="px-2 py-1 border text-right">{{ $r['currency'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div class="text-right text-xs mt-1 font-bold">
                                        Suma za nocleg: {{ number_format($daySum, 2) }} PLN
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif


                <!-- Detailed Breakdown Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-3 py-2">Punkt programu</th>
                                <th class="px-3 py-2 text-right">Cena jednostkowa (za grupę)</th>
                                <th class="px-3 py-2 text-right">dla grupy</th>
                                <th class="px-3 py-2 text-right">Koszt całkowity (dla wszystkich)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            {{-- PUNTY PROGRAMU --}}
                            @foreach($result['program_points_breakdown'] ?? [] as $pp)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-1">
                                        @if($pp['is_child'] ?? false) <span class="text-gray-400 mr-1">→</span> @endif
                                        {{ $pp['name'] }}
                                        @if(($pp['original_currency'] ?? 'PLN') !== 'PLN' && ($pp['convert_to_pln'] ?? false))
                                            <div class="text-[10px] text-gray-400">Kurs: {{ number_format($pp['exchange_rate'] ?? 1, 4) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-1 text-right whitespace-nowrap">
                                        {{ number_format($pp['unit_price'], 2) }} {{ $pp['original_currency'] }}
                                    </td>
                                    <td class="px-3 py-1 text-right text-xs text-gray-500">
                                        {{ $pp['count_value'] ?? $pp['participants'] ?? '?' }} osób
                                    </td>
                                    <td class="px-3 py-1 text-right font-medium">
                                        @if(($pp['convert_to_pln'] ?? false) || ($pp['original_currency'] ?? 'PLN') === 'PLN')
                                            {{ number_format($pp['total_cost_pln'], 2) }} PLN
                                        @else
                                            <span class="text-orange-600">{{ number_format($pp['total_cost_original'], 2) }} {{ $pp['original_currency'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            {{-- UBEZPIECZENIE --}}
                            @if(!empty($result['insurance_breakdown']))
                                @foreach($result['insurance_breakdown'] as $ins)
                                <tr class="bg-blue-50/30">
                                    <td class="px-3 py-1 font-medium">Ubezpieczenie: {{ $ins['name'] }}</td>
                                    <td class="px-3 py-1 text-right">-</td>
                                    <td class="px-3 py-1 text-right text-xs text-gray-500">{{ $result['participant_count'] }} osób</td>
                                    <td class="px-3 py-1 text-right font-medium">{{ number_format($ins['total'], 2) }} PLN</td>
                                </tr>
                                @endforeach
                            @elseif(($result['insurance_cost'] ?? 0) > 0)
                                <tr class="bg-blue-50/30">
                                    <td class="px-3 py-1 font-medium">Ubezpieczenie (Suma)</td>
                                    <td class="px-3 py-1 text-right">-</td>
                                    <td class="px-3 py-1 text-right text-xs text-gray-500">{{ $result['participant_count'] }} osób</td>
                                    <td class="px-3 py-1 text-right font-medium">{{ number_format($result['insurance_cost'], 2) }} PLN</td>
                                </tr>
                            @endif

                            {{-- NOCLEGI SUMARYCZNIE JEŚLI NIE MA DETALI --}}
                             @if(($result['accommodation_cost'] ?? 0) > 0)
                            <tr class="bg-green-50/30">
                                <td class="px-3 py-1 font-medium">Noclegi (Suma)</td>
                                <td class="px-3 py-1 text-right">-</td>
                                <td class="px-3 py-1 text-right text-xs text-gray-500">osób</td>
                                <td class="px-3 py-1 text-right font-medium">{{ number_format($result['accommodation_cost'], 2) }} PLN</td>
                            </tr>
                            @endif

                            {{-- TRANSPORT --}}
                             @if(($result['transport_cost'] ?? 0) > 0)
                            <tr class="bg-yellow-50/30">
                                <td class="px-3 py-1 font-medium">Koszt transportu (autokar)</td>
                                <td class="px-3 py-1 text-right">-</td>
                                <td class="px-3 py-1 text-right text-xs text-gray-500">osób</td>
                                <td class="px-3 py-1 text-right font-medium">{{ number_format($result['transport_cost'], 2) }} PLN</td>
                            </tr>
                            @endif
                            
                            {{-- SUMY --}}
                            <tr class="border-t-2 border-gray-200">
                                <td class="px-3 py-2 font-bold text-gray-700">SUMA Część PLN (bez narzutu):</td>
                                <td colspan="2"></td>
                                <td class="px-3 py-2 text-right font-bold">{{ number_format($result['program_cost'] + $result['accommodation_cost'] + $result['transport_cost'] + $result['insurance_cost'], 2) }} PLN</td>
                            </tr>

                            @foreach($result['currencies'] ?? [] as $code => $amount)
                                @if($amount > 0)
                                <tr>
                                    <td class="px-3 py-2 font-bold text-orange-700">SUMA Część {{ $code }} (bez narzutu):</td>
                                    <td colspan="2"></td>
                                    <td class="px-3 py-2 text-right font-bold text-orange-700">{{ number_format($amount, 2) }} {{ $code }}</td>
                                </tr>
                                @endif
                            @endforeach
                            
                            <tr>
                                <td class="px-3 py-1 text-gray-600">Narzut ({{ number_format($this->record->markup->percent ?? 20, 2) }}%):</td>
                                <td colspan="2"></td>
                                <td class="px-3 py-1 text-right">{{ number_format($result['markup_amount'], 2) }} PLN</td>
                            </tr>

                            <tr>
                                <td class="px-3 py-1 text-gray-600">Suma podatków:</td>
                                <td colspan="2"></td>
                                <td class="px-3 py-1 text-right">{{ number_format($result['tax_amount'], 2) }} PLN</td>
                            </tr>

                             <tr class="bg-gray-100 border-t border-gray-200">
                                <td class="px-3 py-2 font-bold text-lg">SUMA KOŃCOWA dla PLN:</td>
                                <td colspan="2"></td>
                                <td class="px-3 py-2 text-right font-bold text-lg text-blue-800">{{ number_format($result['total_cost'], 2) }} PLN</td>
                            </tr>
                             <tr class="bg-blue-600 text-white">
                                <td class="px-3 py-2 font-bold text-lg uppercase">Cena za osobę (uczestnik):</td>
                                <td colspan="2"></td>
                                <td class="px-3 py-2 text-right font-bold text-lg">{{ number_format($result['final_price_per_person'], 2) }} PLN</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
        
        <!-- Summary Table of All Variants -->
        <div class="mt-8 border border-gray-300 rounded overflow-hidden">
             <div class="bg-gray-100 px-4 py-2 font-bold border-b">Ceny zapisane w bazie danych (Podsumowanie)</div>
             <table class="w-full text-sm text-left">
                  <thead>
                      <tr class="bg-gray-50 border-b">
                          <th class="px-3 py-2">Ilość uczestników</th>
                          <th class="px-3 py-2">Waluta</th>
                          <th class="px-3 py-2 text-right">Cena bazowa</th>
                          <th class="px-3 py-2 text-right">Narzut</th>
                          <th class="px-3 py-2 text-right">Podatki</th>
                          <th class="px-3 py-2 text-right">Cena z podatkiem</th>
                          <th class="px-3 py-2 text-right">Cena za osobę</th>
                      </tr>
                  </thead>
                  <tbody>
                      @foreach($calculationResults as $index => $result)
                       <tr class="border-b hover:bg-gray-50 bg-blue-50/10">
                           <td class="px-3 py-2 font-medium">{{ $variants[$index]['participant_count'] }} osób</td>
                           <td class="px-3 py-2 font-bold">PLN</td>
                           <td class="px-3 py-2 text-right">{{ number_format($result['program_cost'] + $result['accommodation_cost'] + $result['transport_cost'] + $result['insurance_cost'], 2) }}</td>
                           <td class="px-3 py-2 text-right">{{ number_format($result['markup_amount'], 2) }}</td>
                           <td class="px-3 py-2 text-right">{{ number_format($result['tax_amount'], 2) }}</td>
                           <td class="px-3 py-2 text-right">{{ number_format($result['total_cost'], 2) }}</td>
                           <td class="px-3 py-2 text-right font-bold text-blue-700">{{ number_format($result['final_price_per_person'], 2) }}</td>
                       </tr>
                       
                       @foreach($result['currencies'] ?? [] as $code => $amount)
                           @if($amount > 0)
                           <tr class="border-b hover:bg-gray-50 text-orange-700 text-xs">
                               <td class="px-3 py-1 italic text-gray-400">↳ dla {{ $variants[$index]['participant_count'] }} os.</td>
                               <td class="px-3 py-1 font-bold">{{ $code }}</td>
                               <td class="px-3 py-1 text-right">{{ number_format($amount, 2) }}</td>
                               <td class="px-3 py-1 text-right">-</td>
                               <td class="px-3 py-1 text-right">-</td>
                               <td class="px-3 py-1 text-right font-bold">{{ number_format($amount, 2) }}</td>
                               <td class="px-3 py-1 text-right font-medium">{{ number_format($result['currencies_per_person'][$code] ?? 0, 2) }}</td>
                           </tr>
                           @endif
                       @endforeach
                      @endforeach
                  </tbody>
             </table>
        </div>
    </div>
</div>

