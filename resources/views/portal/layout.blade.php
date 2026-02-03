<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strefa Klienta - @yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body class="bg-gray-100 text-gray-800 font-sans">
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-bold text-primary-600">BP RAFA - Strefa Klienta</span>
                </div>
                <div class="flex items-center space-x-4">
                    @if(session()->has('portal_event_id'))
                        <div class="text-sm text-gray-500">
                             Zalogowany jako: 
                             <span class="font-semibold">
                                 @php
                                    $roleLabels = ['manager' => 'Organizator', 'participant' => 'Uczestnik', 'pilot' => 'Pilot'];
                                 @endphp
                                 {{ $roleLabels[session('portal_role')] ?? session('portal_role') }}
                             </span>
                        </div>
                        <a href="{{ route('portal.logout') }}" class="text-red-600 hover:text-red-800 text-sm font-medium">Wyloguj</a>
                    @endif
                </div>
            </div>
        </div>
        @if(session()->has('portal_event_id'))
        <div class="bg-gray-50 border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex space-x-6 py-2 overflow-x-auto">
                    <a href="{{ route('portal.dashboard') }}" class="text-sm {{ request()->routeIs('portal.dashboard') ? 'text-blue-600 font-bold' : 'text-gray-600' }} hover:text-blue-500">Program</a>
                    <a href="{{ route('portal.documents') }}" class="text-sm {{ request()->routeIs('portal.documents') ? 'text-blue-600 font-bold' : 'text-gray-600' }} hover:text-blue-500">Dokumenty</a>
                    <a href="{{ route('portal.payments') }}" class="text-sm {{ request()->routeIs('portal.payments') ? 'text-blue-600 font-bold' : 'text-gray-600' }} hover:text-blue-500">Płatności</a>
                    
                    @if(session('portal_role') === 'pilot' || session('portal_role') === 'manager')
                        <a href="{{ route('portal.expenses') }}" class="text-sm {{ request()->routeIs('portal.expenses') ? 'text-blue-600 font-bold' : 'text-gray-600' }} hover:text-blue-500">Rozliczenia Pilota</a>
                    @endif
                    
                    <a href="{{ route('portal.messages') }}" class="text-sm {{ request()->routeIs('portal.messages') ? 'text-blue-600 font-bold' : 'text-gray-600' }} hover:text-blue-500">Wiadomości</a>
                    <a href="{{ route('portal.contact') }}" class="text-sm {{ request()->routeIs('portal.contact') ? 'text-blue-600 font-bold' : 'text-gray-600' }} hover:text-blue-500">Kontakt</a>
                    <a href="{{ route('portal.help') }}" target="_blank" class="text-sm {{ request()->is('strefa-klienta/help') ? 'text-blue-600 font-bold' : 'text-gray-600' }} hover:text-blue-500">Pomoc / Instrukcja</a>
                </div>
            </div>
        </div>
        @endif
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </div>

    <footer class="bg-gray-200 mt-12 py-6">
        <div class="text-center text-gray-500 text-sm">
            &copy; {{ date('Y') }} Biuro Podróży RAFA. Wszelkie prawa zastrzeżone.
        </div>
    </footer>
</body>
</html>