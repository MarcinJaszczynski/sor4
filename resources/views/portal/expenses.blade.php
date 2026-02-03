@extends('portal.layout')

@section('title', 'Rozliczenia Pilota')

@section('content')
<div class="space-y-6">
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-4">Wprowadź nowy wydatek</h2>
        <form action="{{ route('portal.expenses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kwota</label>
                    <input type="number" step="0.01" name="amount" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Waluta</label>
                    <select name="currency" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="PLN">PLN</option>
                        <option value="EUR">EUR</option>
                        <option value="USD">USD</option>
                        <option value="CZK">CZK</option>
                        <option value="HUF">HUF</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Data wydatku</label>
                    <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Opis (co zostało opłacone)</label>
                <input type="text" name="description" required placeholder="np. Parking, Bilety wstępu, Lunch dla grupy" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Skan/Zdjęcie dokumentu</label>
                <input type="file" name="document" accept="image/*,.pdf" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Zapisz wydatek
            </button>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-bold">Twoje wydatki na tej imprezie</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kwota</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Opis</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dokument</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($expenses as $expense)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $expense->expense_date->format('d.m.Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">{{ number_format($expense->amount, 2) }} {{ $expense->currency }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $expense->description }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($expense->document_image)
                                <a href="{{ Storage::url($expense->document_image) }}" target="_blank" class="text-blue-600 hover:underline">Zobacz plik</a>
                            @else
                                <span class="text-gray-400">Brak</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = ['pending' => 'bg-yellow-100 text-yellow-800', 'approved' => 'bg-green-100 text-green-800', 'rejected' => 'bg-red-100 text-red-800'];
                                $statusLabels = ['pending' => 'Oczekuje', 'approved' => 'Zatwierdzony', 'rejected' => 'Odrzucony'];
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$expense->status] ?? 'bg-gray-100' }}">
                                {{ $statusLabels[$expense->status] ?? $expense->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">Brak zapisanych wydatków.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
