<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
    </style>
</head>
<body>
    <h1>ANEKS DO UMOWY</h1>
    <h2>Nr: {{ $addendum->addendum_number }}</h2>
    <p>Do umowy nr: {{ $contract->contract_number }}</p>
    <p>Data: {{ $addendum->date_issued ? $addendum->date_issued->format('d.m.Y') : '—' }}</p>
    <hr>
    
    @if($addendum->locked_price_per_person > 0)
    <p><strong>Cena za osobę:</strong> {{ number_format($addendum->locked_price_per_person, 2, ',', ' ') }} PLN</p>
    @endif

    <div>
        {!! $addendum->content !!}
    </div>

    @if($addendum->changes_summary)
    <br>
    <h3>Podsumowanie zmian:</h3>
    <div>{{ $addendum->changes_summary }}</div>
    @endif

    @if($addendum->new_total_amount > 0)
    <br>
    <p><strong>Nowa kwota całkowita:</strong> {{ number_format($addendum->new_total_amount, 2, ',', ' ') }} PLN</p>
    @endif

    <br><br>
    <p>Podpis organizatora: ____________________</p>
    <p>Podpis klienta: ____________________</p>
</body>
</html>