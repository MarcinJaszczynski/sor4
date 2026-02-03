@extends('pilot.layout')

@section('content')
    <h2 class="text-xl font-bold mb-4">Wpłaty na miejscu</h2>

    <div class="bg-white rounded shadow p-4 mb-6">
        <h3 class="font-bold text-gray-700 mb-2">Dodaj nową wpłatę</h3>
        <form action="{{ route('pilot.payments.store', $event->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-3">
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
                            <option value="HUF">HUF</option>
                            <option value="GBP">GBP</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Data wpłaty</label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="w-full border rounded p-2 text-sm" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Komentarz (opcjonalnie)</label>
                    <input type="text" name="description" placeholder="np. Dopłata za pokój" class="w-full border rounded p-2 text-sm">
                </div>

                <div class="border-2 border-dashed border-gray-300 rounded p-4 text-center cursor-pointer hover:bg-gray-50">
                    <label class="cursor-pointer block">
                        <span class="block text-gray-400 text-xs mb-2">Dodaj dokumenty potwierdzające (opcjonalnie)</span>
                        <input type="file" name="documents[]" accept="image/*,.pdf" multiple class="hidden" onchange="document.getElementById('file-name').innerText = Array.from(this.files).map(f => f.name).join(', ')">
                        <div id="file-name" class="text-sm font-bold text-blue-600">Wybierz pliki</div>
                    </label>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded shadow mt-2">
                    Zapisz wpłatę
                </button>
            </div>
        </form>
    </div>

    <div class="space-y-3">
        <h3 class="font-bold text-gray-500 text-sm">Historia wpłat</h3>
        @foreach($payments as $payment)
            <div class="bg-white rounded border border-gray-100 p-3 flex justify-between items-center shadow-sm">
                <div>
                    <div class="font-bold text-gray-800">
                        {{ number_format($payment->amount, 2) }} {{ $payment->currency ?? 'PLN' }}
                    </div>
                    <div class="text-xs text-gray-500">{{ $payment->payment_date?->format('d.m.Y') }}</div>
                    @if($payment->description)
                        <div class="text-xs text-gray-600 mt-1">{{ $payment->description }}</div>
                    @endif
                    @if(!empty($payment->documents))
                        <div class="text-xs text-blue-600 mt-1">Załączniki: {{ count($payment->documents) }}</div>
                    @endif
                </div>
                <div class="text-right text-xs text-gray-400">
                    <span class="inline-block px-2 py-1 rounded bg-blue-50 text-blue-700">Wpłata</span>
                </div>
            </div>
        @endforeach

        @if($payments->isEmpty())
            <p class="text-center text-gray-400 text-sm py-4">Brak dodanych wpłat.</p>
        @endif
    </div>
@endsection
