@extends('pilot.layout')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Lista Pasażerów</h2>
        <span class="text-xs bg-gray-200 px-2 py-1 rounded ml-2">{{ $participants->count() }} os.</span>
    </div>

    <div class="space-y-3">
        @foreach($participants as $p)
            <div x-data="{ open: false }" class="bg-white rounded shadow text-sm border-l-4 {{ $p->diet_info ? 'border-red-500' : 'border-green-500' }}">
                <div class="p-4 flex justify-between items-center cursor-pointer" @click="open = !open">
                    <div>
                        <div class="font-bold text-gray-800">{{ $p->last_name }} {{ $p->first_name }}</div>
                        <div class="text-xs text-gray-500">M. {{ $p->seat_number ?: '-' }} | {{ $p->phone ?: 'Brak tel.' }}</div>
                    </div>
                    <div>
                        @if($p->diet_info)
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        @endif
                        <svg class="w-5 h-5 text-gray-400 transform transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                <div x-show="open" class="p-4 border-t bg-gray-50 text-xs">
                    <form action="{{ route('pilot.participants.update', [$event->id, $p->id]) }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-gray-500 mb-1">Telefon</label>
                                <a href="tel:{{ $p->phone }}" class="text-blue-600 underline text-base">{{ $p->phone ?: '-' }}</a>
                            </div>
                            <div>
                                <label class="block text-gray-500 mb-1">Miejsce w autokarze</label>
                                <input type="text" name="seat_number" value="{{ $p->seat_number }}" class="w-full border rounded p-1">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="block text-gray-500 mb-1">Diety / Uwagi / Alergie</label>
                            <textarea name="diet_info" class="w-full border rounded p-2" rows="2">{{ $p->diet_info }}</textarea>
                        </div>
                        <div class="mt-3 text-right">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded text-xs font-bold">Zapisz</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
    
    <!-- Alpine.js for interactions -->
    <script src="//unpkg.com/alpinejs" defer></script>
@endsection
