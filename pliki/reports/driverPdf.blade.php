<!DOCTYPE html>
<html lang="pl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teczka imprezy</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css" integrity="sha384-zCbKRCUGaJDkqS1kPbPd7TveP5iyJE0EjAuZQTgFLD2ylzuqKfdKlfG/eSrtxUkn" crossorigin="anonymous">
    <link href="{{ public_path('css/print.css') }}" rel="stylesheet">


    <link href="{{ public_path('css/print.css') }}" rel="stylesheet">
    

</head>
<body>
@php
$pilots = ($event->eventcontractor)->where('contractortype_id', 5);
$contractors = ($event->eventcontractor)->where('contractortype_id', 1);

@endphp

<footer>
    <hr>
    Biuro Podróży RAFA, tel.: + 48 606 102 243, www.bprafa.pl, nip: 716-250-87-61
        </footer>

<div class="titleclass textcenter"><strong>INFORMACJE DLA KIEROWCY</strong></div>


<div class="titleclass  textcenter"><strong>{{ $event->eventOfficeId}} - {{ $event->eventName }}: </strong> {{ date('d.m.Y',  strtotime($event->eventStartDateTime)) }} - {{ date('d.m.Y',  strtotime($event->eventEndDateTime)) }}</div>

<hr>
<div class="textcenter titleclass">Pilot: 
    @foreach($pilots as $pilot)
        {{$pilot->contractor->firstname}} {{$pilot->contractor->surname}} (tel.:{{$pilot->contractor->phone}}), 
    @endforeach
</div>


<table class="tablebordered titleclass">
    <tr>
        <td class="tdbordered" ><strong>Podstawienie: </strong></td>
        <td class="tdbordered">godz: {{ date('H:i d/m/Y', strtotime($event->busBoardTime)) }}
            <hr>
        <div class="tabletext">{!! $event->eventStartDescription !!}</div>
        </td>
    </tr>
    <tr>
        <td class="tdbordered"><strong>Odjazd: </strong></td>
        <td class="tdbordered">godz: {{ date('H:i d/m/Y', strtotime($event->eventStartDateTime)) }}</td>
    </tr>

    <tr>
        <td class="tdbordered" ><strong>Powrót: </strong></td>
        <td class="tdbordered">godz: {{ date('H:i d/m/Y', strtotime($event->eventEndDateTime)) }}</td>
    </tr>
    <tr>
        <td class="tdbordered" ><strong>Ilość uczestników: </strong></td>
        <td class="tdbordered">{{ $event->eventTotalQty }} + 1 pilot</td>
    </tr>
</table>
<div class="titleclass"><strong>Informacje o wycieczce: </strong></div>
<div class="tabletext titleclass">{!! $event->eventEndDescription !!}</div>
<hr>
<div class="titleclass"><strong>Hotele: </strong></div>

@isset($event->hotels)
            @foreach($event->hotels as $hotel)
            <span>{!! $hotel->hotelName !!}, </span>
            @endforeach
        @endisset
        @foreach($event->eventContractor->where('contractortype_id', 1)->unique('contractor_id') as $econtractor)
        <hr>
        <div>{!! $econtractor->contractor->name !!}</div>
        <div>{!! $econtractor->contractor->street!!}, {!! $econtractor->contractor->city !!}, </div>
        <div>{!! $econtractor->contractor->phone!!}</div>
        <hr>
        
        @endforeach

    
</body>
</html>




