@php
$pilots = ($event->eventcontractor)->where('contractortype_id', 5);
@endphp


<!DOCTYPE html>
<html lang="pl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda dla hotelu</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css" integrity="sha384-zCbKRCUGaJDkqS1kPbPd7TveP5iyJE0EjAuZQTgFLD2ylzuqKfdKlfG/eSrtxUkn" crossorigin="anonymous">

    <link href="{{ public_path('css/print.css') }}" rel="stylesheet">
    

</head>
<body>

<footer>
    <hr>
    Biuro Podróży RAFA, tel.: + 48 606 102 243, www.bprafa.pl, nip: 716-250-87-61
        </footer>






   



    

    {{-- @foreach($event->eventContractor->where('contractortype_id', 1)->unique('contractor_id') as $econtractor) --}}
    @foreach($event->eventContractor->where('contractortype_id', 1)->unique('contractor_id') as $econtractor)
        <div class="titleclass textcenter"><strong>AGENDA DLA HOTELU</strong></div>
        <div class="titleclass  textcenter"><strong>{{ $event->eventName }}: </strong> {{ date('d.m.Y',  strtotime($event->eventStartDateTime)) }} - {{ date('d.m.Y',  strtotime($event->eventEndDateTime)) }}</div>
        <hr>
    <table class="tablebordered">
        <tr>
            <td class="tdbordered lightgrey">Termin: </td>
            <td colspan="3" class="tdbordered">{{ date('d.m.Y',  strtotime($event->eventStartDateTime)) }} - {{ date('d.m.Y',  strtotime($event->eventEndDateTime)) }}</td>
        </tr>
        <tr>
            <td class="tdbordered lightgrey">Pilot</td>
            <td class="tdbordered">
                    @foreach($event->eventContractor->where('contractortype_id', 5) as $contractor)
                    {{$contractor->contractor->firstname}} {{$contractor->contractor->surname}}<br> tel.: {{$contractor->contractor->phone}}<br>
                    @endforeach

            <td class="tdbordered lightgrey">Kierowca: </td>
            <td class="tdbordered">                    
                @foreach($event->eventContractor->where('contractortype_id', 6) as $contractor)
                    {{$contractor->contractor->name}}<br>{{$contractor->contractor->firstName}} {{$contractor->contractor->surName}}<br> tel.: {{$contractor->contractor->phone}}<br>
                    @endforeach
        </tr>

        <tr>
            <td class="tdbordered lightgrey">Zamawiający: </td>
            <td class="tdbordered"><div>Biuro Podróży RAFA</div><div>tel.: 48 660 699 210, 48 606 102 243</div></td>
            <td class="tdbordered lightgrey">Uczestnicy łącznie: </td>
            <td class="tdbordered">{{ $event->eventTotalQty }} (w tym {{ $event->eventGuardiansQty }} opiekunów) <br>+ pilot i kierowca</td>
        </tr>
        <tr>
        <td class="tdbordered lightgrey"></td>
        <td class="tbordered">
           
        </td>
        <td class="tdbordered lightgrey">Dieta: </td>
            <td class="tdbordered tabletext">{{ $event->eventDietAlert }}</td>
        </tr>
    </table>

    <div class="mb-3"><strong>Program: </strong>{{$econtractor->contractor->name}}</div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <td>data</td>
                    <td>program</td>
                    <td>uwagi</td>
                </thead>
                @foreach($event->eventElements->where('eventElementHotelPrint', 'tak')->sortBy('eventElementStart') as $element)

                    {{-- @if($element->elementContractor->contractor_id === $econtractor->id) --}}
                    @foreach($element->elementContractor as $ccc)
                        @if($ccc->id === $econtractor->contractor->id)

                    <tr>


                        <td class="tdbordered"><div>{{ date('H:i',  strtotime($element->eventElementStart)) }}-{{ date('H:i',  strtotime($element->eventElementEnd)) }}</div>
                        <div>{{ date('d.m.Y',  strtotime($element->eventElementStart)) }}</div></td>
                        <td class="tdbordered"><strong>{{ $element->element_name }}</strong>
                        {!! $element->eventElementDescription !!}<br>
                        <td class="tdbordered"><pre>{!! $element->eventElementReservation !!}</pre>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                    {{-- @endif --}}
                @endforeach
            </table>
        </div>
    </div>
        <div class="page_break"></div>

    @endforeach

    <div class="titleclass textcenter"><strong>PLIKI</strong></div>


    <div class="titleclass  textcenter"><strong>{{ $event->eventOfficeId}} - {{ $event->eventName }}: </strong> {{ date('d.m.Y',  strtotime($event->eventStartDateTime)) }} - {{ date('d.m.Y',  strtotime($event->eventEndDateTime)) }}</div>
    <div class="textcenter">Imię i nazwisko pilota:
            @foreach($pilots as $pilot)
        {{$pilot->contractor->name}} (tel.:{{$pilot->contractor->phone}}), 
    @endforeach
    </div>

    <hr>


    <table>


        <tr>
            <td class="titleclass2">Plik</td>
            <td class="titleclass2">Opis</td>
        </tr>
        @foreach($event->files->where('fileHotelSet', 'tak') as $file)

        <tr>
            <td class="titleclass2"><a href="https://biurorafa.pl/storage/{{ $file->fileName }}" download>{{ $file->fileName }}</a></td>
            <td class="titleclass2">{{ $file->FileNote }}</td>
        </tr>

        @endforeach
    </table>

    


 
    </body>
</html>




