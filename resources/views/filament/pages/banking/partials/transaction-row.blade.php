<tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
    <td class="px-4 py-2">{{ $result['date'] }}</td>
    <td class="px-4 py-2">{{ $result['sender'] }}</td>
    <td class="px-4 py-2">
        <span class="text-sm text-gray-600">{{ strtoupper($result['confidence'] ?? 'none') }}</span>
        @if(!empty($result['score']))
            <div class="text-xs text-gray-500">score: {{ number_format($result['score'], 2) }}</div>
        @endif
    </td>
    <td class="px-4 py-2" title="{{ $result['title'] }}">{{ \Illuminate\Support\Str::limit($result['title'], 50) }}</td>
    <td class="px-4 py-2 font-bold {{ $result['amount'] > 0 ? 'text-green-600' : 'text-red-600' }}">
        {{ number_format($result['amount'], 2) }} PLN
    </td>
    <td class="px-4 py-2">
        @if($result['match_found'])
            <span class="text-custom-600 font-bold" style="color: var(--primary-600);">Umowa: {{ $result['contract_id'] }}</span>
        @else
            <span class="text-gray-500">Nie rozpoznano</span>
        @endif
    </td>
    <td class="px-4 py-2">
         <span class="text-xs text-gray-600">{{ $result['reason'] ?? ($result['parsed_keys']['reason'] ?? '-') }}</span>
         <br>
         @if(!empty($result['parsed_keys']))
            @foreach(\Illuminate\Support\Arr::except($result['parsed_keys'], ['reason', 'score']) as $k => $v)
                <span class="text-xs text-gray-400">[{{$k}}: {{$v}}]</span>
            @endforeach
         @endif
    </td>
    <td class="px-4 py-2">
        @if(!empty($result['already_exists']))
            <span class="text-red-600 font-bold">TAK</span>
        @else
            <span class="text-gray-400">-</span>
        @endif
    </td>
    <td class="px-4 py-2">
        @if($result['match_found'])
            <x-filament::button size="xs" color="success" wire:click="approveMatch('{{ $result['id'] }}')">
                Zaksięguj
            </x-filament::button>

            @if(($result['amount'] ?? 0) > 0)
                <x-filament::button size="xs" color="gray" class="ml-2" wire:click="createParticipantFromTransaction('{{ $result['id'] }}')">
                    Utwórz uczestnika
                </x-filament::button>
            @endif
        @else
            <div>
                <x-filament::button size="xs" color="gray" wire:click="manualMatch('{{ $result['id'] }}')">
                    Przypisz
                </x-filament::button>

                @if($manualSelection === $result['id'])
                    <div class="mt-2 p-2 border rounded bg-gray-50 shadow-lg absolute z-10 w-64">
                        <input wire:model.live.debounce.300ms="manualContractSearch" placeholder="Szukaj umowy..." class="w-full px-2 py-1 border rounded text-xs mb-2 text-black" />
                        <div class="mb-2">
                            <select wire:model="manualContractId" class="w-full border rounded px-2 py-1 text-xs text-black">
                                <option value="">-- wybierz --</option>
                                @foreach($manualContractOptions as $id => $label)
                                    <option value="{{ $id }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <x-filament::button size="xs" color="success" wire:click="confirmManualAssign('{{ $result['id'] }}')">OK</x-filament::button>
                            <x-filament::button size="xs" color="secondary" wire:click="$set('manualSelection', null)">X</x-filament::button>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </td>
</tr>