<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Flight Manifest Pl</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
        th { background-color: #f0f0f0; }
        h1 { font-size: 16px; margin-bottom: 5px; }
    </style>
</head>
<body>
    <h1>Flight Manifest (Lista Pasażerów)</h1>
    <div>
        <strong>Impreza:</strong> {{ $event->name }} | 
        <strong>Termin:</strong> {{ $event->start_date->format('d.m.Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Lp.</th>
                <th>Nazwisko (Surname)</th>
                <th>Imię (Given Name)</th>
                <th>Płeć (Sex)</th>
                <th>Data Ur. (DOB)</th>
                <th>Narodowość (NAT)</th>
                <th>Typ Dok.</th>
                <th>Numer Dok.</th>
                <th>Ważność Dok.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($participants as $p)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ mb_strtoupper($p->last_name) }}</td>
                    <td>{{ mb_strtoupper($p->first_name) }}</td>
                    <td>{{ $p->gender ?: '-' }}</td>
                    <td>{{ $p->birth_date ? $p->birth_date->format('d-M-Y') : '' }}</td>
                    <td>{{ $p->nationality ?: 'PL' }}</td>
                    <td>{{ $p->document_type ?: 'ID/PASS' }}</td>
                    <td>{{ $p->document_number ?: '-' }}</td>
                    <td>{{ $p->document_expiry_date ? $p->document_expiry_date->format('d-M-Y') : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
