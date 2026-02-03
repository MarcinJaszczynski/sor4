<!DOCTYPE html>
<html lang="pl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teczka imprezy</title>
    <link href="{{ public_path('css/print.css') }}" rel="stylesheet">
    

</head>
<body>

<footer>
    <hr>
    Biuro Podróży RAFA, tel.: + 48 606 102 243, www.bprafa.pl, nip: 716-250-87-61
        </footer>

<div class="titleclass textcenter"><h3>TECZKA IMPREZY</h3></div>
<hr>
<div class="titleclass  textcenter"><h4>{{ $event->eventOfficeId}}</h4>
<h2>
 {{ $event->eventName }}</h2></div>
 <div class="titleclass  textcenter"><h5>{{ date('d.m.Y',  strtotime($event->eventStartDateTime)) }} - {{ date('d.m.Y',  strtotime($event->eventEndDateTime)) }}</h5></div>

<div class="titleclass  textcenter"><strong>Zamawiający: </strong><br>{{ $event->eventPurchaserContactPerson }}<br>{{ $event->eventPurchaserName }}, {{ $event->eventPurchaserStreet }}, {{ $event->eventPurchaserCity }}</div>

@if($event->eventPurchaserContactPerson  != NULL)
<hr>

                <div class="titleclass textcenter">{{ $event->eventPurchaserContactPerson }}</div>
                <div class="titleclass textcenter"> {{ $event->eventPurchaserTel }}</div>
                <br />

                <div class="titleclass textcenter">{{ $event->eventPurchaserName }}</div>
                <div class="titleclas textcenters">{{ $event->eventPurchaserStreet }}</div>
                <div class="titleclass textcenter">{{ $event->eventPurchaserCity }}</div>

                                <div>
                    
                    {{-- {{ $event->eventDriver }} --}}
                    @else
                    @foreach($event->eventContractor as $contractor)
                    @if($contractor->contractortype_id === 4 && $contractor->eventelement_id === NULL)
                    <div class="titleclass textcenter text-uppercase">{{$contractor->contractor->name}}</div>
                    <div class="titleclass textcenter">{{$contractor->contractor->firstname}} {{$contractor->contractor->surname}}</div>
                    <div class="titleclass textcenter">{{$contractor->contractor->phone}}</div>
                        @endif
                    @endforeach
                    @endif



<!-- <table class="tablebordered titleclass tablebottom">
    <tr class="tdbordered  ">
        <td class="tdbordered" width="15%">Wpłaty: </td>
        <td class="tdbordered"></td>
        <td class="tdbordered"></td>
        <td class="tdbordered"></td>
        <td class="tdbordered"></td>
        <td class="tdbordered"></td>
        <td class="tdbordered"></td>
        <td class="tdbordered"></td>
        <td class="tdbordered"></td>
        <td class="tdbordered"></td>
        <td class="tdbordered" width="25%">Razem:</td>
    </tr>
    <tr>
        <td class="tdbordered" colspan="10">Koszty:</td>
        <td class="tdbordered"></td>
    </tr>
    <tr>
        <td class="tdbordered" colspan="10">Rozliczenie:</td>
        <td class="tdbordered">Razem:</td>
    </tr>
</table> -->




<div class="page_break"></div>


<div class="titleclass textcenter"><strong>PROGRAM IMPREZY </strong></div>


<div class="titleclass  textcenter"><strong>{{ $event->eventOfficeId}} - {{ $event->eventName }}: </strong> {{ date('d.m.Y',  strtotime($event->eventStartDateTime)) }}-{{ date('d.m.Y',  strtotime($event->eventEndDateTime)) }}</div>
<div class="textcenter">Imię i nazwisko pilota: {{ $event->eventPilot }}</div>

