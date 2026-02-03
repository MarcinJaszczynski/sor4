<!DOCTYPE html>
<html lang="pl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Informacje dla kierowcy - {{ $event->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 18pt; font-weight: bold; }
        .subtitle { font-size: 13pt; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #000; padding: 8px; }
        th { background-color: #f0f0f0; font-weight: bold; width: 30%; }
        .section-title { font-size: 13pt; font-weight: bold; margin: 15px 0 5px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8pt; border-top: 1px solid #000; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="footer">
        Biuro Podróży RAFA, tel.: +48 606 102 243, www.bprafa.pl, NIP: 716-250-87-61
    </div>

    <div class="header">
        <div class="title">INFORMACJE DLA KIEROWCY</div>
        <div class="subtitle">{{ $event->public_code }} - {{ $event->name }}</div>
        <div>{{ $event->start_date?->format('d.m.Y') }} - {{ $event->end_date?->format('d.m.Y') }}</div>
    </div>

    <table>
        <tr>
            <th>Podstawienie autobusu</th>
            <td>{{ $event->pickup_datetime?->format('H:i d.m.Y') }}</td>
        </tr>
        <tr>
            <th>Miejsce podstawienia</th>
            <td>{{ $event->pickup_place ?? 'Do ustalenia' }}</td>
        </tr>
        <tr>
            <th>Odjazd</th>
            <td>{{ $event->start_date?->format('H:i d.m.Y') }}</td>
        </tr>
        <tr>
            <th>Powrót</th>
            <td>{{ $event->end_date?->format('H:i d.m.Y') }}</td>
        </tr>
        <tr>
            <th>Liczba uczestników</th>
            <td>{{ $event->participant_count }}
                @if($event->staff_count > 0) + {{ $event->staff_count }} opiekun(ów)@endif
                + 1 pilot
            </td>
        </tr>
        <tr>
            <th>Autobus</th>
            <td>{{ $event->bus?->name }} - {{ $event->bus?->registration_number }}</td>
        </tr>
    </table>

    <div class="section-title">Trasa / Dystans</div>
    <table>
        <tr>
            <th>Transfer (km)</th>
            <td>{{ $event->transfer_km ?? 0 }} km</td>
        </tr>
        <tr>
            <th>Program (km)</th>
            <td>{{ $event->program_km ?? 0 }} km</td>
        </tr>
        <tr>
            <th>Łącznie</th>
            <td><strong>{{ ($event->transfer_km ?? 0) + ($event->program_km ?? 0) }} km</strong></td>
        </tr>
    </table>

    <div class="section-title">Notatki dla kierowcy</div>
    <div style="border: 1px solid #000; padding: 10px; min-height: 120px;">
        {!! $event->driver_notes ?? 'Brak szczególnych uwag' !!}
    </div>

    @if($event->hotelDays && $event->hotelDays->count() > 0)
    <div style="page-break-before: always;"></div>
    <div class="section-title">Noclegi na trasie</div>
    <table>
        <thead>
            <tr>
                <th width="20%">Dzień</th>
                <th width="80%">Pokoje</th>
            </tr>
        </thead>
        <tbody>
            @foreach($event->hotelDays->sortBy('day') as $hotelDay)
            <tr>
                <td>Dzień {{ $hotelDay->day }}</td>
                <td>
                    @php
                        $roomIds = array_merge(
                            $hotelDay->hotel_room_ids_qty ?? [],
                            $hotelDay->hotel_room_ids_gratis ?? [],
                            $hotelDay->hotel_room_ids_staff ?? [],
                            $hotelDay->hotel_room_ids_driver ?? []
                        );
                        $rooms = \App\Models\HotelRoom::whereIn('id', $roomIds)->get();
                    @endphp
                    @foreach($rooms as $room)
                        {{ $room->name }} ({{ $room->people_count }} os.)
                        @if(!$loop->last), @endif
                    @endforeach
                    @if($hotelDay->notes)
                        <br><small>{{ $hotelDay->notes }}</small>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($event->documents && $event->documents->count() > 0)
    <div style="page-break-before: always;"></div>
    <div class="section-title">Pliki załączone</div>
    <table>
        <thead>
            <tr>
                <th width="60%">Nazwa pliku</th>
                <th width="40%">Link</th>
            </tr>
        </thead>
        <tbody>
            @foreach($event->documents as $doc)
            <tr>
                <td>{{ $doc->name }}</td>
                <td style="font-size: 8pt; word-break: break-all;"><a href="{{ url('storage/' . $doc->file_path) }}">{{ url('storage/' . $doc->file_path) }}</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</body>
</html>
