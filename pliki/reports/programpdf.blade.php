<!DOCTYPE html>
<html lang="pl">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program imprezy</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css"
        integrity="sha384-zCbKRCUGaJDkqS1kPbPd7TveP5iyJE0EjAuZQTgFLD2ylzuqKfdKlfG/eSrtxUkn" crossorigin="anonymous">

    <link href="{{ public_path('css/print.css') }}" rel="stylesheet">


</head>

<body>
    @php
    $currencies=\App\Models\Currency::get();

    $pilots = ($event->eventcontractor)->where('contractortype_id', 5);
    $contractors = ($event->eventcontractor)->where('contractortype_id', 1);
    $totalSum=0.0;
    $pilotTotalPLN=0.0;
    @endphp
    @inject('carbon', 'Carbon\Carbon')

    <footer>
        <hr>
        <p>Biuro Podróży RAFA, ul. M. Konopnickiej 6, 00-491 Warszawa, tel.: + 48 606 102 243, nip: 716-250-87-61</p>
    </footer>

    <div class="titleclass textcenter"><strong>Program imprezy </strong></div>


    <div class="titleclass  textcenter"><strong>{{ $event->eventOfficeId}} - {{ $event->eventName }}: </strong> {{
        date('d.m.Y', strtotime($event->eventStartDateTime)) }} - {{ date('d.m.Y', strtotime($event->eventEndDateTime))
        }}</div>
    <hr>

    <table class="tablebordered">
        <tr>
            <td class="tdbordered lightgrey">Termin: </td>
            <td colspan="5" class="tdbordered">{{ date('d.m.Y', strtotime($event->eventStartDateTime)) }} - {{
                date('d.m.Y', strtotime($event->eventEndDateTime)) }}</td>
        </tr>
    </table>


    <p><strong>PROGRAM</strong></p>


    <div class="table-responsive">
        <table class="table table-bordered border border-dark border-1">
            <tr>
                <th scope="col" class="border border-dark border-1" width="70px">data</th>
                <th scope="col" class="border border-dark border-1">Program</th>

            </tr>
            @php
            $first_datetime = new DateTime($event->eventStartDateTime);
            $f_datetime = $first_datetime->format("d");
            $timeInterval = 1;
            echo '<tr>
                <td class="border border-dark border-1" colspan="4">
                    <h6><strong>DZIEŃ '.$timeInterval.'</strong></h6>
                </td>
            </tr>';
            @endphp

            @foreach($event->eventElements->where('eventElementPilotPrint', 'tak')->where('active',
            1)->sortBy('eventElementStart') as $element)

            <?php
        $last_datetime = new DateTime($element->eventElementStart);
        $l_datetime = $last_datetime->format("d");
        if ($f_datetime != $l_datetime) {
            $timeInterval++;
            $f_datetime = $l_datetime;
            echo '<tr><td class="border border-dark border-1" colspan="4"><h4><strong>DZIEŃ ' . $timeInterval . '</strong></h4></td></tr>';
        }

        ?>

            <tr>
                <td class="border border-dark border-1">
                    <div>{{ date('H:i', strtotime($element->eventElementStart)) }}-{{ date('H:i',
                        strtotime($element->eventElementEnd)) }}</div>
                    <div>{{ date('d.m.Y', strtotime($element->eventElementStart)) }}</div>
                </td>
                <td class="border border-dark border-1">
                    {!! $element->element_name !!}
                </td>
                @endif



            </tr>
            @endforeach
        </table>

        <div class="page_break"></div>
</body>

</html>