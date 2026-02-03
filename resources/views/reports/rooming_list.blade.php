<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rooming List</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
        th { background-color: #f0f0f0; }
        h1 { font-size: 16px; margin-bottom: 5px; }
        .details { margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Rooming List</h1>
    <div class="details">
        <strong>Impreza:</strong> {{ $event->name }}<br>
        <strong>Termin:</strong> {{ $event->start_date->format('d.m.Y') }} - {{ $event->end_date->format('d.m.Y') }}<br>
        <strong>Liczba osób:</strong> {{ $event->participant_count }}
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">Lp.</th>
                <th width="10%">Typ Pokoju</th>
                <th width="40%">Nazwiska i Imiona</th>
                <th width="45%">Uwagi (Diety, Łóżka)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groupedParticipants as $index => $group)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $group['room_type'] ?: 'DBL' }}</td>
                    <td>
                        @foreach($group['participants'] as $p)
                            {{ $loop->iteration }}. {{ mb_strtoupper($p->last_name) }} {{ $p->first_name }}<br>
                        @endforeach
                    </td>
                    <td>
                        {{ $group['notes'] }}
                        @foreach($group['participants'] as $p)
                            @if($p->diet_info)
                                <br><strong>{{ $p->first_name }}:</strong> {{ $p->diet_info }}
                            @endif
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
