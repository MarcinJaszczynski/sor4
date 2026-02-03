<div x-data="{ editRes: false, editCon: false }" class="text-sm text-black leading-5">
    @php
        $res = $point->latestReservation;
        $contractor = $point->assignedContractor;
        $dueDate = $res?->due_date ? \Illuminate\Support\Carbon::parse($res->due_date)->format('d.m.Y') : null;
        $priceDisplay = $manager->getPricingTotalDisplay($point);
        $priceTooltip = $manager->getPricingTooltip($point);
    @endphp

    {{-- Cena i koszty --}}
    @if($priceDisplay !== '—')
        <div class="mb-2">
            <span class="text-xs font-semibold text-black uppercase tracking-wider">Cena:</span>
            <span class="text-sm font-bold text-black border-b border-dotted border-black cursor-help" 
                  x-data
                  x-tooltip="{
                      content: @js($priceTooltip),
                      theme: 'light'
                  }">
                {{ $priceDisplay }}
            </span>
        </div>
    @endif
    
    <div class="space-y-0.5">
        {{-- Rezerwacja --}}
        <div>
            rezerwacja: <span class="text-black">{{ $res ? 'tak' : 'nie' }}</span>
        </div>

        {{-- Kontrahent --}}
        <div>
            kontrahent: <span class="text-black">{{ $contractor?->name ?? 'brak' }}</span>
        </div>

        @if($res)
            {{-- Zaliczka --}}
            <div>
                zaliczka: <span class="text-black">
                    @if(($res->advance_payment ?? 0) > 0)
                        tak - {{ number_format($res->advance_payment, 0, ',', ' ') }} PLN{{ $dueDate ? ' ' . $dueDate : '' }}
                    @else
                        nie
                    @endif
                </span>
            </div>

            {{-- Zapłacono --}}
            <div>
                zapłacono: <span class="text-black">{{ $res->status === 'paid' ? 'tak' : 'nie' }}</span>
                @if(($res->cost ?? 0) > 0 && $res->status !== 'paid')
                    <span class="text-black font-semibold">(do zapłaty: {{ number_format($res->cost, 0, ',', ' ') }} PLN{{ $dueDate ? ' do ' . $dueDate : '' }})</span>
                @endif
            </div>
        @endif

        @if($point->pilot_pays)
            @php
                $pilotCurrency = $point->pilot_payment_currency ?? 'PLN';
                $pilotNeeded = $point->pilot_payment_needed;
                $pilotGiven = $point->pilot_payment_given;
            @endphp
            <div>
                pilot opłaca: <span class="text-black">tak</span>
            </div>
            <div>
                potrzebuje: <span class="text-black">{{ $pilotNeeded !== null ? number_format($pilotNeeded, 2, ',', ' ') . ' ' . $pilotCurrency : '—' }}</span>
            </div>
            <div>
                otrzymał: <span class="text-black">{{ $pilotGiven !== null ? number_format($pilotGiven, 2, ',', ' ') . ' ' . $pilotCurrency : '—' }}</span>
            </div>
        @endif
    </div>

    {{-- Formularz kontrahenta --}}
    <div x-show="editCon" x-cloak style="display:none" class="mt-2 bg-gray-50 dark:bg-gray-800 p-2 rounded text-xs">
        <div class="font-semibold mb-1">Zmień kontrahenta:</div>
        <div class="flex gap-2">
            <select id="contractor-{{ $point->id }}" name="contractor-{{ $point->id }}" wire:model.defer="selectedContractors.{{ $point->id }}" class="flex-1 text-xs rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600">
                <option value="">-- Wybierz --</option>
                @foreach($contractors as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
            <button wire:click="saveContractorAssignment({{ $point->id }})" @click.wait="setTimeout(() => editCon = false, 100)" class="bg-teal-600 text-white px-3 py-1 rounded font-semibold hover:bg-teal-700">OK</button>
        </div>
    </div>

    {{-- Formularz rezerwacji --}}
    <div x-show="editRes" x-cloak style="display:none" class="mt-2 bg-gray-50 dark:bg-gray-800 p-2 rounded text-xs">
        <div class="font-semibold mb-2">Edycja rezerwacji:</div>
        <div class="space-y-2">
            <div>
                <label for="res-status-{{ $point->id }}" class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Status</label>
                <select id="res-status-{{ $point->id }}" name="res-status-{{ $point->id }}" wire:model.defer="reservationForm.{{ $point->id }}.status" class="w-full rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600">
                    <option value="pending">Oczekuje</option>
                    <option value="confirmed">Potwierdzona</option>
                    <option value="paid">Opłacona</option>
                    <option value="cancelled">Anulowana</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label for="res-cost-{{ $point->id }}" class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Koszt (PLN)</label>
                    <input id="res-cost-{{ $point->id }}" name="res-cost-{{ $point->id }}" type="number" step="0.01" wire:model.defer="reservationForm.{{ $point->id }}.cost" class="w-full rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600">
                </div>
                <div>
                    <label for="res-advance-{{ $point->id }}" class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Zaliczka (PLN)</label>
                    <input id="res-advance-{{ $point->id }}" name="res-advance-{{ $point->id }}" type="number" step="0.01" wire:model.defer="reservationForm.{{ $point->id }}.advance_payment" class="w-full rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600">
                </div>
            </div>
            <div>
                <label for="res-due-date-{{ $point->id }}" class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Termin / Data płatności</label>
                <input id="res-due-date-{{ $point->id }}" name="res-due-date-{{ $point->id }}" type="date" wire:model.defer="reservationForm.{{ $point->id }}.due_date" class="w-full rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600">
            </div>
            <div>
                <label for="res-contractor-{{ $point->id }}" class="block text-[10px] font-semibold text-gray-500 uppercase mb-1">Kontrahent (w rezerwacji)</label>
                <select id="res-contractor-{{ $point->id }}" name="res-contractor-{{ $point->id }}" wire:model.defer="reservationForm.{{ $point->id }}.contractor_id" class="w-full rounded border-gray-300 dark:bg-gray-700 dark:border-gray-600">
                    <option value="">-- Wybierz --</option>
                    @foreach($contractors as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-1">
                <button type="button" @click="editRes = false" class="text-gray-600 hover:text-gray-800 dark:text-gray-400">Anuluj</button>
                <button type="button" wire:click="saveReservationForPoint({{ $point->id }})" @click.wait="setTimeout(() => editRes = false, 100)" class="text-primary-600 font-semibold hover:text-primary-700">Zapisz</button>
            </div>
        </div>
    </div>
</div>
