@extends('portal.layout')

@section('title', 'Płatności')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-4 gap-6">
    <!-- Sidebar / Menu -->
    <div class="col-span-1">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4 bg-gray-50 border-b font-semibold text-gray-700">Menu</div>
            <nav class="flex flex-col">
                <a href="{{ route('portal.dashboard') }}" class="px-4 py-3 hover:bg-gray-50 text-gray-600 border-l-4 border-transparent hover:border-gray-300">
                    <i class="fas fa-home mr-2 w-5"></i> Pulpit
                </a>
                <a href="{{ route('portal.documents') }}" class="px-4 py-3 hover:bg-gray-50 text-gray-600 border-l-4 border-transparent hover:border-gray-300">
                    <i class="fas fa-file-alt mr-2 w-5"></i> Dokumenty
                </a>
                <a href="{{ route('portal.payments') }}" class="px-4 py-3 hover:bg-blue-50 text-blue-700 border-l-4 border-blue-600 font-medium">
                    <i class="fas fa-credit-card mr-2 w-5"></i> Płatności
                </a>
                <a href="{{ route('portal.contact') }}" class="px-4 py-3 hover:bg-gray-50 text-gray-600 border-l-4 border-transparent hover:border-gray-300">
                    <i class="fas fa-envelope mr-2 w-5"></i> Kontakt / Pytania
                </a>
            </nav>
        </div>
        
        @if($role === 'manager')
        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <h4 class="font-bold text-yellow-800 mb-2"><i class="fas fa-crown text-yellow-600 mr-2"></i>Strefa Organizatora</h4>
            <p class="text-sm text-yellow-700">Jesteś zalogowany jako kierownik. Widzisz historię wszystkich wpłat dodanych w systemie.</p>
        </div>
        @endif
    </div>

    <!-- Main Content -->
    <div class="col-span-1 md:col-span-3">
        
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p class="font-bold">Sukces!</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Payment Info / Form -->
            <div class="lg:col-span-2">
                 <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Dane do przelewu</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                        <div>
                            <p class="font-semibold text-gray-700">Odbiorca:</p>
                            <p>Biuro Podróży RAFA</p>
                            <p>ul. Przykładowa 123</p>
                            <p>00-000 Warszawa</p>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-700">Numer konta:</p>
                            <p class="font-mono text-lg text-blue-600 font-bold">PL 00 0000 0000 0000 0000 0000 0000</p>
                            <p class="mt-2 text-xs text-gray-500">W tytule przelewu prosimy wpisać nazwę imprezy oraz imię i nazwisko uczestnika.</p>
                        </div>
                    </div>
                 </div>

                 <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Szybka płatność online</h2>
                    <p class="text-sm text-gray-500 mb-4">Użyj poniższego formularza, aby dokonać bezpiecznej płatności online (symulacja).</p>
                    
                    <form action="{{ route('portal.payments.process') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Kwota (PLN)</label>
                                <input type="number" step="0.01" name="amount" id="amount" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" placeholder="0.00" required>
                            </div>
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Tytułem</label>
                                <input type="text" name="title" id="title" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" value="Zaliczka - {{ $event->name }}" required>
                            </div>
                            <div>
                                <label for="payer_name" class="block text-sm font-medium text-gray-700 mb-1">Imię i nazwisko wpłacającego</label>
                                <input type="text" name="payer_name" id="payer_name" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required>
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Adres e-mail (potwierdzenie)</label>
                                <input type="email" name="email" id="email" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required>
                            </div>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-green-600 text-white font-medium rounded-md hover:bg-green-700 transition-colors shadow-sm">
                                <i class="fas fa-lock mr-2"></i> Przejdź do płatności
                            </button>
                        </div>
                    </form>
                 </div>
            </div>

            <!-- List (Manager only) -->
            @if($role === 'manager')
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Rejestr wpłat (Tylko Manager)</h2>
                    
                    @if($payments->isEmpty())
                        <p class="text-gray-500 italic">Brak zarejestrowanych wpłat.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opis</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kwota</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($payments as $payment)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $payment->description ?? '-' }}
                                            @if($payment->is_advance)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                  Zaliczka
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ number_format($payment->amount, 2) }} PLN
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection