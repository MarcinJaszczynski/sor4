<!doctype html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <title>Raport zaległych rat</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 16px; margin: 0 0 8px; }
        .meta { margin-bottom: 10px; font-size: 11px; color: #444; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h1>Raport zaległych rat</h1>
    <div class="meta">
        Wygenerowano: {{ $generatedAt }}
        @if($dateFrom || $dateTo)
            · Zakres: {{ $dateFrom ?: '—' }} – {{ $dateTo ?: '—' }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Opiekun</th>
                <th>Kod</th>
                <th>Impreza</th>
                <th>Umowa</th>
                <th>Termin</th>
                <th>Kwota</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $inst)
                @php $event = $inst->contract?->event; @endphp
                <tr>
                    <td>{{ $event?->assignedUser?->name ?? '-' }}</td>
                    <td>{{ $event?->public_code ?? '-' }}</td>
                    <td>{{ $event?->name ?? '-' }}</td>
                    <td>{{ $inst->contract?->contract_number ?? '-' }}</td>
                    <td>{{ $inst->due_date?->format('d.m.Y') ?? '-' }}</td>
                    <td>{{ number_format((float) ($inst->amount ?? 0), 2, ',', ' ') }} PLN</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Brak zaległości.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
