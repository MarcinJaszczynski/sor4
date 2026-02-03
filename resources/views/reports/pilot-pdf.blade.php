<!DOCTYPE html>
<html lang="pl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Teczka Pilota - {{ $event->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 16pt; font-weight: bold; }
        .subtitle { font-size: 12pt; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .section-title { font-size: 12pt; font-weight: bold; margin: 15px 0 5px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8pt; border-top: 1px solid #000; padding-top: 5px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="footer">
        Biuro Podróży RAFA, ul. M. Konopnickiej 6, 00-491 Warszawa, tel.: +48 606 102 243, NIP: 716-250-87-61
    </div>

    <div class="header">
        <div class="title">TECZKA PILOTA</div>
        <div class="subtitle">{{ $event->public_code }} - {{ $event->name }}</div>
        <div>{{ $event->start_date?->format('d.m.Y') }} - {{ $event->end_date?->format('d.m.Y') }}</div>
    </div>

    <div class="section-title">Podstawowe informacje</div>
    <table>
        <tr>
            <th width="30%">Termin</th>
            <td>{{ $event->start_date?->format('d.m.Y') }} - {{ $event->end_date?->format('d.m.Y') }} ({{ $event->duration_days }} dni)</td>
        </tr>
        <tr>
            <th>Zamawiający</th>
            <td>{{ $event->client_name }}<br>
                @if($event->client_phone)
                    Tel.: {{ $event->client_phone }}<br>
                @endif
                @if($event->client_email)
                    Email: {{ $event->client_email }}
                @endif
            </td>
        </tr>
        <tr>
            <th>Liczba uczestników</th>
            <td>{{ $event->participant_count }}
                @if($event->staff_count > 0)
                    + {{ $event->staff_count }} opiekun(ów)
                @endif
                @if($event->gratis_count > 0)
                    + {{ $event->gratis_count }} gratis
                @endif
            </td>
        </tr>
        <tr>
            <th>Podstawienie autobusu</th>
            <td>{{ $event->pickup_datetime?->format('H:i d.m.Y') }}<br>{{ $event->pickup_place }}</td>
        </tr>
        <tr>
            <th>Autobus</th>
            <td>{{ $event->bus?->name }} - {{ $event->bus?->registration_number }}</td>
        </tr>
    </table>

    <div class="section-title">Notatki dla pilota</div>
    <div style="border: 1px solid #000; padding: 10px; min-height: 100px;">
        {!! $event->pilot_notes ?? 'Brak notatek' !!}
    </div>

    @if($event->programPoints && $event->programPoints->count() > 0)
    <div class="page-break"></div>
    <div class="section-title">Program wycieczki</div>
    <table>
        <thead>
            <tr>
                <th width="15%">Data/Godzina</th>
                <th width="35%">Punkt programu</th>
                <th width="25%">Lokalizacja</th>
                <th width="25%">Szczegóły</th>
            </tr>
        </thead>
        <tbody>
            @foreach($event->programPoints->sortBy('order') as $programPoint)
            <tr>
                <td>{{ $programPoint->start_time }}<br>{{ $programPoint->duration_minutes }} min</td>
                <td><strong>{{ $programPoint->name }}</strong><br>{{ $programPoint->description }}</td>
                <td>{{ $programPoint->city }}</td>
                <td>{{ $programPoint->notes }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($event->hotelDays && $event->hotelDays->count() > 0)
    <div class="page-break"></div>
    <div class="section-title">Noclegi</div>
    <table>
        <thead>
            <tr>
                <th width="15%">Dzień</th>
                <th width="85%">Pokoje hotelowe</th>
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
    <div class="page-break"></div>
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
                <td><a href="{{ url('storage/' . $doc->file_path) }}">{{ url('storage/' . $doc->file_path) }}</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($event->costs && $event->costs->count() > 0)
    <div class="page-break"></div>
    <div class="section-title">Koszty i rozliczenia</div>
    <table>
        <thead>
            <tr>
                <th width="40%">Pozycja</th>
                <th width="20%">Kategoria</th>
                <th width="20%">Kwota planowana</th>
                <th width="20%">Zapłacono</th>
            </tr>
        </thead>
        <tbody>
            @php $totalPlanned = 0; $totalPaid = 0; @endphp
            @foreach($event->costs as $cost)
            @php 
                $totalPlanned += $cost->amount ?? 0;
                $totalPaid += $cost->paid_amount ?? 0;
            @endphp
            <tr>
                <td>{{ $cost->name }}</td>
                <td>{{ $cost->category }}</td>
                <td style="text-align: right;">{{ number_format($cost->amount ?? 0, 2) }} {{ $cost->currency }}</td>
                <td style="text-align: right;">{{ number_format($cost->paid_amount ?? 0, 2) }} {{ $cost->currency }}</td>
            </tr>
            @endforeach
            <tr style="font-weight: bold;">
                <td colspan="2">RAZEM (PLN)</td>
                <td style="text-align: right;">{{ number_format($totalPlanned, 2) }}</td>
                <td style="text-align: right;">{{ number_format($totalPaid, 2) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    <div style="margin-top: 30px; text-align: center; font-size: 8pt;">
        <p>Dokument wygenerowany: {{ now()->format('d.m.Y H:i') }}</p>
        <p>System BP RAFA</p>
    </div>
</body>
</html>
