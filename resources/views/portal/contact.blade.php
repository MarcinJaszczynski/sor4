@extends('portal.layout')

@section('title', 'Kontakt')

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
                <a href="{{ route('portal.payments') }}" class="px-4 py-3 hover:bg-gray-50 text-gray-600 border-l-4 border-transparent hover:border-gray-300">
                    <i class="fas fa-credit-card mr-2 w-5"></i> Płatności
                </a>
                <a href="{{ route('portal.contact') }}" class="px-4 py-3 hover:bg-blue-50 text-blue-700 border-l-4 border-blue-600 font-medium">
                    <i class="fas fa-envelope mr-2 w-5"></i> Kontakt / Pytania
                </a>
            </nav>
        </div>
        
        @if($role === 'manager')
        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <h4 class="font-bold text-yellow-800 mb-2"><i class="fas fa-crown text-yellow-600 mr-2"></i>Strefa Organizatora</h4>
            <p class="text-sm text-yellow-700">Jesteś zalogowany jako kierownik.</p>
        </div>
        @endif
    </div>

    <!-- Main Content -->
    <div class="col-span-1 md:col-span-3">
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p class="font-bold">Wiadomość wysłana!</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Formularz kontaktowy -->
            <div>
                 <div class="bg-white rounded-lg shadow-md p-6 h-full">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Wyślij wiadomość do organizatora</h2>
                    <p class="text-sm text-gray-500 mb-6">Masz pytania dotyczące wyjazdu? Wypełnij formularz, a skontaktujemy się z Tobą najszybciej jak to możliwe.</p>
                    
                    <form action="{{ route('portal.contact.submit') }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Twoje imię i nazwisko</label>
                                <input type="text" name="name" id="name" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required>
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Twój adres e-mail</label>
                                <input type="email" name="email" id="email" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required>
                            </div>
                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Treść wiadomości</label>
                                <textarea name="message" id="message" rows="5" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required></textarea>
                            </div>
                        </div>
                        <div class="mt-6">
                            <button type="submit" class="w-full inline-flex justify-center items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors shadow-sm">
                                <i class="fas fa-paper-plane mr-2"></i> Wyślij wiadomość
                            </button>
                        </div>
                    </form>
                 </div>
            </div>

            <!-- Dane kontaktowe -->
            <div>
                 <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Dane kontaktowe</h2>
                    
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                    <i class="fas fa-map-marker-alt text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">Biuro Podróży RAFA</h3>
                                <p class="mt-1 text-gray-600">
                                    ul. Przykładowa 123<br>
                                    00-000 Warszawa
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                    <i class="fas fa-phone text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">Telefon</h3>
                                <p class="mt-1 text-gray-600">
                                    +48 123 456 789<br>
                                    <span class="text-xs text-gray-400">Pn-Pt: 9:00 - 17:00</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                    <i class="fas fa-envelope text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">E-mail</h3>
                                <p class="mt-1 text-gray-600">
                                    kontakt@bprafa.pl
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-500 tracking-wider uppercase mb-3">O nas</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Jesteśmy biurem podróży z wieloletnim doświadczeniem. Naszą pasją jest organizowanie niezapomnianych wyjazdów i przygód.
                        </p>
                    </div>
                 </div>
            </div>
        </div>
    </div>
</div>
@endsection