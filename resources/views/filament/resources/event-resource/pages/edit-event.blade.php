<x-filament-panels::page>
    <form wire:submit="save" class="fi-form space-y-6">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </form>

    <x-filament::section 
        class="mt-8" 
        collapsible
        header-actions-position="bottom"
    >
        <x-slot name="heading">
            Hotel i zakwaterowanie
        </x-slot>

        <x-slot name="description">
            Konfiguracja noclegów dla każdej nocy imprezy.
        </x-slot>

        @include('filament.components.event-template-hotel-days-table', ['page' => $this])
        
        <!-- Add Room Section -->
        <div x-data="{ open: false }" class="mt-4 border border-gray-200 rounded-lg bg-gray-50 p-4">
            <button type="button" @click="open = !open" class="flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-800">
                <span x-show="!open">+ Dodaj nowy typ pokoju (Ad-hoc)</span>
                <span x-show="open">- Anuluj dodawanie pokoju</span>
            </button>
            <div x-show="open" class="mt-4">
                <p class="text-xs text-gray-500 mb-3">Dodaj nowy typ pokoju do systemu, aby móc go wybrać w tabeli powyżej (dla każdego noclegu osobno).</p>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                         <label class="block text-xs font-medium text-gray-700">Nazwa pokoju</label>
                         <input type="text" wire:model="newRoom.name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                         <label class="block text-xs font-medium text-gray-700">Pojemność (os.)</label>
                         <input type="number" wire:model="newRoom.people_count" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                         <label class="block text-xs font-medium text-gray-700">Cena domyślna</label>
                         <input type="number" step="0.01" wire:model="newRoom.price" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <button type="button" wire:click="createCustomRoom" class="w-full inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Dodaj pokój
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 flex gap-2">
            <button type="button" wire:click="saveHotelDays" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                💾 Zapisz noclegi i ceny
            </button>
            <button type="button" wire:click="forceRefreshHotelDays" class="text-xs text-blue-600 underline">
                Odśwież noclegi
            </button>
        </div>
    </x-filament::section>

    <x-filament::section 
        class="mt-8" 
        collapsible
        header-actions-position="bottom"
    >
        <x-slot name="heading">
            Dokumenty i Pliki
        </x-slot>

        <x-slot name="description">
            Zarządzanie plikami i dokumentami przypisanymi do imprezy. Określ widoczność dla poszczególnych ról.
        </x-slot>

        <!-- Document List -->
        <div class="overflow-x-auto rounded-lg border border-gray-200 mb-6">
            <table class="w-full text-sm text-left divide-y divide-gray-200">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-4 py-3">Nazwa</th>
                        <th class="px-4 py-3">Plik</th>
                        <th class="px-4 py-3 text-center">Biuro</th>
                        <th class="px-4 py-3 text-center">Kierowca</th>
                        <th class="px-4 py-3 text-center">Hotel</th>
                        <th class="px-4 py-3 text-center">Pilot</th>
                        <th class="px-4 py-3 text-center">Klient</th>
                        <th class="px-4 py-3 text-right">Akcje</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($event_documents as $doc)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ $doc['name'] }}
                                <div class="text-xs text-gray-400 font-normal">{{ $doc['created_at'] }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ \Illuminate\Support\Facades\Storage::url($doc['file_path']) }}" target="_blank" class="text-blue-600 hover:underline flex items-center gap-1">
                                    <x-heroicon-o-document class="w-4 h-4"/> Pobierz
                                </a>
                            </td>
                            
                            @foreach(['is_visible_office', 'is_visible_driver', 'is_visible_hotel', 'is_visible_pilot', 'is_visible_client'] as $col)
                            <td class="px-4 py-3 text-center">
                                <button type="button" wire:click="toggleDocumentVisibility({{ $doc['id'] }}, '{{ $col }}')" 
                                    @class([
                                        'rounded-full p-1 transition-colors',
                                        'bg-green-100 text-green-700' => $doc[$col],
                                        'bg-gray-100 text-gray-400 hover:bg-gray-200' => !$doc[$col],
                                    ])
                                >
                                    @if($doc[$col])
                                        <x-heroicon-s-check class="w-4 h-4" />
                                    @else
                                        <x-heroicon-s-x-mark class="w-4 h-4" />
                                    @endif
                                </button>
                            </td>
                            @endforeach

                            <td class="px-4 py-3 text-right">
                                <button type="button" wire:confirm="Czy na pewno usunąć ten dokument?" wire:click="deleteDocument({{ $doc['id'] }})" class="text-gray-400 hover:text-red-600 transition-colors p-1 rounded-full hover:bg-red-50" title="Usuń">
                                    <x-heroicon-o-trash class="w-5 h-5"/>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-400 italic">
                                Brak dokumentów przypisanych do tej imprezy.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Add Document Form -->
        <div class="bg-blue-50/50 p-5 rounded-lg border border-blue-100">
             <div class="text-xs font-bold text-blue-700 mb-3 uppercase tracking-wide flex items-center gap-2">
                <x-heroicon-o-plus class="w-4 h-4"/>
                Dodaj nowy dokument
             </div>
             
             <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                 <div>
                     <label class="block text-xs font-medium text-gray-700 mb-1">Nazwa dokumentu</label>
                     <input type="text" wire:model="new_document.name" class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="np. Umowa przewozu">
                     @error('new_document.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                 </div>

                 <div>
                     <label class="block text-xs font-medium text-gray-700 mb-1">Plik</label>
                     <input type="file" wire:model="new_document.file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                     @error('new_document.file') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                     <div wire:loading wire:target="new_document.file" class="text-xs text-blue-600 mt-1">Przesyłanie...</div>
                 </div>

                 <div class="md:col-span-2">
                     <label class="block text-xs font-medium text-gray-700 mb-2">Widoczność domyślna</label>
                     <div class="flex flex-wrap gap-4">
                         <label class="inline-flex items-center">
                             <input type="checkbox" wire:model="new_document.is_visible_office" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                             <span class="ml-2 text-sm text-gray-600">Biuro</span>
                         </label>
                         <label class="inline-flex items-center">
                             <input type="checkbox" wire:model="new_document.is_visible_driver" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                             <span class="ml-2 text-sm text-gray-600">Kierowca</span>
                         </label>
                         <label class="inline-flex items-center">
                             <input type="checkbox" wire:model="new_document.is_visible_hotel" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                             <span class="ml-2 text-sm text-gray-600">Hotel</span>
                         </label>
                         <label class="inline-flex items-center">
                             <input type="checkbox" wire:model="new_document.is_visible_pilot" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                             <span class="ml-2 text-sm text-gray-600">Pilot</span>
                         </label>
                         <label class="inline-flex items-center">
                             <input type="checkbox" wire:model="new_document.is_visible_client" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                             <span class="ml-2 text-sm text-gray-600">Klient</span>
                         </label>
                     </div>
                 </div>

                 <div class="md:col-span-2 text-right">
                     <button type="button" wire:click="addDocument" class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-black bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                         <x-heroicon-o-arrow-up-tray class="w-4 h-4 mr-2"/>
                         Wgraj dokument
                     </button>
                 </div>
             </div>
        </div>

    </x-filament::section>

    @if (count($relationManagers = $this->getRelationManagers()))
        <div class="fi-resource-relation-managers flex flex-col gap-y-6">
            @php
                $activeManager = $this->activeRelationManager ?? 'summary';
            @endphp

            <div class="fi-resource-relation-managers-header flex items-center justify-between">
                <nav class="fi-tabs flex max-w-full gap-x-3 overflow-x-auto border-b border-gray-200 pb-px dark:border-white/10 mx-auto w-full">
                    <button
                        wire:click="setRelationManager('summary')"
                        type="button"
                        @class([
                            'flex whitespace-nowrap border-b-2 px-3 pb-3 text-sm font-medium outline-none transition duration-75',
                            'border-primary-600 text-primary-600 dark:border-primary-400 dark:text-primary-400' => $activeManager === 'summary',
                            'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-200' => $activeManager !== 'summary',
                        ])
                    >
                        Podsumowanie i Warianty
                    </button>

                    @foreach ($relationManagers as $managerKey => $manager)
                        @php
                            $managerLabel = $manager instanceof \Filament\Resources\RelationManagers\RelationGroup
                                ? $manager->getLabel()
                                : $manager::getTitle($record, $this::class);
                                
                            $isActive = $activeManager == $managerKey;
                        @endphp

                        <button
                            wire:click="setRelationManager('{{ $managerKey }}')"
                            type="button"
                            @class([
                                'flex whitespace-nowrap border-b-2 px-3 pb-3 text-sm font-medium outline-none transition duration-75',
                                'border-primary-600 text-primary-600 dark:border-primary-400 dark:text-primary-400' => (string)$activeManager === (string)$managerKey,
                                'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-200' => (string)$activeManager !== (string)$managerKey,
                            ])
                        >
                            {{ $managerLabel }}
                        </button>
                    @endforeach
                </nav>
            </div>

            <div class="fi-resource-relation-managers-content">
                @if ($activeManager === 'summary')
                     <div wire:key="summary-variants-component">
                         @livewire(\App\Livewire\EventVariantsCalculation::class, ['ownerRecord' => $record], key('summary-comp'))
                     </div>
                @endif

                @foreach ($relationManagers as $managerKey => $manager)
                    @if ((string)$activeManager === (string)$managerKey)
                        <div wire:key="relation-manager-{{ $managerKey }}">
                            @if ($manager instanceof \Filament\Resources\RelationManagers\RelationGroup && $manager->getLabel() === 'Finanse')
                                <div class="flex flex-col gap-y-12">
                                    @foreach($manager->getManagers() as $subManager)
                                        <div wire:key="sub-manager-{{ $loop->index }}">
                                            @livewire($subManager, ['ownerRecord' => $record, 'pageClass' => static::class], key($subManager))
                                        </div>
                                    @endforeach
                                </div>
                            @elseif ($manager instanceof \Filament\Resources\RelationManagers\RelationGroup)
                                {{-- Custom Local Tabs for Groups (Organizacja etc) to avoid Livewire global state collision --}}
                                <div x-data="{ activeGroupTab: 0 }" class="flex flex-col gap-y-6">
                                    <div class="fi-tabs flex max-w-full gap-x-3 overflow-x-auto border-b border-gray-200 pb-px dark:border-white/10">
                                        @foreach($manager->getManagers() as $subKey => $subManager)
                                            @php 
                                                 $subLabel = $subManager instanceof \Filament\Resources\RelationManagers\RelationManagerConfiguration
                                                    ? $subManager->relationManager::getTitle($record, static::class)
                                                    : $subManager::getTitle($record, static::class);
                                            @endphp
                                            <button
                                                x-on:click="activeGroupTab = {{ $loop->index }}"
                                                :class="{
                                                    'border-primary-600 text-primary-600 dark:border-primary-400 dark:text-primary-400': activeGroupTab === {{ $loop->index }},
                                                    'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-200': activeGroupTab !== {{ $loop->index }},
                                                }"
                                                type="button"
                                                class="flex whitespace-nowrap border-b-2 px-3 pb-3 text-sm font-medium outline-none transition duration-75"
                                            >
                                                {{ $subLabel }}
                                            </button>
                                        @endforeach
                                    </div>
                                    
                                    @foreach($manager->getManagers() as $subKey => $subManager)
                                        <div x-show="activeGroupTab === {{ $loop->index }}" x-cloak>
                                            @livewire($subManager, ['ownerRecord' => $record, 'pageClass' => static::class], key($subManager))
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                {{-- Standard Single Manager --}}
                                @livewire($manager, ['ownerRecord' => $record, 'pageClass' => static::class], key($manager))
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
</x-filament-panels::page>
