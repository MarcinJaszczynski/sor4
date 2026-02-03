<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
    .page-break { page-break-after: always; }
    .schedule-container { width: 100%; position: relative; border: 1px solid #ccc; }
    .row { position: relative; height: 40px; border-bottom: 1px solid #eee; page-break-inside: avoid; }
    .resource-col { position: absolute; left: 0; top: 0; width: 150px; height: 40px; padding: 5px; font-weight: bold; border-right: 1px solid #ccc; background: #eee; z-index: 10; overflow: hidden; line-height: 1.2; }
    .timeline-col { position: absolute; left: 150px; top: 0; right: 0; height: 40px; }
    .day-cell { position: absolute; top: 0; height: 40px; border-right: 1px solid #eee; width: 30px; text-align: center; font-size: 8px; color: #999; }
    .event-bar { position: absolute; top: 5px; height: 30px; background: #3b82f6; color: white; border-radius: 3px; padding: 2px; overflow: hidden; white-space: nowrap; font-size: 9px; opacity: 0.9; }
    .event-conflict { background: #ef4444 !important; border: 1px solid #991b1b; z-index: 5; }
    .weekend { background: #f3f4f6; }
    .bg-purple { background-color: #9333ea !important; } /* Purple for pilots */
</style>
</head>
<body>
    <h2>Grafik Zajętości - {{ $currentDate->translatedFormat('F Y') }}</h2>
    
    <div class="schedule-container">
        <!-- Header -->
        <div class="row" style="height: 20px; background: #ddd; font-weight: bold;">
             <div class="resource-col" style="height: 20px; background: #ddd;">Zasób</div>
             <div class="timeline-col" style="height: 20px;">
                @for ($day = 1; $day <= $daysInMonth; $day++)
                    <div class="day-cell" style="left: {{ ($day - 1) * 30 }}px; height: 20px;">{{ $day }}</div>
                @endfor
             </div>
        </div>

        <!-- Buses -->
        <div style="background: #eee; padding: 5px; font-weight: bold;">Autokary</div>
        @foreach($buses as $bus)
            <div class="row">
                <div class="resource-col">
                    {{ $bus->name }}
                    <div style="font-size: 8px; font-weight: normal; color: #666;">{{ $bus->description }}</div>
                </div>
                <div class="timeline-col">
                     <!-- Grid -->
                    @for ($day = 1; $day <= $daysInMonth; $day++)
                        @php $isWeekend = $currentDate->copy()->day($day)->isWeekend(); @endphp
                        <div class="day-cell {{ $isWeekend ? 'weekend' : '' }}" style="left: {{ ($day - 1) * 30 }}px;"></div>
                    @endfor
                    
                    <!-- Events -->
                    @foreach($bus->events_in_range as $event)
                        @php
                            if (!$event->start_date || !$event->end_date) continue;
                            $startDay = $event->start_date->month == $currentDate->month ? $event->start_date->day : 1;
                            $endDay = $event->end_date->month == $currentDate->month ? $event->end_date->day : $daysInMonth;
                            
                            if ($event->end_date < $currentDate->startOfMonth()) continue;
                            if ($event->start_date > $currentDate->endOfMonth()) continue;
                            
                            $startDay = max(1, $startDay);
                            $endDay = min($daysInMonth, $endDay);
                            $duration = $endDay - $startDay + 1;
                            $left = ($startDay - 1) * 30;
                            $width = $duration * 30;
                            
                            $isConflict = in_array($event->id, $bus->conflicts);
                        @endphp
                        <div class="event-bar {{ $isConflict ? 'event-conflict' : '' }}"
                             style="left: {{ $left }}px; width: {{ $width - 2 }}px;">
                             {{ $event->name }}
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <!-- Pilots -->
        <div style="background: #eee; padding: 5px; font-weight: bold; page-break-before: auto;">Piloci</div>
        @foreach($pilots as $pilot)
            <div class="row">
                <div class="resource-col">
                    {{ $pilot->name }}
                </div>
                <div class="timeline-col">
                     <!-- Grid -->
                    @for ($day = 1; $day <= $daysInMonth; $day++)
                        @php $isWeekend = $currentDate->copy()->day($day)->isWeekend(); @endphp
                        <div class="day-cell {{ $isWeekend ? 'weekend' : '' }}" style="left: {{ ($day - 1) * 30 }}px;"></div>
                    @endfor
                    
                    <!-- Events -->
                    @foreach($pilot->events_in_range as $event)
                        @php
                            if (!$event->start_date || !$event->end_date) continue;
                            $startDay = $event->start_date->month == $currentDate->month ? $event->start_date->day : 1;
                            $endDay = $event->end_date->month == $currentDate->month ? $event->end_date->day : $daysInMonth;
                            if ($event->end_date < $currentDate->startOfMonth()) continue;
                                if ($event->start_date > $currentDate->endOfMonth()) continue;
                            $startDay = max(1, $startDay);
                            $endDay = min($daysInMonth, $endDay);
                            $duration = $endDay - $startDay + 1;
                            $left = ($startDay - 1) * 30;
                            $width = $duration * 30;
                            $isConflict = in_array($event->id, $pilot->conflicts);
                        @endphp
                        <div class="event-bar {{ $isConflict ? 'event-conflict' : '' }} bg-purple"
                             style="left: {{ $left }}px; width: {{ $width - 2 }}px;">
                             {{ $event->name }}
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>