<hr>




    <table class="tablebordered">
        <tr class="tdbordered">
            <td class="tdbordered lightgrey">Termin: </td>
            <td colspan="3" class="tdbordered">{{ date('d.m.Y',  strtotime($event->eventStartDateTime)) }} - {{ date('d.m.Y',  strtotime($event->eventEndDateTime)) }}</td>
        </tr>
        <tr>
            <td class="tdbordered lightgrey">Pilot</td>
            <td class="tdbordered"><div> {{ $event->eventPilot }}</div> </td>

            <td class="tdbordered lightgrey">Kierowca: </td>
            <td class="tdbordered"><div>{{ $event->eventDriver }}</div></td>
        </tr>
        <tr>
            <td class="tdbordered lightgrey">Start: </td>
            <td  class="tdbordered"><div>wyjazd: {{ date('H:i d/m/Y',  strtotime($event->eventStartDateTime)) }} </div>
            <hr>
            <div>podstawienie: {{ date('H:i d/m/Y', strtotime($event->busBoardTime)) }}</div><div><pre>{{ $event->eventStartDescription }}</pre></div></td>
            <td class="tdbordered lightgrey">Koniec:</td>
            <td class="tdbordered"><div>powrót {{ date('H:i d/m/Y',  strtotime($event->eventEndDateTime)) }}</div>
            <hr>
            <div> {{ $event->eventEndDescription }}</div></td>
        </tr>
        <tr>
            <td class="tdbordered lightgrey">Zamawiający: </td>
            <td class="tdbordered"><div>{{ $event->eventPurchaserContactPerson }}</div><div> {{ $event->eventPurchaserTel }}</div></td>
            <td class="tdbordered lightgrey">Uczestnicy łącznie: </td>
            <td class="tdbordered">{{ $event->eventTotalQty }} (w tym {{ $event->eventGuardiansQty }} opiekunów) </td>
        </tr>
        <tr>
        <td class="tdbordered lightgrey">Noclegi: </td>
        <td class="tbordered">
            @foreach($event->hotels as $hotel)
            <div class="p5"><strong>{{ $hotel->hotelName }}</strong> - {{ $hotel->hotelStreet }} {{ $hotel->hotelCity }}, tel.: {{ $hotel->hotelPhone }} </div>
            <hr>
            @endforeach
        </td>
        <td class="tdbordered lightgrey">Dieta: </td>
            <td class="tdbordered"><pre>{{ $event->eventDietAlert }}</pre></td>
        </tr>
    </table>

    
    <p><strong>PROGRAM</strong></p>

    

    <table class="tablebordered" width="100%">
        <tr>
            <th class="tdbordered lightgrey" width="70px">data</th><th class="tdbordered lightgrey">Program</th><th class="tdbordered lightgrey">kontakt/miejsce</th><th class="tdbordered lightgrey">Rezerwacje</th>
</tr>

@php
$first_datetime = new DateTime($event->eventStartDateTime);
$f_datetime = $first_datetime->format("d");
$timeInterval = 1;
echo '<tr><td class="tdbordered" colspan="4"><h4><strong>DZIEŃ '.$timeInterval.'</strong></h4></td></tr>';
@endphp

    @foreach($event->eventElements->sortBy('eventElementStart') as $element)

    <?php
$last_datetime = new DateTime($element->eventElementStart);
$l_datetime = $last_datetime->format("d");
if ($f_datetime != $l_datetime){
    $timeInterval++;
    $f_datetime = $l_datetime;
    echo '<tr><td class="tdbordered" colspan="4"><h4><strong>DZIEŃ '.$timeInterval.'</strong></h4></td></tr>';
}
                                    
                                ?>

                                
        <tr>
            <td class="tdbordered"><div>{{ date('H:i',  strtotime($element->eventElementStart)) }}-{{ date('H:i',  strtotime($element->eventElementEnd)) }}</div>
            <div>{{ date('d.m.Y',  strtotime($element->eventElementStart)) }}</div></td>
            <td class="tdbordered"><strong>{!! $element->element_name !!}</strong>
            <div class="tabletext">{!! $element->eventElementDescription !!}</div></td>
            <td class="tdbordered tabletext">{!! $element->eventElementContact !!}</td>
            <td class="tdbordered"><div class="tabletext">{!! $element->eventElementReservation !!}</div>
        </td>



        </tr>
        @endforeach
    </table>

    <div class="page_break"></div>

    <div class="titleclass textcenter"><strong>NOTATKI/STRUKTURA POKOJÓW</strong></div>
    <div class="titleclass  textcenter"><strong>{{ $event->eventOfficeId}} - {{ $event->eventName }}: </strong> {{ date('d.m.Y',  strtotime($event->eventStartDateTime)) }} - {{ date('d.m.Y',  strtotime($event->eventEndDateTime)) }}</div>
    <div class="textcenter">Imię i nazwisko pilota: {{ $event->eventPilot }}</div>


    <hr>

    <br>
    @foreach($event->hotels as $hotel)

    <div class="titleclass2">{{ date('d.m.Y',  strtotime($hotel->pivot->eventHotelStartDate)) }}-{{ date('d.m.Y',  strtotime($hotel->pivot->eventHotelEndDate))}}: <strong>{{ $hotel->hotelName }},</strong> {{$hotel->hotelStreet}}, {{$hotel->hotelCity}}, tel.: {{$hotel->hotelPhone}}</div>
    <div>Notatki do hotelu: {{ $hotel->pivot->eventHotelNote }}</div>
    <div>Pokoje: <pre>{{ $hotel->pivot->eventHotelRooms }}</pre></div>
    <hr>

    @endforeach

    <div class="textcenter titleclass2">Notatki dla pilota: </div>
