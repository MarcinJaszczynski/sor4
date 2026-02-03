<x-filament-panels::page>
    <div class="flex justify-between items-center mb-4">
        <div class="flex space-x-2">
            <button wire:click="prevMonth" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 font-medium text-sm">&laquo; Poprzedni</button>
            <button wire:click="currentMonth" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-50 font-medium text-sm">Dzisiaj</button>
            <button wire:click="nextMonth" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 font-medium text-sm">Następny &raquo;</button>
        </div>
        
        <div class="flex flex-col items-center">
             <span class="text-2xl font-bold text-gray-800">{{ $currentDate->translatedFormat('F Y') }}</span>
        </div>

        <x-filament::button wire:click="downloadPdf" icon="heroicon-o-printer" color="primary">
            Pobierz PDF
        </x-filament::button>
    </div>

    <div class="flex space-x-6 mb-4 text-xs text-gray-600 bg-white p-2 rounded shadow-sm border border-gray-100 w-fit">
        <div class="flex items-center"><span class="w-3 h-3 bg-blue-500 rounded-sm mr-2"></span> Wyjazd (Autokar)</div>
        <div class="flex items-center"><span class="w-3 h-3 bg-purple-500 rounded-sm mr-2"></span> Wyjazd (Pilot)</div>
        <div class="flex items-center"><span class="w-3 h-3 bg-red-500 border border-red-700 rounded-sm mr-2"></span> Konflikt</div>
        <div class="flex items-center"><span class="w-3 h-3 bg-gray-100 border border-gray-200 rounded-sm mr-2"></span> Weekend</div>
    </div>

    <div class="overflow-x-auto bg-white rounded shadow border border-gray-200">
        <style>
            .schedule-header { min-width: 31px; text-align: center; font-size: 10px; padding: 2px; border-right: 1px solid #ddd; background: #f9fafb; font-weight: bold; flex: 0 0 31px; }
            .resource-row { border-bottom: 1px solid #eee; display: flex; width: max-content; min-width: 100%; }
            .resource-name { width: 220px; padding: 10px; font-weight: bold; border-right: 2px solid #ddd; background: #fff; position: sticky; left: 0; z-index: 20; flex-shrink: 0; }
            .event-bar { 
                position: absolute; 
                top: 5px; 
                height: 30px; 
                background-color: #3b82f6; 
                color: white; 
                font-size: 10px; 
                overflow: hidden; 
                white-space: nowrap; 
                padding: 0 4px; 
                line-height: 30px;
                border-radius: 4px;
                z-index: 10;
                opacity: 0.9;
                pointer-events: auto;
            }
            .event-conflict { background-color: #ef4444 !important; border: 2px solid #991b1b; z-index: 15; }
            .weekend { background-color: #f3f4f6; }
            .grid-container { display: flex; flex-direction: row; flex-grow: 1; position: relative; }
            .schedule-cell { width: 31px; min-width: 31px; height: 40px; border-right: 1px solid #eee; position: relative; flex: 0 0 31px; }
        </style>

        <div class="overflow-x-auto" style="position: relative;">
            <!-- Header Row -->
            <div class="flex text-xs border-b border-gray-300 w-max min-w-full">
                <div class="resource-name bg-gray-100">Zasób</div>
                @for ($day = 1; $day <= $daysInMonth; $day++)
                    @php 
                        $date = $currentDate->copy()->day($day);
                        $isWeekend = $date->isWeekend();
                    @endphp
                    <div class="schedule-header {{ $isWeekend ? 'bg-gray-200' : '' }}">
                        {{ $day }} <br> {{ $date->translatedFormat('D') }}
                    </div>
                @endfor
            </div>

            <!-- Buses -->
            <div class="bg-gray-50 p-2 font-bold text-gray-700 border-b w-max min-w-full">Autokary</div>
            @foreach($buses as $bus)
                <div class="resource-row relative">
                    <div class="resource-name flex flex-col justify-center">
                        <span>{{ $bus->name }}</span>
                        <span class="text-xs text-gray-400 font-normal">{{ $bus->description }}</span>
                    </div>
                    
                    <div class="grid-container">
                        <!-- Grid Lines -->
                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            @php $isWeekend = $currentDate->copy()->day($day)->isWeekend(); @endphp
                            <div class="schedule-cell {{ $isWeekend ? 'weekend' : '' }}"></div>
                        @endfor

                        <!-- Events -->
                        @foreach($bus->events_in_range as $event)
                            @php
                                if (!$event->start_date) continue;
                                $endDate = $event->end_date ?? $event->start_date;
                                
                                $startOfMonth = $currentDate->copy()->startOfMonth();
                                $endOfMonth = $currentDate->copy()->endOfMonth();

                                if ($endDate < $startOfMonth || $event->start_date > $endOfMonth) continue;

                                $viewStart = $event->start_date < $startOfMonth ? 1 : $event->start_date->day;
                                $viewEnd = $endDate > $endOfMonth ? $daysInMonth : $endDate->day;

                                $duration = $viewEnd - $viewStart + 1;
                                $left = ($viewStart - 1) * 31; 
                                $width = $duration * 31;
                                
                                $isConflict = in_array($event->id, $bus->conflicts);
                            @endphp
                            <a href="{{ \App\Filament\Resources\EventResource::getUrl('edit', ['record' => $event->id]) }}" 
                               target="_blank"
                               class="event-bar {{ $isConflict ? 'event-conflict' : '' }} hover:opacity-100 shadow-sm"
                               style="left: {{ $left }}px; width: {{ $width - 2 }}px;"
                               title="{{ $event->name }} ({{ $event->start_date->format('d.m') }} - {{ $endDate->format('d.m') }})">
                                {{ $event->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <!-- Pilots -->
            <div class="bg-gray-50 p-2 font-bold text-gray-700 border-b border-t mt-4 w-max min-w-full">Piloci</div>
            @foreach($pilots as $pilot)
                <div class="resource-row relative">
                    <div class="resource-name flex flex-col justify-center">
                        <span>{{ $pilot->name }}</span>
                    </div>
                    
                    <div class="grid-container">
                        <!-- Grid Lines -->
                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            @php $isWeekend = $currentDate->copy()->day($day)->isWeekend(); @endphp
                            <div class="schedule-cell {{ $isWeekend ? 'weekend' : '' }}"></div>
                        @endfor

                        <!-- Events -->
                        @foreach($pilot->events_in_range as $event)
                            @php
                                if (!$event->start_date) continue;
                                $endDate = $event->end_date ?? $event->start_date;

                                $startOfMonth = $currentDate->copy()->startOfMonth();
                                $endOfMonth = $currentDate->copy()->endOfMonth();

                                if ($endDate < $startOfMonth || $event->start_date > $endOfMonth) continue;

                                $viewStart = $event->start_date < $startOfMonth ? 1 : $event->start_date->day;
                                $viewEnd = $endDate > $endOfMonth ? $daysInMonth : $endDate->day;

                                $duration = $viewEnd - $viewStart + 1;
                                $left = ($viewStart - 1) * 31; 
                                $width = $duration * 31;
                                
                                $isConflict = in_array($event->id, $pilot->conflicts);
                            @endphp
                            <a href="{{ \App\Filament\Resources\EventResource::getUrl('edit', ['record' => $event->id]) }}" 
                               target="_blank"
                               class="event-bar {{ $isConflict ? 'event-conflict' : '' }} hover:opacity-100 shadow-sm bg-purple-500"
                               style="left: {{ $left }}px; width: {{ $width - 2 }}px;"
                               title="{{ $event->name }} ({{ $event->start_date->format('d.m') }} - {{ $endDate->format('d.m') }})">
                                {{ $event->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
