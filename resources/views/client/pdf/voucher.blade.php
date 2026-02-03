<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: DejaVu Sans, sans-serif; text-align: center; border: 5px solid #000; padding: 20px; }
        .voucher-title { font-size: 40px; font-weight: bold; margin-bottom: 20px; }
        .details { font-size: 18px; margin: 10px 0; }
        .verified { color: green; font-weight: bold; border: 2px solid green; display: inline-block; padding: 10px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="voucher-title">VOUCHER / BILET</div>
    
    <div class="details">
        <strong>Impreza:</strong> {{ $contract->event->name ?? '---' }}
    </div>
    
    <div class="details">
        <strong>Umowa:</strong> {{ $contract->contract_number }}
    </div>
    
    <div class="details">
        <strong>Uczestnicy:</strong><br>
        @foreach($contract->participants as $p)
            {{ $p->first_name }} {{ $p->last_name }}<br>
        @endforeach
    </div>

    <div class="verified">
        OPŁACONO W CAŁOŚCI - POTWIERDZENIE REZERWACJI
    </div>

    <p style="margin-top: 50px; font-size: 12px; color: #555;">Prosimy o okazanie tego dokumentu pilotowi lub w recepcji hotelu.</p>
</body>
</html>
