@extends('portal.layout')

@section('title', 'Pulpit')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-4 gap-6">
    <!-- Sidebar / Menu -->
    <div class="col-span-1">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-4 bg-gray-50 border-b font-semibold text-gray-700">Menu</div>
            <nav class="flex flex-col">
                <a href="{{ route('portal.dashboard') }}" class="px-4 py-3 hover:bg-blue-50 text-blue-700 border-l-4 border-blue-600 font-medium">
                    <i class="fas fa-home mr-2 w-5"></i> Pulpit
                </a>
                <a href="{{ route('portal.documents') }}" class="px-4 py-3 hover:bg-gray-50 text-gray-600 border-l-4 border-transparent hover:border-gray-300">
                    <i class="fas fa-file-alt mr-2 w-5"></i> Dokumenty
                </a>
                <a href="{{ route('portal.payments') }}" class="px-4 py-3 hover:bg-gray-50 text-gray-600 border-l-4 border-transparent hover:border-gray-300">
                    <i class="fas fa-credit-card mr-2 w-5"></i> Płatności
                </a>
                @if($role === 'pilot' || $role === 'manager')
                <a href="{{ route('portal.expenses') }}" class="px-4 py-3 hover:bg-gray-50 text-gray-600 border-l-4 border-transparent hover:border-gray-300">
                    <i class="fas fa-hand-holding-dollar mr-2 w-5"></i> Rozliczenia
                </a>
                @endif
                <a href="{{ route('portal.messages') }}" class="px-4 py-3 hover:bg-gray-50 text-gray-600 border-l-4 border-transparent hover:border-gray-300">
                    <i class="fas fa-envelope-open mr-2 w-5"></i> Wiadomości
                </a>
                <a href="{{ route('portal.contact') }}" class="px-4 py-3 hover:bg-gray-50 text-gray-600 border-l-4 border-transparent hover:border-gray-300">
                    <i class="fas fa-envelope mr-2 w-5"></i> Kontakt / Pytania
                </a>
            </nav>
        </div>
        
        @if($role === 'manager' || $role === 'pilot')
        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <h4 class="font-bold text-yellow-800 mb-2">
                <i class="fas fa-user-shield text-yellow-600 mr-2"></i>
                Strefa {{ $role === 'manager' ? 'Organizatora' : 'Pilota' }}
            </h4>
            <p class="text-sm text-yellow-700">Masz uprawnienia do podglądu uwag technicznych i wprowadzania rozliczeń.</p>
        </div>
        @endif
    </div>

    <!-- Main Content -->
    <div class="col-span-1 md:col-span-3">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $event->name }}</h1>
            <p class="text-gray-600 mb-4"><i class="fas fa-calendar-alt mr-2"></i> {{ $event->start_date }} - {{ $event->end_date }} ({{ $event->duration_days }} dni)</p>
            
            <div class="prose max-w-none text-gray-600">
                {!! $event->description !!}
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Program imprezy</h2>
            <div class="space-y-4">
                @forelse($event->programPoints as $point)
                    <div class="border-l-4 border-blue-400 pl-4 py-2">
                        <div class="flex items-center text-sm text-gray-500 mb-1">
                            <span class="font-bold mr-2">Dzień {{ $point->day }}</span>
                            @if($point->start_time) <span class="mr-2"><i class="far fa-clock"></i> {{ $point->start_time }}</span> @endif
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">{{ $point->name }}</h3>
                        <p class="text-gray-600 text-sm mt-1">{{ strip_tags($point->description) }}</p>
                        
                        @if(($role === 'pilot' || $role === 'manager') && $point->pilot_notes)
                        <div class="mt-2 p-2 bg-yellow-50 border border-yellow-100 rounded text-sm text-yellow-900">
                            <strong><i class="fas fa-info-circle mr-1"></i> Uwagi dla pilota:</strong>
                            {!! $point->pilot_notes !!}
                        </div>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500 italic">Program imprezy jest w trakcie ustalania.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection