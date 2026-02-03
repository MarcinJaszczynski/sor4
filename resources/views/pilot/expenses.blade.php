@extends('pilot.layout')

@section('content')
    <h2 class="text-xl font-bold mb-4">Rozliczenie Wyjazdu</h2>
    
    <div class="bg-white rounded shadow p-4 mb-6">
        <h3 class="font-bold text-gray-700 mb-2">Dodaj Nowy Wydatek</h3>
        <form action="{{ route('pilot.expenses.store', $event->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-3">

                @if(($pilotPayPoints ?? collect())->isNotEmpty())
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Za co płatność (punkt programu)</label>
                        <select name="event_program_point_id" class="w-full border rounded p-2 text-sm">
                            <option value="">-- wybierz --</option>
                            @foreach($pilotPayPoints as $point)
                                @php
                                    $label = ($point->templatePoint?->name ?? $point->name ?? ('#' . $point->id));
                                    $currency = $point->pilot_payment_currency ?? 'PLN';
                                    $needed = $point->pilot_payment_needed !== null ? number_format($point->pilot_payment_needed, 2, ',', ' ') . ' ' . $currency : '—';
                                    $given = $point->pilot_payment_given !== null ? number_format($point->pilot_payment_given, 2, ',', ' ') . ' ' . $currency : '—';
                                @endphp
                                <option value="{{ $point->id }}">
                                    Dzień {{ $point->day }} — {{ $label }} (potrzebuje: {{ $needed }}, otrzymał: {{ $given }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Rodzaj / Opis</label>
                    <input type="text" name="description" placeholder="np. Parking, Autostrada A4" class="w-full border rounded p-2 text-sm">
                </div>

                <div class="flex gap-2">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-500 mb-1">Kwota</label>
                        <input type="number" step="0.01" name="amount" class="w-full border rounded p-2 text-sm" required>
                    </div>
                    <div class="w-24">
                        <label class="block text-xs font-bold text-gray-500 mb-1">Waluta</label>
                        <select name="currency" class="w-full border rounded p-2 text-sm">
                            <option value="PLN">PLN</option>
                            <option value="EUR">EUR</option>
                            <option value="USD">USD</option>
                            <option value="CZK">CZK</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Data</label>
                    <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" class="w-full border rounded p-2 text-sm" required>
                </div>

                <div class="border-2 border-dashed border-gray-300 rounded p-4 text-center cursor-pointer hover:bg-gray-50">
                    <label class="cursor-pointer block">
                        <span class="block text-gray-400 text-xs mb-2">Kliknij, aby dodać dokument (opcjonalnie)</span>
                        <input type="file" name="document_image" accept="image/*" capture="environment" class="hidden" onchange="document.getElementById('file-name').innerText = this.files[0].name">
                        <div id="file-name" class="text-sm font-bold text-blue-600">Wybierz plik</div>
                    </label>
                </div>

                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded shadow mt-2">
                    Wyślij Wydatek
                </button>
            </div>
        </form>
    </div>

    <div class="space-y-3">
        <h3 class="font-bold text-gray-500 text-sm">Historia Wydatków</h3>
        @foreach($expenses as $cost)
            <div class="bg-white rounded border border-gray-100 p-3 flex justify-between items-center shadow-sm">
                <div>
                    <div class="font-bold text-gray-800">{{ $cost->description ?: '—' }}</div>
                    <div class="text-xs text-gray-500">{{ $cost->expense_date->format('d.m.Y') }}</div>
                    <div class="text-xs text-gray-500">
                        {{ $cost->eventProgramPoint?->templatePoint?->name ?? $cost->eventProgramPoint?->name ?? '' }}
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-blue-800">{{ number_format($cost->amount, 2) }} {{ $cost->currency }}</div>
                    <div class="text-xs text-gray-400">
                        @if($cost->status == 'pending') <span class="text-yellow-600">Oczekuje</span>
                        @else <span class="text-green-600">Zatwierdzono</span> @endif
                    </div>
                </div>
            </div>
        @endforeach
        
        @if($expenses->isEmpty())
            <p class="text-center text-gray-400 text-sm py-4">Brak dodanych kosztów.</p>
        @endif
    </div>
@endsection
