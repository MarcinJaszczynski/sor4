<!DOCTYPE html>
<html lang="pl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Teczka imprezy - {{ $event->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; }
        .header { text-align: center; margin-bottom: 15px; }
        .title { font-size: 18pt; font-weight: bold; }
        .subtitle { font-size: 13pt; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #000; padding: 4px; font-size: 9pt; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .section-title { font-size: 13pt; font-weight: bold; margin: 15px 0 5px; background-color: #333; color: white; padding: 5px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 7pt; border-top: 1px solid #000; padding-top: 5px; }
        .page-break { page-break-after: always; }
        .info-box { border: 1px solid #ccc; padding: 10px; margin: 10px 0; background-color: #f9f9f9; }
    </style>
</head>
<body>
    <div class="footer">
        Biuro Podróży RAFA, ul. M. Konopnickiej 6, 00-491 Warszawa, tel.: +48 606 102 243, NIP: 716-250-87-61
    </div>

    <div class="header">
        <div class="title">TECZKA IMPREZY</div>
        <div class="subtitle">{{ $event->public_code }} - {{ $event->name }}</div>
        <div>{{ $event->start_date?->format('d.m.Y') }} - {{ $event->end_date?->format('d.m.Y') }} ({{ $event->duration_days }} dni)</div>
    </div>

    <div class="section-title">1. INFORMACJE PODSTAWOWE</div>
    <table>
        <tr>
            <th width="25%">Kod imprezy</th>
            <td width="25%">{{ $event->public_code }}</td>
            <th width="25%">Status</th>
            <td width="25%">{{ ucfirst($event->status) }}</td>
        </tr>
        <tr>
            <th>Termin</th>
            <td>{{ $event->start_date?->format('d.m.Y H:i') }} - {{ $event->end_date?->format('d.m.Y H:i') }}</td>
            <th>Czas trwania</th>
            <td>{{ $event->duration_days }} dni</td>
        </tr>
        <tr>
            <th>Zamawiający</th>
            <td colspan="3">
                {{ $event->client_name }}<br>
                @if($event->client_phone)Tel.: {{ $event->client_phone }}<br>@endif
                @if($event->client_email)Email: {{ $event->client_email }}@endif
            </td>
        </tr>
        <tr>
            <th>Uczestnicy</th>
            <td>{{ $event->participant_count }} osób</td>
            <th>Opiekunowie</th>
            <td>{{ $event->staff_count }}</td>
        </tr>
        <tr>
            <th>Gratis</th>
            <td>{{ $event->gratis_count }}</td>
            <th>Autobus</th>
            <td>{{ $event->bus?->name }} ({{ $event->bus?->registration_number }})</td>
        </tr>
        <tr>
            <th>Podstawienie</th>
            <td>{{ $event->pickup_datetime?->format('H:i d.m.Y') }}</td>
            <th>Miejsce</th>
            <td>{{ $event->pickup_place }}</td>
        </tr>
    </table>

    <div class="section-title">2. TRASA I PRZEBIEG</div>
    <table>
        <tr>
            <th width="25%">Transfer (km)</th>
            <td width="25%">{{ $event->transfer_km ?? 0 }}</td>
            <th width="25%">Program (km)</th>
            <td width="25%">{{ $event->program_km ?? 0 }}</td>
        </tr>
        <tr>
            <th>Łączny dystans</th>
            <td colspan="3"><strong>{{ ($event->transfer_km ?? 0) + ($event->program_km ?? 0) }} km</strong></td>
        </tr>
    </table>

    @if($event->programPoints && $event->programPoints->count() > 0)
    <div class="section-title">3. PROGRAM SZCZEGÓŁOWY</div>
    <table>
        <thead>
            <tr>
                <th width="12%">Dzień</th>
                <th width="12%">Godzina</th>
                <th width="30%">Punkt programu</th>
                <th width="20%">Lokalizacja</th>
                <th width="26%">Szczegóły</th>
            </tr>
        </thead>
        <tbody>
            @foreach($event->programPoints->sortBy('order') as $index => $programPoint)
            <tr>
                <td>Dzień {{ ceil(($index + 1) / 5) }}</td>
                <td>{{ $programPoint->start_time }}</td>
                <td><strong>{{ $programPoint->name }}</strong></td>
                <td>{{ $programPoint->city }}</td>
                <td style="font-size: 8pt;">{{ Str::limit($programPoint->notes, 60) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($event->hotelDays && $event->hotelDays->count() > 0)
    <div class="page-break"></div>
    <div class="section-title">4. NOCLEGI</div>
    <table>
        <thead>
            <tr>
                <th width="20%">Dzień</th>
                <th width="80%">Pokoje hotelowe</th>
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
                        <strong>{{ $room->name }}</strong> ({{ $room->people_count }} os., {{ number_format($room->price, 2) }} {{ $room->currency }})
                        @if(!$loop->last)<br>@endif
                    @endforeach
                    @if($hotelDay->notes)
                        <br><em style="font-size: 9pt;">{{ $hotelDay->notes }}</em>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="section-title">5. ROZLICZENIE FINANSOWE</div>
    <table>
        <thead>
            <tr>
                <th width="35%">Pozycja</th>
                <th width="15%">Kategoria</th>
                <th width="15%">Kwota planowana</th>
                <th width="15%">Zapłacono</th>
                <th width="20%">Waluta</th>
            </tr>
        </thead>
        <tbody>
            @php $totalPlanned = 0; $totalPaid = 0; @endphp
            @foreach($event->costs ?? [] as $cost)
            @php 
                $totalPlanned += $cost->amount ?? 0;
                $totalPaid += $cost->paid_amount ?? 0;
            @endphp
            <tr>
                <td>{{ $cost->name }}</td>
                <td>{{ $cost->category }}</td>
                <td style="text-align: right;">{{ number_format($cost->amount ?? 0, 2) }}</td>
                <td style="text-align: right;">{{ number_format($cost->paid_amount ?? 0, 2) }}</td>
                <td>{{ $cost->currency }}</td>
            </tr>
            @endforeach
            <tr style="font-weight: bold; background-color: #e0e0e0;">
                <td colspan="2">RAZEM (PLN)</td>
                <td style="text-align: right;">{{ number_format($totalPlanned, 2) }}</td>
                <td style="text-align: right;">{{ number_format($totalPaid, 2) }}</td>
                <td>{{ number_format($totalPlanned - $totalPaid, 2) }} (różnica)</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">6. NOTATKI</div>
    <div class="info-box">
        <strong>Notatki biurowe:</strong><br>
        {!! $event->office_notes ?? 'Brak' !!}
    </div>
    <div class="info-box">
        <strong>Notatki dla pilota:</strong><br>
        {!! $event->pilot_notes ?? 'Brak' !!}
    </div>

    @if($event->documents && $event->documents->count() > 0)
    <div class="page-break"></div>
    <div class="section-title">7. ZAŁĄCZNIKI</div>
    <table>
        <thead>
            <tr>
                <th width="50%">Nazwa pliku</th>
                <th width="20%">Typ</th>
                <th width="30%">Link</th>
            </tr>
        </thead>
        <tbody>
            @foreach($event->documents as $doc)
            <tr>
                <td>{{ $doc->name }}</td>
                <td>{{ $doc->type }}</td>
                <td style="font-size: 7pt; word-break: break-all;"><a href="{{ url('storage/' . $doc->file_path) }}">{{ url('storage/' . $doc->file_path) }}</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div style="margin-top: 30px; text-align: center; font-size: 8pt;">
        <p>Dokument wygenerowany: {{ now()->format('d.m.Y H:i') }}</p>
        <p>System BP RAFA - Zarządzanie imprezami</p>
    </div>
</body>
</html>
