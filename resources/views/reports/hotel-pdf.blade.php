<!DOCTYPE html>
<html lang="pl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Agenda dla hotelu - {{ $event->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 16pt; font-weight: bold; }
        .subtitle { font-size: 12pt; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #000; padding: 5px; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .section-title { font-size: 12pt; font-weight: bold; margin: 15px 0 5px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8pt; border-top: 1px solid #000; padding-top: 5px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="footer">
        Biuro Podróży RAFA, tel.: +48 606 102 243, www.bprafa.pl, NIP: 716-250-87-61
    </div>

    <div class="header">
        <div class="title">AGENDA DLA HOTELU</div>
        <div class="subtitle">{{ $event->name }}</div>
        <div>{{ $event->start_date?->format('d.m.Y') }} - {{ $event->end_date?->format('d.m.Y') }}</div>
    </div>

    <table>
        <tr>
            <th width="30%">Termin</th>
            <td>{{ $event->start_date?->format('d.m.Y') }} - {{ $event->end_date?->format('d.m.Y') }}</td>
        </tr>
        <tr>
            <th>Zamawiający</th>
            <td>Biuro Podróży RAFA<br>tel.: +48 606 102 243, +48 660 699 210</td>
        </tr>
        <tr>
            <th>Liczba uczestników</th>
            <td>{{ $event->participant_count }}
                @if($event->staff_count > 0)(w tym {{ $event->staff_count }} opiekunów)@endif
                <br>+ pilot i kierowca
            </td>
        </tr>
        <tr>
            <th>Pilot</th>
            <td>{{ $event->contact?->name ?? 'Do ustalenia' }}<br>
                @if($event->contact?->phone)Tel.: {{ $event->contact->phone }}@endif
            </td>
        </tr>
    </table>

    <div class="section-title">Notatki dla hotelu</div>
    <div style="border: 1px solid #000; padding: 10px; min-height: 80px;">
        {!! $event->hotel_notes ?? 'Brak szczególnych uwag' !!}
    </div>

    @if($event->programPoints && $event->programPoints->count() > 0)
    <div class="section-title">Program pobytu</div>
    <table>
        <thead>
            <tr>
                <th width="20%">Data/Godzina</th>
                <th width="40%">Program</th>
                <th width="40%">Uwagi/Rezerwacje</th>
            </tr>
        </thead>
        <tbody>
            @foreach($event->programPoints->sortBy('order') as $programPoint)
            <tr>
                <td>{{ $programPoint->start_time }}<br>{{ $programPoint->duration_minutes }} min</td>
                <td><strong>{{ $programPoint->name }}</strong><br>{{ $programPoint->description }}</td>
                <td>{{ $programPoint->notes }}</td>
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
                <th width="50%">Plik</th>
                <th width="50%">Link</th>
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
