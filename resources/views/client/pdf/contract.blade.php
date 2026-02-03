<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
    </style>
</head>
<body>
    <h1>UMOWA O USŁUGI TURYSTYCZNE</h1>
    <h2>Nr: {{ $contract->contract_number }}</h2>
    <p>Data: {{ $contract->date_issued ? $contract->date_issued->format('d.m.Y') : 'Data nieznana' }}</p>
    <hr>
    <h3>Impreza: {{ $contract->event->name ?? '---' }}</h3>
    
    @if($contract->locked_price_per_person > 0)
    <p><strong>Cena za osobę:</strong> {{ number_format($contract->locked_price_per_person, 2, ',', ' ') }} PLN</p>
    @endif
    
    <div>
        {!! $contract->content !!}
    </div>

    @if($contract->total_amount > 0)
    <br>
    <p><strong>Całkowita kwota umowy:</strong> {{ number_format($contract->total_amount, 2, ',', ' ') }} PLN</p>
    @endif

    <br><br>
    <p>Podpis organizatora: ____________________</p>
    <p>Podpis klienta: ____________________</p>
</body>
</html>
