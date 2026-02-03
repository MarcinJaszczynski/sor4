<x-filament-panels::page>
    <div class="space-y-6">
        <form class="flex flex-wrap gap-3 items-end" method="GET" action="{{ request()->url() }}">
            <div>
                <label class="block text-xs text-gray-600 mb-1">Opiekun</label>
                <select name="assigned_to" class="fi-select-input block border py-2 pe-8 ps-3 text-sm rounded-lg">
                    <option value="">Wszyscy</option>
                    @foreach($assignees as $u)
                        <option value="{{ $u->id }}" {{ (int) $assignedTo === (int) $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-gray-600 mb-1">Termin od</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="fi-input block border py-2 px-3 text-sm rounded-lg" />
            </div>

            <div>
                <label class="block text-xs text-gray-600 mb-1">Termin do</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="fi-input block border py-2 px-3 text-sm rounded-lg" />
            </div>

            <button class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-primary-600 text-white" type="submit">
                Zastosuj
            </button>
        </form>

        <x-filament::section>
            <x-slot name="heading">Podsumowanie wg opiekuna</x-slot>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                @forelse($summary as $name => $data)
                    <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-800">
                        <div class="text-xs text-gray-500">{{ $name }}</div>
                        <div class="font-semibold">{{ $data['count'] }} rat</div>
                        <div class="text-sm">{{ number_format($data['amount'], 2, ',', ' ') }} PLN</div>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">Brak zaległości.</div>
                @endforelse
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Lista zaległych rat</x-slot>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500">
                            <th class="py-2 pr-4">Opiekun</th>
                            <th class="py-2 pr-4">Kod</th>
                            <th class="py-2 pr-4">Impreza</th>
                            <th class="py-2 pr-4">Umowa</th>
                            <th class="py-2 pr-4">Termin</th>
                            <th class="py-2 pr-4">Kwota</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $inst)
                            @php $event = $inst->contract?->event; @endphp
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <td class="py-2 pr-4">{{ $event?->assignedUser?->name ?? '-' }}</td>
                                <td class="py-2 pr-4">{{ $event?->public_code ?? '-' }}</td>
                                <td class="py-2 pr-4">{{ $event?->name ?? '-' }}</td>
                                <td class="py-2 pr-4">{{ $inst->contract?->contract_number ?? '-' }}</td>
                                <td class="py-2 pr-4">{{ $inst->due_date?->format('d.m.Y') ?? '-' }}</td>
                                <td class="py-2 pr-4">{{ number_format((float) ($inst->amount ?? 0), 2, ',', ' ') }} PLN</td>
                            </tr>
                        @empty
                            <tr><td class="py-3 text-gray-500" colspan="6">Brak zaległości.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