<hr>


    <div class="formatedText"><pre>{!! $event->eventPilot !!}</pre></div>




    <div class="page_break"></div>

    <div class="titleclass textcenter"><strong>ROZLICZENIE IMPREZY</strong></div>


    <div class="titleclass  textcenter"><strong>{{ $event->eventOfficeId}} - {{ $event->eventName }}: </strong> {{ date('d.m.Y',  strtotime($event->eventStartDateTime)) }} - {{ date('d.m.Y',  strtotime($event->eventEndDateTime)) }}</div>
    <div class="textcenter">Imię i nazwisko pilota: {{ $event->eventPilot }}</div>

    <hr>

    <table class="tablebordered" width="100%">
    <tr><td colspan="2" class="tdbordered">ILOSĆ UCZESTNIKÓW</td></tr>
    <tr>
        <td class="tdbordered">PLANOWANA: {{$event->eventTotalQty}} w tym {{ $event->eventGuardiansQty }} opiekunów</td>
        <td class="tdbordered">RZECZYWISTA: </td>
    </tr>
</table>
    <table class="tablebordered" width="100%">
    <tr><td colspan="2" class="tdbordered">LICZNIK AUTOKARU</td></tr>
    <tr>
        <td class="tdbordered">START:</td>
        <td class="tdbordered">KONIEC</td>
    </tr>
</table>
    

    <table  class="tablebordered">
        <tr>
            <th  class="tdbordered lightgrey"">miejsce</th>
            <th  class="tdbordered lightgrey"">cena/os</th>            
            <th  class="tdbordered lightgrey"">ilość</th>
            <th  class="tdbordered lightgrey"">suma</th>
            <th class="tdbordered lightgrey">data</th>
            <th class="tdbordered lightgrey">nr faktury</th>
            <th  class="tdbordered lightgrey">zapłacone</th>
        </tr>



        @foreach($event->eventPayment as $payment)

        
        
        <tr>
            <td class="tdbordered">{{ $payment->paymentName }}<br>{{ $payment->paymentDescription }}
            <td class="tdbordered">{{ $payment->price }}</td>
            <td class="tdbordered">{{ $payment->qty }}</td>
            <td class="tdbordered"><strong> {{ $payment->price * $payment->qty }}</strong></td>
            <td class="tdbordered">{{ $payment->paymentDate }}</td>
            <td class="tdbordered">{{ $payment->invoice }}<br>{{ $payment->paymentNote }}</td></td>
            <td class="tdbordered">        
                @if($payment->paymentStatus == 0)
            niezapłacone
        @else
            zapłacone
        @endif</td>
        </tr>

        @endforeach

        <tr><td colspan="4" class="titleclass2 tdbordered"></td><td colspan="3" class="titleclass2 tdbordered">
            Łącznie: {{ $event->totalSum($event->id) }}</td>
        </tr>
        <tr><td colspan="4" class="titleclass2 tdbordered"></td><td colspan="3" class="titleclass2 tdbordered">
            Do zwrotu: </td>
        </tr>
    </table>

        <div class="page_break"></div>

<div class="titleclass textcenter"><strong>PLIKI</strong></div>


<div class="titleclass  textcenter"><strong>{{ $event->eventOfficeId}} - {{ $event->eventName }}: </strong> {{ date('d.m.Y',  strtotime($event->eventStartDateTime)) }} - {{ date('d.m.Y',  strtotime($event->eventEndDateTime)) }}</div>
<div class="textcenter">Imię i nazwisko pilota: {{ $event->eventPilot }}</div>

<hr>


    <table>


    <tr>
        <td class="titleclass2">Plik</td><td class="titleclass2">Opis</td>
    </tr>
        @foreach($event->files as $file)

        <tr>
        <td class="titleclass2"><a href="https://host378742.xce.pl/storage/{{ $file->fileName }}" download>{{ $file->fileName }}</a></td>
        <td class="titleclass2">{{ $file->FileNote }}</td>
        </tr>

        @endforeach
    </table>









    
</body>
</html>




