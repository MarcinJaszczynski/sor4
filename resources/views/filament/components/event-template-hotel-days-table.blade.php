@php
    // $page - Livewire instance (EditEvent)
    // $hotelRooms - list of rooms (id => name)
    $hotelDays = isset($page->hotel_days) ? $page->hotel_days : [];
    $allRoomsMap = \App\Models\HotelRoom::all(); 
    $hotelRooms = $allRoomsMap->pluck('name', 'id')->toArray();
    $record = isset($page->record) ? $page->record : null;
@endphp

<div class="space-y-6">
    @if(empty($hotelDays))
        <div class="p-6 text-center text-gray-500 bg-white border rounded">
            Brak zdefiniowanych noclegów (Duration: {{ $record->duration_days ?? 0 }}d).
        </div>
    @endif

    @foreach($hotelDays as $i => $day)
        @php
            $stats = method_exists($page, 'getDayStats') ? $page->getDayStats($i) : ['people' => 0, 'places' => 0, 'diff' => 0];
            $diffParams = ['count' => abs($stats['diff'])];
            $balanceColor = $stats['diff'] < 0 ? 'text-red-600' : ($stats['diff'] == 0 ? 'text-green-600' : 'text-blue-600');
            $balanceText = $stats['diff'] < 0 ? "Brakuje {$diffParams['count']}" : ($stats['diff'] > 0 ? "Nadmiar {$diffParams['count']}" : "Idealnie");
        @endphp

        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 relative" wire:key="day-card-{{ $i }}">
             <div class="flex justify-between items-start mb-6 border-b border-gray-100 pb-4">
                 <div>
                     <h3 class="font-bold text-xl text-gray-800">
                         Nocleg {{ $day['day'] }}
                     </h3>
                     @if(isset($record->start_date))
                        <div class="text-sm text-gray-500 mt-1">
                            {{ \Carbon\Carbon::parse($record->start_date)->addDays($day['day'] - 1)->format('d.m.Y') }}
                        </div>
                     @endif
                 </div>
                 <div class="text-right text-sm leading-6 bg-gray-50 p-3 rounded-lg border border-gray-100 min-w-[200px]">
                     <div class="flex justify-between">
                         <span class="text-gray-600">Uczestnicy (z obsł.):</span> 
                         <span class="font-semibold">{{ $stats['people'] }}</span>
                     </div>
                     <div class="flex justify-between">
                         <span class="text-gray-600">Miejsca w pokojach:</span> 
                         <span class="font-semibold">{{ $stats['places'] }}</span>
                     </div>
                     <div class="flex justify-between border-t border-gray-200 mt-2 pt-2">
                         <span class="text-gray-600 font-medium">Bilans:</span> 
                         <strong class="{{ $balanceColor }}">{{ $balanceText }}</strong>
                     </div>
                 </div>
             </div>

             {{-- List of Configured Rooms --}}
             <div class="mb-6">
                 @if(!empty($day['custom_config']))
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="w-full text-sm text-left divide-y divide-gray-200">
                            <thead class="bg-gray-50 text-gray-500 uppercase text-xs font-semibold">
                                <tr>
                                    <th class="px-4 py-3">Pokój</th>
                                    <th class="px-4 py-3">Pojemność</th>
                                    <th class="px-4 py-3 w-32">Ilość sztuk</th>
                                    <th class="px-4 py-3 w-40">Cena (1 szt)</th>
                                    <th class="px-4 py-3 text-right">Suma</th>
                                    <th class="px-4 py-3 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach($day['custom_config'] as $rid => $conf)
                                    @php
                                        $roomName = $conf['name'] ?? ($hotelRooms[$rid] ?? "Pokój #$rid");
                                        $qty = (int)$conf['quantity'];
                                        $price = (float)$conf['price'];
                                        $curr = $conf['currency'];
                                        $total = $qty * $price;
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $roomName }}</td>
                                        <td class="px-4 py-3 text-gray-600">
                                            {{ $conf['people_count'] }} os.
                                        </td>
                                        <td class="px-4 py-3">
                                             <div class="flex items-center">
                                                 <input type="number" min="0"
                                                        wire:model.live.debounce.500ms="hotel_days.{{ $i }}.custom_config.{{ $rid }}.quantity"
                                                        class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-center py-1">
                                             </div>
                                        </td>
                                        <td class="px-4 py-3">
                                             <div class="flex items-center gap-2">
                                                <input type="number" step="0.01"
                                                    wire:model.live.debounce.500ms="hotel_days.{{ $i }}.custom_config.{{ $rid }}.price"
                                                    class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-right py-1"> 
                                                 <span class="text-xs font-medium text-gray-500 code">{{ $curr }}</span>
                                             </div>
                                        </td>
                                        <td class="px-4 py-3 text-right font-bold text-gray-900">
                                            {{ number_format($total, 2, '.', ' ') }} <span class="text-xs font-normal text-gray-500">{{ $curr }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <button type="button" 
                                                    wire:confirm="Czy na pewno usunąć ten typ pokoju z tego dnia?"
                                                    wire:click="removeRoomFromDayConfig({{ $i }}, {{ $rid }})"
                                                    class="text-gray-400 hover:text-red-600 transition-colors p-1 rounded-full hover:bg-red-50"
                                                    title="Usuń">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 border-t border-gray-200">
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-right text-xs text-gray-500 font-medium uppercase">Razem nocleg:</td>
                                    <td class="px-4 py-2 text-right font-bold text-gray-900">
                                        @php
                                            $dayTotal = 0;
                                            $dayCurr = 'PLN'; 
                                            foreach($day['custom_config'] as $conf) {
                                                $dayTotal += ((int)$conf['quantity'] * (float)$conf['price']);
                                                $dayCurr = $conf['currency']; // Simplication: assumes mostly single currency
                                            }
                                        @endphp
                                        {{ number_format($dayTotal, 2, '.', ' ') }} <span class="text-xs font-normal text-gray-500">{{ $dayCurr }}</span>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                 @else
                    <div class="py-8 text-center bg-gray-50 rounded-lg border border-dashed border-gray-300 text-gray-500 text-sm">
                        Brak skonfigurowanych pokoi dla tego dnia. <br>
                        Użyj formularza poniżej, aby dodać pokoje.
                    </div>
                 @endif
             </div>

             {{-- Add Room Form --}}
             <div class="bg-blue-50/50 p-5 rounded-lg border border-blue-100">
                 <div class="text-xs font-bold text-blue-700 mb-3 uppercase tracking-wide flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Dodaj pokój
                 </div>
                 
                 <div class="grid grid-cols-12 gap-3 items-end">
                     <div class="col-span-12 md:col-span-4" 
                          x-data="{
                              search: '',
                              open: false,
                              selectedId: @entangle("hotel_days.{$i}.new_room.room_id").live,
                              get options() {
                                  return [
                                      @foreach($allRoomsMap as $r)
                                          { id: {{ $r->id }}, label: '{{ addslashes($r->name) }} ({{ $r->people_count }} os) - {{ $r->price }} {{ $r->currency }}' },
                                      @endforeach
                                  ];
                              },
                              get filtered() {
                                  if (this.search === '') return this.options;
                                  return this.options.filter(i => i.label.toLowerCase().includes(this.search.toLowerCase()));
                              },
                              select(opt) {
                                  this.selectedId = opt.id;
                                  this.search = opt.label;
                                  this.open = false;
                              },
                              init() {
                                  this.$watch('selectedId', value => {
                                      if (!value) this.search = '';
                                      // If value is set externally (e.g. reload), try to find label?
                                      // Optional enhancement
                                  });
                              }
                          }"
                     >
                         <label class="block text-[10px] text-gray-500 uppercase font-semibold mb-1">Typ pokoju (katalog)</label>
                         
                         <div class="relative">
                             <input type="text"
                                    x-model="search"
                                    @focus="open = true"
                                    @click.away="open = false"
                                    placeholder="Wpisz aby wyszukać pokój..."
                                    class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                             >
                             
                             <div x-show="open" 
                                  class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto"
                                  style="display: none;">
                                 <template x-for="opt in filtered">
                                     <div @click="select(opt)" 
                                          class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm text-gray-700"
                                          :class="{ 'bg-blue-50': opt.id == selectedId }"
                                          x-text="opt.label"></div>
                                 </template>
                                 <div x-show="filtered.length === 0" class="px-4 py-2 text-gray-500 text-sm">Brak wyników</div>
                             </div>
                         </div>
                     </div>
                     <div class="col-span-6 md:col-span-2">
                         <label class="block text-[10px] text-gray-500 uppercase font-semibold mb-1">Ilość (szt)</label>
                         <input type="number" wire:model="hotel_days.{{ $i }}.new_room.quantity" placeholder="1" class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                     </div>
                     <div class="col-span-6 md:col-span-2">
                         <label class="block text-[10px] text-gray-500 uppercase font-semibold mb-1">Pojemność (os)</label>
                         <input type="number" wire:model="hotel_days.{{ $i }}.new_room.people_count" placeholder="os" class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                     </div>
                     <div class="col-span-6 md:col-span-2">
                         <label class="block text-[10px] text-gray-500 uppercase font-semibold mb-1">Cena (za szt)</label>
                         <input type="number" step="0.01" wire:model="hotel_days.{{ $i }}.new_room.price" placeholder="cena" class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                     </div>
                     <div class="col-span-12 md:col-span-4">
                         <label class="block text-[10px] text-gray-500 uppercase font-semibold mb-1">Nowy pokój (ad-hoc, tylko dla tej imprezy)</label>
                         <input type="text" wire:model="hotel_days.{{ $i }}.new_room.ad_hoc_name" placeholder="Nazwa pokoju (np. Pokój rodzinny)" class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                     </div>
                     <div class="col-span-6 md:col-span-2">
                         <button type="button" 
                                 wire:click="addRoomToDay({{ $i }})"
                                 class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-indigo-900 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                             Dodaj
                         </button>
                     </div>
                 </div>
                 <div class="mt-2 text-[10px] text-gray-400 italic">
                     * Wybranie pokoju z listy uzupełni domyślne wartości. Możesz je zmienić przed dodaniem.
                 </div>
             </div>

             {{-- Footer Actions --}}
             <div class="mt-4 flex justify-between items-center text-sm">
                 <span class="text-gray-400 text-xs">ID noclegu: {{ $i }}</span>
                 @if(!$loop->last)
                     <div class="flex items-center gap-3">
                         <button type="button" wire:click="copyFromTemplateToDay({{ $i }})" class="inline-flex items-center text-indigo-700 hover:text-indigo-900 font-medium">
                             <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h6l3 6 4-8 5 12"></path></svg>
                             Skopiuj z szablonu
                         </button>
                         <button type="button"
                                 wire:click="copyToNextDay({{ $i }})" 
                                 class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                             <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                             Kopiuj konfigurację do następnego noclegu
                         </button>
                     </div>
                 @endif
             </div>
        </div>
    @endforeach

    <div class="mt-8 pt-4 border-t">
        <div class="flex gap-3 justify-end items-center bg-gray-50 p-3 rounded">
            <span class="text-sm font-bold text-gray-900 uppercase tracking-wide">Akcje globalne:</span>
            <button type="button" wire:click="saveHotelDays" class="px-4 py-2 bg-green-600 text-white font-bold rounded shadow hover:bg-green-700 active:bg-green-800">
                Zapisz cały plan noclegów
            </button>
             <button type="button" wire:click="forceRefreshHotelDays" class="text-gray-600 hover:text-black font-semibold text-sm underline decoration-gray-300">
                Odśwież
            </button>
        </div>
    </div>
</div>
