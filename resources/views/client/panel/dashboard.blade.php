<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Klienta - {{ $contract->event->name ?? 'Impreza' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <div class="max-w-4xl mx-auto py-10 px-4">
        
        <!-- Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Panel Klienta @if($is_organizer) (Organizator) @else (Uczestnik) @endif</h1>
            <p class="text-gray-600 mt-2">Umowa nr: <span class="font-semibold">{{ $contract->contract_number }}</span></p>
            <p class="text-gray-600">Impreza: <span class="font-semibold text-blue-600">{{ $contract->event->name ?? 'Brak nazwy' }}</span></p>
        </div>

        <!-- Finanse (Widoczne tylko dla Organizatora / Właściciela umowy) -->
        @if($is_organizer)
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Finanse</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                <div class="p-4 bg-gray-50 rounded">
                    <p class="text-sm text-gray-500">Całkowity koszt</p>
                    <p class="text-2xl font-bold">{{ number_format($contract->total_amount, 2) }} PLN</p>
                </div>
                <div class="p-4 bg-green-50 rounded">
                    <p class="text-sm text-green-600">Opłacono</p>
                    <p class="text-2xl font-bold text-green-700">{{ number_format($contract->paid_amount, 2) }} PLN</p>
                </div>
                <div class="p-4 bg-red-50 rounded">
                    <p class="text-sm text-red-600">Do zapłaty</p>
                    <p class="text-2xl font-bold text-red-700">{{ number_format(max(0, $contract->total_amount - $contract->paid_amount), 2) }} PLN</p>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end">
                @if($contract->is_fully_paid)
                    <span class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-md font-semibold">
                        ✓ Opłacono w całości
                    </span>
                @else
                    <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow" onclick="alert('Przekierowanie do Przelewy24...')">
                        Zapłać online (Bramka)
                    </button>
                @endif
            </div>
        </div>
        @endif

        <!-- Dokumenty / Voucher (Widoczność zależna od opłacenia) -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Dokumenty</h2>
            <div class="space-y-3">
                <!-- Dokumenty Umowy - TYLKO DLA ORGANIZATORA -->
                @if($is_organizer)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded hover:bg-gray-100 transition">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        <span>Umowa {{ $contract->contract_number }}</span>
                    </div>
                    <a href="{{ route('client.contract', $contract->uuid) }}" class="text-blue-600 hover:underline font-medium">Pobierz PDF</a>
                </div>
                @endif

                <!-- Voucher - Dostępny jeśli opłacono (dla wszystkich, jeśli Organizator pozwoli, lub tylko Organizator) -->
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded hover:bg-gray-100 transition">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                        <span class="{{ $contract->is_fully_paid ? 'text-gray-800' : 'text-gray-400' }}">Voucher / Bilet</span>
                    </div>
                    @if($contract->is_fully_paid)
                        <a href="{{ route('client.voucher', $contract->uuid) }}" class="text-blue-600 hover:underline font-medium">Pobierz PDF</a>
                    @else
                        <span class="text-gray-400 text-sm">Dostępne po opłaceniu całości</span>
                    @endif
                </div>

                <!-- Dokumenty Ogólne Imprezy (Dostępne dla wszystkich) -->
                @foreach($contract->event->documents as $doc)
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded hover:bg-blue-100 transition">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span>{{ $doc->name }}</span>
                        </div>
                        <a href="{{ asset('storage/' . $doc->path) }}" target="_blank" class="text-blue-600 hover:underline font-medium">Otwórz</a>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Uczestnicy -->
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">
                @if($is_organizer) Lista Uczestników @else Moje Dane @endif
            </h2>
            
            <p class="text-sm text-gray-600 mb-4">
                @if($is_organizer)
                    Zarządzaj listą uczestników i udostępnij im linki do edycji.
                @else
                    Uzupełnij swoje dane niezbędne do ubezpieczenia.
                @endif
            </p>

            @if(session('success'))
                <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('client.participants.update', $is_organizer ? $contract->uuid : $participant->uuid) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    
                    @if($is_organizer)
                        <!-- WIDOK ORGANIZATORA: Pełna lista -->
                        @foreach($contract->participants as $index => $p)
                            <div class="p-4 border rounded bg-gray-50 flex flex-col gap-4">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <input type="hidden" name="participants[{{ $index }}][id]" value="{{ $p->id }}">
                                    
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500">Imię</label>
                                        <input type="text" name="participants[{{ $index }}][first_name]" value="{{ $p->first_name }}" class="mt-1 block w-full rounded border-gray-300 p-2 border">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500">Nazwisko</label>
                                        <input type="text" name="participants[{{ $index }}][last_name]" value="{{ $p->last_name }}" class="mt-1 block w-full rounded border-gray-300 p-2 border">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500">PESEL</label>
                                        <input type="text" name="participants[{{ $index }}][pesel]" value="{{ $p->pesel }}" class="mt-1 block w-full rounded border-gray-300 p-2 border">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500">Data ur.</label>
                                        <input type="date" name="participants[{{ $index }}][birth_date]" value="{{ $p->birth_date ? $p->birth_date->format('Y-m-d') : '' }}" class="mt-1 block w-full rounded border-gray-300 p-2 border">
                                    </div>
                                </div>
                                
                                <!-- Link dla uczestnika -->
                                <div class="text-xs text-gray-500 flex items-center justify-between border-t pt-2 mt-2">
                                    <span>Link dla uczestnika: </span>
                                    <code class="bg-gray-200 px-2 py-1 rounded select-all">{{ route('client.panel', $p->uuid) }}</code>
                                </div>
                            </div>
                        @endforeach
                        
                        @if($contract->participants->isEmpty())
                            <div class="text-center py-4 text-gray-500">Brak uczestników na liście.</div>
                        @endif

                    @else
                        <!-- WIDOK UCZESTNIKA: Tylko ja -->
                        <div class="p-4 border rounded bg-gray-50 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Imię</label>
                                <input type="text" name="first_name" value="{{ $participant->first_name }}" class="mt-1 block w-full p-2 border rounded">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nazwisko</label>
                                <input type="text" name="last_name" value="{{ $participant->last_name }}" class="mt-1 block w-full p-2 border rounded">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">PESEL</label>
                                <input type="text" name="pesel" value="{{ $participant->pesel }}" class="mt-1 block w-full p-2 border rounded">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Data Urodzenia</label>
                                <input type="date" name="birth_date" value="{{ $participant->birth_date ? $participant->birth_date->format('Y-m-d') : '' }}" class="mt-1 block w-full p-2 border rounded">
                            </div>
                        </div>
                    @endif

                </div>
                
                <div class="mt-6">
                    <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded hover:bg-gray-700 transition">Zapisz zmiany</button>
                    @if($is_organizer)
                    <!-- TODO: Button to add blank row JS logic -->
                    @endif
                </div>
            </form>
        </div>

    </div>

</body>
</html>
