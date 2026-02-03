<!DOCTYPE html>
<html lang="pl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rozliczenie imprezy</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css" integrity="sha384-zCbKRCUGaJDkqS1kPbPd7TveP5iyJE0EjAuZQTgFLD2ylzuqKfdKlfG/eSrtxUkn" crossorigin="anonymous">
    <link href="{{ public_path('css/print.css') }}" rel="stylesheet">  

</head>
<body>
@php
$currencies=\App\Models\Currency::get();
$payments = ($event->eventPayment);
@endphp

<footer>
    <hr>
    Biuro Podróży RAFA, tel.: + 48 606 102 243, www.bprafa.pl, nip: 716-250-87-61
        </footer>

<div class="titleclass textcenter"><strong>Rozliczenie imprezy</strong></div>


<div class="titleclass  textcenter"><strong>Nr imprezy: {{ $event->eventOfficeId}}</strong></div>
<div class="titleclass  textcenter"><strong> {{ $event->eventName }}: </strong> </div>
<div class="titleclass  textcenter"> termin: {{ date('d.m.Y',  strtotime($event->eventStartDateTime)) }} - {{ date('d.m.Y',  strtotime($event->eventEndDateTime)) }}</div>

<hr>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <td>data płatności</td>
            <td>dokument</td>
            <td>za</td>
            <td>kontrahent</td>
            <td>kwota</td>
            <td>waluta</td>
        </thead>
        @foreach($payments as $payment)
        <tr>
            <td>{{$payment->paymentDate}}</td>
            <td>{{$payment->invoice}}</td>
            <td>{{$payment->paymentName}}</td>
            <td>
                @isset($payment->contractor)
                    {{$payment->contractor->name}}<br>                                    
                    {{$payment->contractor->street}}<br>                                    
                    {{$payment->contractor->city}}<br>                                    
                    {{$payment->contractor->nip}}<br>                                    
                @endisset
                </td>
            <td>{{ $payment->price * $payment->qty }}</td>
            <td>
                @isset($payment->currency)
                    {{ $payment->currency->symbol }}</td>
                @endisset
        </tr>
        @endforeach
    </table>
</div>
<div class="container">
    <div class="row">
                <div class="float-right">
        <div class="font-weight-bold">Razem:</div>
        @foreach($currencies as $currency)
            @php
            $total = 0.0;
            @endphp
            @foreach($payments as $payment)
                @if($payment->currency_id === $currency->id)
                    @php
                        $total += $payment->qty * $payment->price;
                    @endphp
                @endif
            @endforeach

        @if($total != 0)

            <div class="">{{$total}} - {{$currency->symbol}}</div>
        @endif
        @endforeach
        </div>
    </div>
</div>

    
</body>
</html>




