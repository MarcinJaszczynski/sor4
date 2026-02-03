@extends('pilot.layout')

@section('content')
    <h2 class="text-xl font-bold mb-4">Moje Wyjazdy</h2>
    
    @if($events->isEmpty())
        <div class="p-4 bg-white rounded shadow text-center text-gray-500">
            Brak przypisanych wyjazdów.
        </div>
    @else
        <div class="space-y-4">
            @foreach($events as $event)
                <a href="{{ route('pilot.event', $event->id) }}" class="block bg-white p-4 rounded shadow hover:bg-gray-50 transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-lg text-blue-900">{{ $event->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $event->start_date->format('d.m.Y') }} - {{ $event->end_date->format('d.m.Y') }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $event->startPlace ? $event->startPlace->name : 'Brak miejsca startu' }}</p>
                        </div>
                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                            {{ $event->status }}
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection
