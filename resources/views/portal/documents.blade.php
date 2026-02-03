@extends('portal.layout')

@section('title', 'Dokumenty')

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
                <a href="{{ route('portal.documents') }}" class="px-4 py-3 hover:bg-blue-50 text-blue-700 border-l-4 border-blue-600 font-medium">
                    <i class="fas fa-file-alt mr-2 w-5"></i> Dokumenty
                </a>
                <a href="{{ route('portal.payments') }}" class="px-4 py-3 hover:bg-gray-50 text-gray-600 border-l-4 border-transparent hover:border-gray-300">
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
            <p class="text-sm text-yellow-700">Jesteś zalogowany jako kierownik wycieczki. Masz dostęp do dokumentów wewnętrznych.</p>
        </div>
        @endif
    </div>

    <!-- Main Content -->
    <div class="col-span-1 md:col-span-3">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Dokumenty do pobrania</h2>

            @if($documents->isEmpty())
                <div class="text-center py-10 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <i class="fas fa-folder-open text-4xl text-gray-400 mb-3"></i>
                    <p class="text-gray-500">Brak dostępnych dokumentów dla tego wydarzenia.</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4">
                    @foreach($documents as $doc)
                        <div class="flex items-center p-4 border rounded-lg hover:shadow-sm transition-shadow {{ $doc->type === 'manager' ? 'bg-yellow-50 border-yellow-200' : 'bg-white' }}">
                            <div class="flex-shrink-0 mr-4">
                                <i class="far fa-file-pdf text-3xl text-red-500"></i>
                            </div>
                            <div class="flex-grow">
                                <h3 class="font-semibold text-gray-800 flex items-center">
                                    {{ $doc->name }}
                                    @if($doc->type === 'manager')
                                        <span class="ml-2 px-2 py-0.5 text-xs bg-yellow-200 text-yellow-800 rounded-full">Tylko Kierownik</span>
                                    @endif
                                </h3>
                                @if($doc->description)
                                    <p class="text-sm text-gray-600">{{ $doc->description }}</p>
                                @endif
                                <p class="text-xs text-gray-400 mt-1">Dodano: {{ $doc->created_at->format('Y-m-d') }}</p>
                            </div>
                            <div class="flex-shrink-0 ml-4">
                                <a href="{{ route('portal.documents.download', $doc->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-download mr-2"></i> Pobierz
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection