<x-filament-panels::page>
    <x-filament::card>
        {{ $this->form }}
        
        <div class="flex justify-end mt-4">
            <x-filament::button wire:click="import">
                Analizuj plik
            </x-filament::button>
        </div>
    </x-filament::card>

    @if(!empty($reconciliationResults))
        <x-filament::section>
            <x-slot name="heading">
                Wyniki analizy
            </x-slot>

            <div class="flex flex-wrap gap-2 justify-end mb-4">
                <x-filament::button color="success" wire:click="bulkApproveMatchedIncomes">
                    Zaksięguj dopasowane wpływy
                </x-filament::button>
                <x-filament::button color="gray" wire:click="bulkCreateParticipantsFromMatchedIncomes">
                    Utwórz uczestników z dopasowanych wpływów
                </x-filament::button>
            </div>

            <div class="mb-4">
                <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Szukaj transakcji..." 
                       class="w-full px-4 py-2 border rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200">
            </div>

            @php
                $headers = [
                    'date' => 'Data',
                    'sender' => 'Nadawca',
                    'confidence' => 'Pewność',
                    'title' => 'Tytuł',
                    'amount' => 'Kwota',
                    'contract_id' => 'Status'
                ];
            @endphp
            
            <h3 class="text-lg font-bold mb-2 text-custom-600">Wpływy ({{ $this->incomes->count() }})</h3>
            <div class="overflow-x-auto mb-8 border rounded-lg">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            @foreach($headers as $field => $label)
                                <th class="px-4 py-3 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" wire:click="sort('{{ $field }}')">
                                    <div class="flex items-center gap-1">
                                        {{ $label }}
                                        @if($sortField === $field)
                                            <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3"/>
                                        @endif
                                    </div>
                                </th>
                            @endforeach
                            <th class="px-4 py-3">Powód / Klucze</th>
                            <th class="px-4 py-3">Zaksięgowano</th>
                            <th class="px-4 py-3">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->incomes as $result)
                            @include('filament.pages.banking.partials.transaction-row', ['result' => $result])
                        @empty
                            <tr><td colspan="9" class="p-4 text-center text-gray-500">Brak wpływów pasujących do filtrów.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <h3 class="text-lg font-bold mb-2 text-red-600">Wydatki ({{ $this->expenses->count() }})</h3>
            <div class="overflow-x-auto border rounded-lg">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            @foreach($headers as $field => $label)
                                <th class="px-4 py-3 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" wire:click="sort('{{ $field }}')">
                                    <div class="flex items-center gap-1">
                                        {{ $label }}
                                        @if($sortField === $field)
                                            <x-heroicon-s-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="w-3 h-3"/>
                                        @endif
                                    </div>
                                </th>
                            @endforeach
                             <th class="px-4 py-3">Powód / Klucze</th>
                             <th class="px-4 py-3">Zaksięgowano</th>
                             <th class="px-4 py-3">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                         @forelse($this->expenses as $result)
                            @include('filament.pages.banking.partials.transaction-row', ['result' => $result])
                        @empty
                            <tr><td colspan="9" class="p-4 text-center text-gray-500">Brak wydatków pasujących do filtrów.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
