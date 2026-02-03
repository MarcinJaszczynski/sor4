@extends('pilot.layout')

@section('content')
    <div class="bg-white rounded shadow p-6 mb-6">
        <h2 class="text-2xl font-bold mb-2">{{ $event->name }}</h2>
        <p class="text-gray-600 mb-4">{{ $event->start_date->format('d.m.Y') }} - {{ $event->end_date->format('d.m.Y') }}</p>
        
        <div class="grid grid-cols-2 gap-4 text-sm mb-4">
            <div class="bg-gray-50 p-3 rounded">
                <span class="block text-gray-400 text-xs">Pilot / Opiekun</span>
                <span class="font-semibold">{{ $event->guides->pluck('name')->join(', ') ?: Auth::user()->name }}</span>
            </div>
            <div class="bg-gray-50 p-3 rounded">
                <span class="block text-gray-400 text-xs">Autokar</span>
                <span class="font-semibold">{{ $event->bus ? $event->bus->name : 'Nie przypisano' }}</span>
            </div>
        </div>

        <div class="border-t pt-4">
            <h3 class="font-bold text-gray-700 mb-2">Hotele i Kontakty</h3>
            @if($event->eventTemplate && $event->eventTemplate->hotelDays->isNotEmpty())
               <!-- TODO: logic to show hotels if linked correctly via template pivot or event pivot -->
               <p class="text-sm text-gray-500">Lista hoteli z szablonu:</p>
               <ul class="text-sm space-y-2 mt-2">
                   @foreach($event->eventTemplate->hotelDays as $hotelDay)
                        <!-- Assuming relation to Hotel exists eventually, currently simple display -->
                        <li class="p-2 bg-blue-50 rounded">
                            Day {{ $hotelDay->day }}: Hotele... 
                            <span class="text-xs text-gray-400 block">(Szczegóły wkrótce)</span>
                        </li>
                   @endforeach
               </ul>
            @else
                <p class="text-sm text-gray-500">Brak informacji o hotelach.</p>
            @endif
        </div>
        
        <div class="border-t pt-4 mt-4 grid grid-cols-2 gap-4">
            <a href="{{ route('pilot.report.rooming', $event->id) }}" target="_blank" class="text-center p-2 bg-gray-100 rounded text-blue-700 text-xs font-bold hover:bg-gray-200">
                📄 Rooming List (PDF)
            </a>
            <a href="{{ route('pilot.report.manifest', $event->id) }}" target="_blank" class="text-center p-2 bg-gray-100 rounded text-blue-700 text-xs font-bold hover:bg-gray-200">
                ✈️ Flight Manifest (PDF)
            </a>
        </div>
    </div>
    
    <div class="grid grid-cols-2 gap-4">
        <a href="{{ route('pilot.participants', $event->id) }}" class="bg-blue-600 text-white p-4 rounded shadow text-center block hover:bg-blue-700">
            <span class="block text-2xl font-bold mb-1">{{ $event->participant_count }}</span>
            <span class="text-xs uppercase tracking-wide">Pasażerów</span>
        </a>
        <a href="{{ route('pilot.payments', $event->id) }}" class="bg-indigo-600 text-white p-4 rounded shadow text-center block hover:bg-indigo-700">
            <span class="block text-2xl font-bold mb-1">+</span>
            <span class="text-xs uppercase tracking-wide">Wpłaty na miejscu</span>
        </a>
        <a href="{{ route('pilot.expenses', $event->id) }}" class="bg-green-600 text-white p-4 rounded shadow text-center block hover:bg-green-700">
            <span class="block text-2xl font-bold mb-1">+</span>
            <span class="text-xs uppercase tracking-wide">Dodaj Koszt</span>
        </a>
    </div>
@endsection
