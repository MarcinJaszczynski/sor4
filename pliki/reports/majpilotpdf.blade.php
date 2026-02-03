<!DOCTYPE html>
<html lang="pl">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odprawa pilota</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css" integrity="sha384-zCbKRCUGaJDkqS1kPbPd7TveP5iyJE0EjAuZQTgFLD2ylzuqKfdKlfG/eSrtxUkn" crossorigin="anonymous">

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

    <div class="titleclass textcenter"><strong>TECZKA PILOTA </strong></div>


    <div class="titleclass textcenter"><strong>{{ $event->eventOfficeId}} - {{ $event->eventName }}: </strong> {{ date('d.m.Y',  strtotime($event->eventStartDateTime)) }} - {{ date('d.m.Y',  strtotime($event->eventEndDateTime)) }}</div>
    <div class="textcenter">Imię i nazwisko pilota: 

    @foreach($pilots as $pilot)
        {{$pilot->contractor->name}} (tel.:{{$pilot->contractor->phone}}), 
    @endforeach
    </div>

    <hr>




    <table class="tablebordered">
        <tr>
            <td class="tdbordered lightgrey">Termin: </td>
            <td colspan="5" class="tdbordered">{{ date('d.m.Y',  strtotime($event->eventStartDateTime)) }} - {{ date('d.m.Y',  strtotime($event->eventEndDateTime)) }}</td>
        </tr>
        <tr>
            <td class="tdbordered lightgrey" >Pilot</td>
            <td class="tdbordered">
            @foreach($pilots as $pilot)
                {{$pilot->contractor->name}}, 
            @endforeach
            </td>

            <td class="tdbordered lightgrey">Przewoźnik: </td>
            <td class="tdbordered">
                <div>
                    @foreach($event->eventContractor->where('contractortype_id', 7) as $contractor)
                    {{$contractor->contractor->name}}<br>{{$contractor->contractor->firstName}} {{$contractor->contractor->surName}}<br> tel.: {{$contractor->contractor->phone}}<br>
                    @endforeach
                
                </div>
            </td>

            <td class="tdbordered lightgrey">Kierowca: </td>
            <td class="tdbordered">
                <div>
                    @foreach($event->eventContractor->where('contractortype_id', 6) as $contractor)
                    {{$contractor->contractor->name}}<br>{{$contractor->contractor->firstName}} {{$contractor->contractor->surName}}<br> tel.: {{$contractor->contractor->phone}}<br>
                    @endforeach
                
                </div>
            </td>
        </tr>
        <tr>
            <td class="tdbordered lightgrey">Start: </td>
            <td colspan="2" class="tdbordered">
                <div>podstawienie: <strong>{{ date('H:i d/m/Y', strtotime($event->busBoardTime)) }}</strong></div>
                <hr>
                <div>wyjazd: <strong>{{ date('H:i d/m/Y',  strtotime($event->eventStartDateTime)) }} </strong></div>
                <hr>

                <div class="tabletext">{!! $event->eventStartDescription !!}</div>
            </td>
            <td class="tdbordered lightgrey">Koniec:</td>
            <td colspan="2" class="tdbordered">
                <div>powrót {{ date('H:i d/m/Y',  strtotime($event->eventEndDateTime)) }}</div>
                <hr>
                <div class="tabletext"> {!! $event->eventEndDescription !!}</div>
            </td>
        </tr>
        <tr>
            <td class="tdbordered lightgrey">Zamawiający: </td>
            <td colspan="2" class="tdbordered">
                @if($event->eventPurchaserContactPerson  != NULL)

                <div>{{ $event->eventPurchaserContactPerson }}</div>
                <div> {{ $event->eventPurchaserTel }}</div>
                <br />

                <div>{{ $event->eventPurchaserName }}</div>
                <div>{{ $event->eventPurchaserStreet }}</div>
                <div>{{ $event->eventPurchaserCity }}</div>

                                <div>
                    
                    {{-- {{ $event->eventDriver }} --}}
                    @else
                    @foreach($event->eventContractor as $contractor)
                    @if($contractor->contractortype_id === 4 && $contractor->eventelement_id === NULL)
                    <div class="text-uppercase">{{$contractor->contractor->name}}</div>
                    <div>{{$contractor->contractor->firstName}} {{$contractor->contractor->surName}}</div>
                    <div>{{$contractor->contractor->phone}}</div>
                        @endif
                    @endforeach
                    @endif
                
                </div>
            </td>
            <td class="tdbordered lightgrey">Uczestnicy łącznie: </td>
            <td colspan="2" class="tdbordered">{{ $event->eventTotalQty }} (w tym {{ $event->eventGuardiansQty }} opiekunów) </td>
        </tr>
        <tr>
            <td class="tdbordered lightgrey">Noclegi: </td>
            <td colspan="2" class="p-1 tbordered">

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
        
        
        @endforeach
                {{-- @php
                $hotelCount=0;
                @endphp

                    @foreach($event->eventContractor as $contractor)
                        @if($contractor->contractortype_id === 1)
                        @php
                        $hotelCount++
                        @endphp
                        <div><span class="text-uppercase font-weight-bold">{{$contractor->contractor->name}}</span> - {{$contractor->contractor->street}}, {{$contractor->contractor->city}},</div>
                        <div>tel.: {{$contractor->contractor->phone}}, email: {{$contractor->contractor->email}}</div>
                    @endif
                    @endforeach
                    @if($hotelCount=0)
                        @foreach($event->hotels as $hotel)
                        <div class="p5"><strong>{{ $hotel->hotelName }}</strong> - {{ $hotel->hotelStreet }} {{ $hotel->hotelCity }}, tel.: {{ $hotel->hotelPhone }} </div>
                        <hr>
                        @endforeach
                    @endif --}}
                        
            </td>
            <td class="tdbordered lightgrey">Dieta: </td>
            <td colspan="2" class="tdbordered tabletext">{{ $event->eventDietAlert }}</td>
        </tr>
    </table>


    <p><strong>PROGRAM</strong></p>


    <div class="table-responsive">
    <table class="table border table-bordered border-dark border-1">
        <tr>
            <th scope="col" class="border border-dark border-1" width="70px">data</th>
            <th scope="col" class="border border-dark border-1">Program</th>
            <th scope="col" class="border border-dark border-1">Rezerwacje/ustalenia</th>
            <th scope="col" class="border border-dark border-1">Kontrahent</th>
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

        @foreach($event->eventElements->where('eventElementPilotPrint', 'tak')->where('active', 1)->sortBy('eventElementStart') as $element)

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
                <div>{{ date('H:i',  strtotime($element->eventElementStart)) }}-{{ date('H:i',  strtotime($element->eventElementEnd)) }}</div>
                <div>{{ date('d.m.Y',  strtotime($element->eventElementStart)) }}</div>
            </td>
            <td class="border border-dark border-1"><strong>
                    {!! $element->element_name !!}</strong><br>
                {!! $element->eventElementDescription !!}
            </td>
            <td class="border border-dark border-1">{!! $element->eventElementReservation !!}</td>
            <td class="border border-dark border-1">
                @if($element->elementContractor != null)
                @foreach($element->elementContractor as $contractor)
                <div>{!!$contractor->name!!}/{!!$contractor->firstname!!} {!!$contractor->surname!!}</div>
                <div>{!!$contractor->city!!}, {!!$contractor->street!!}</div>
                <div>{!!$contractor->phone!!}, {!!$contractor->email!!}</div>
                @endforeach
                @else
                {!! $element->eventElementContact !!}</td>
                @endif



        </tr>
        @endforeach
    </table>

    <div class="page_break"></div>

    <div class="titleclass textcenter"><strong>STRUKTURA POKOJÓW/NOTATKI DLA PILOTA</strong></div>
    <div class="titleclass textcenter"><strong>{{ $event->eventOfficeId}} - {{ $event->eventName }}: </strong> {{ date('d.m.Y',  strtotime($event->eventStartDateTime)) }} - {{ date('d.m.Y',  strtotime($event->eventEndDateTime)) }}</div>
    <div class="textcenter">Imię i nazwisko pilota: 
    @foreach($pilots as $pilot)
        {{$pilot->contractor->name}} (tel.:{{$pilot->contractor->phone}}), 
    @endforeach
    </div>


    <hr>

    <br>
    @foreach($event->hotels as $hotel)

    <div class="titleclass2">{{ date('d.m.Y',  strtotime($hotel->pivot->eventHotelStartDate)) }} - {{ date('d.m.Y',  strtotime($hotel->pivot->eventHotelEndDate))}}: <strong>{{ $hotel->hotelName }},</strong> {{$hotel->hotelStreet}}, {{$hotel->hotelCity}}, tel.: {{$hotel->hotelPhone}}</div>
    <div class="tabletext"><strong>Pokoje:</strong><br>{{ $hotel->pivot->eventHotelRooms }}</div>
    <div class="tabletex"><strong>Notatki do hotelu:</strong> <br>{{ $hotel->pivot->eventHotelNote }}</div>
    <hr>

    @endforeach

    <br>
    <div class="textcenter titleclass"><strong>Notatki dla pilota: </strong></div>
    <hr>


    <div class="formatedText tabletext">{!! $event->eventPilotNotes !!}</div>




    <div class="page_break"></div>

    <div class="titleclass textcenter"><strong>ROZLICZENIE IMPREZY</strong></div>


    <div class="titleclass textcenter"><strong>{{ $event->eventOfficeId}} - {{ $event->eventName }}: </strong> {{ date('d.m.Y',  strtotime($event->eventStartDateTime)) }} - {{ date('d.m.Y',  strtotime($event->eventEndDateTime)) }}</div>
    <div class="textcenter">Imię i nazwisko pilota: 
    @foreach($pilots as $pilot)
        {{$pilot->contractor->name}} (tel.:{{$pilot->contractor->phone}}), 
    @endforeach
    </div>

    <hr>

    <table class="tablebordered" width="100%">
        <tr>
            <td colspan="2" class="tdbordered">ILOSĆ UCZESTNIKÓW</td>
        </tr>
        <tr>
            <td class="tdbordered">PLANOWANA: {{$event->eventTotalQty}} w tym {{ $event->eventGuardiansQty }} opiekunów</td>
            <td class="tdbordered">RZECZYWISTA: </td>
        </tr>
    </table>
    <table class="tablebordered" width="100%">
        <tr>
            <td colspan="2" class="tdbordered">LICZNIK AUTOKARU</td>
        </tr>
        <tr>
            <td class="tdbordered">START:</td>
            <td class="tdbordered">KONIEC</td>
        </tr>
    </table>
    </div>


    <table class="tablebordered">
        <tr>
            <th class="tdbordered lightgrey"">miejsce</th>
            <th  class=" tdbordered lightgrey"">cena/os</th>
            <th class="tdbordered lightgrey"">ilość</th>
            <th  class=" tdbordered lightgrey"">suma planowana</th>
            <th class="tdbordered lightgrey">data</th>
            <th class="tdbordered lightgrey">nr faktury</th>
            <th class="tdbordered lightgrey">suma zapłacona</th>
        </tr>



        @foreach($event->eventPayment->where('payer', 'pilot') as $payment)
        <tr>
            <td class="tdbordered tabletext">{{ $payment->paymentName }}<br>{{ $payment->paymentNote }}</td>
            <td class="tdbordered">{{ $payment->plannedPrice }}</td>
            <td class="tdbordered">{{ $payment->plannedQty }}</td>
            <td class="tdbordered"><strong> {{ $payment->plannedPrice * $payment->plannedQty }} 
                @isset($payment->plannedPaymentCurrency)
                    {{$payment->plannedPaymentCurrency->symbol}}
                @endisset
            </strong>
            <br>


            @php
                $advanceTotal = 0.0;
                $advances = \App\Models\Advance::where('payment_id', $payment->id)->get();
            @endphp

             @foreach($advances as $advance)
                    @php
                        $advanceTotal += $advance->total;
                    @endphp
       
            @endforeach
            
            @if($advanceTotal != 0.0)
                <div>Zapłacone zaliczki: {{$advanceTotal}} {{$payment->plannedPaymentCurrency->symbol}}</div>
                <div class="font-weight-bold"><u>Dopłata na miejscu: 
                    @php
                        $suplementTotal = $payment->plannedPrice * $payment->plannedQty - $advanceTotal;
                        echo $suplementTotal;
                    @endphp
                     {{$payment->plannedPaymentCurrency->symbol}}
                     </u>
                </div>
            @endif   
    

            
        </td>

            <td class="tdbordered"></td>
            <td class="tdbordered"></td>
            <td class="tdbordered"></td>
        </tr>

        @endforeach
        @for ($i = 0; $i < 5; $i++) <tr>
            <td class="tdbordered">:</td>
            <td class="tdbordered"></td>
            <td class="tdbordered"></td>
            <td class="tdbordered"></td>
            <td class="tdbordered"></td>
            <td class="tdbordered"></td>
            <td class="tdbordered"></td>
            </tr>

            @endfor

            <tr>
                <td colspan="4" class="titleclass2 tdbordered">
                         @foreach($currencies as $currency)
                                @php $paymentTemp=0.0; @endphp
                                @foreach($event->eventPayment->where('planned_currency_id', $currency->id)->where('payer','pilot') as $payment)
                                @php 
                                $pilotTotalPLN += $payment->plannedPrice * $payment->plannedQty * $payment->planned_exchange_rate;
                                $paymentTemp += $payment->plannedPrice * $payment->plannedQty;
                                echo $payment->planedQty;
                                @endphp
                                @endforeach
                                @if($paymentTemp != 0)
                                <div>{{ $paymentTemp }} {{$currency->symbol}}</div>
                                @endif

                            @endforeach
                        <hr>
                        <div>razem w przeliczeniu: {{$pilotTotalPLN}} PLN</div>
                </td>
                <td colspan="3" class="titleclass2 tdbordered">
                    Wydano: </td>
            </tr>
            <tr>
                <td colspan="4" class="titleclass2 tdbordered">Zaliczka dla pilota: {{ $event->eventAdvancePayment }}</td>
                <td colspan="3" class="titleclass2 tdbordered">
                    Do zwrotu: </td>
            </tr>
    </table>

    <div class="page_break"></div>

    <div class="titleclass textcenter"><strong>PLIKI</strong></div>


    <div class="titleclass textcenter"><strong>{{ $event->eventOfficeId}} - {{ $event->eventName }}: </strong> {{ date('d.m.Y',  strtotime($event->eventStartDateTime)) }} - {{ date('d.m.Y',  strtotime($event->eventEndDateTime)) }}</div>
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
        @foreach($event->files->where('filePilotSet', 'tak') as $file)

        <tr>
            <td class="titleclass2"><a href="https://biurorafa.pl/storage/{{ $file->fileName }}" download>{{ $file->fileName }}</a></td>
            <td class="titleclass2">{{ $file->FileNote }}</td>
        </tr>

        @endforeach
    </table>

    <div class="page_break"></div>

    <!-- Start - polityka ochrona małoletnich -->

    <div class="font-weight-bold h4">Procedury Ochrony Małoletnich/Dzieci  Podczas Wycieczek Organizowanych Przez Biuro Podróży „RAFA”</div>
    <div class="font-weight-bold h6">Preambuła</div>
Biuro Podróży RAFA – Rafał Latos (posługujące się również nazwą handlową Biuro Podróży RAFA) prowadzi swoją działalność operacyjną z najwyższym poszanowaniem praw człowieka, w szczególności praw dzieci jako osób szczególnie wrażliwych na krzywdzenie. Ponadto Biuro Podróży RAFA podkreśla istotność obowiązku zawiadamiania organów ścigania o każdym przypadku podejrzenia popełnienia przestępstwa na szkodę dzieci, zobowiązuje się szkolić swój personel w tym zakresie i edukować personel na temat okoliczności wskazujących, że dziecko przebywające na wycieczce może być krzywdzone oraz w zakresie sposobów szybkiego i odpowiedniego reagowania na takie sytuacje.
<div class="font-weight-bold h6">Procedura w przypadku podejrzenia krzywdzenia dziecka</div>
<ul>
 	<li>Pilot grupy (oraz w przypadku wycieczki szkolnej wychowawcy i nauczyciele wyznaczeni przez dyrektora placówki oświatowej do sprawowania opieki pedagogicznej), zwracają szczególną uwagę na uniemożliwienie obcym osobom dorosłym wchodzenie w jakiekolwiek relacje w stosunku do dzieci uczestniczących w wycieczce.</li>
 	<li>W przypadku pojawienia się obcej osoby dorosłej, co do której zachodzą podejrzenia o możliwości krzywdzenia dziecka należy podjąć próbę identyfikacji takiej osoby, ustalenia relacji łączącej dziecko z daną osobą, a w przypadku występowania dalszych podejrzeń krzywdzenia należy uniemożliwić takiej osobie kontakt z dzieckiem oraz niezwłocznie wezwać policję.</li>
</ul>
<div class="font-weight-bold h6">Procedura w przypadku okoliczności wskazujących na skrzywdzenie dziecka</div>
<ul>
 	<li>Mając uzasadnione podejrzenie, że dziecko przebywające na wycieczce jest krzywdzone, należy niezwłocznie zawiadomić policję, dzwoniąc pod numer 112 i opisując okoliczności zdarzenia. Zgłoszenie wykonuje pilot lub opiekun, który jest bezpośrednim świadkiem zdarzenia. O fakcie takiego zgłoszeniu należy natychmiast poinformować Biuro Podróży RAFA.</li>
 	<li>Uzasadnione podejrzenie krzywdzenia dziecka występuje wtedy, gdy:
<ul>
	<li>dziecko ujawniło pilotowi grupy lub opiekunowi fakt krzywdzenia,</li>
<li>pilot grupy lub opiekun zaobserwował krzywdzenie,</li>
<li>dziecko ma na sobie ślady krzywdzenia (np. zadrapania, zasinienia), a zapytane odpowiada niespójnie i/lub chaotycznie lub/i popada w zakłopotanie bądź występują inne okoliczności mogące wskazywać na krzywdzenie np. znalezienie materiałów pornograficznych z udziałem dzieci.</li></uL></li>
 	<li>W tej sytuacji należy starać się uniemożliwić dziecku oraz osobie podejrzewanej o krzywdzenie oddalenie się z miejsca zdarzenia.</li>
 	<li>W uzasadnionych przypadkach można dokonać obywatelskiego zatrzymania osoby podejrzewanej.</li>
 	<li>W każdym przypadku priorytetem musi być bezpieczeństwo dziecka. Dziecko, odseparowane od osoby krzywdzącej, powinno przebywać pod opieką pilota grupy i/lub opiekuna do czasu przyjazdu policji.</li>
 	<li>W przypadku uzasadnionego podejrzenia, że doszło do popełnienia przestępstwa powiązanego z kontaktem dziecka z materiałem biologicznym sprawcy (np. ślina, naskórek, płyny ustrojowe), należy w miarę możliwości nie dopuścić, aby dziecko myło się oraz jadło/piło do czasu przyjazdu policji.</li>
 	<li>Po interwencji należy opisać zdarzenie w notatce służbowej, przekazanej następnie do Biura Podróży RAFA.</li>
</ul>
<div class="font-weight-bold h6">Zatrudnianie osób do pracy z dziećmi</div>
<ol>
 	<li>Personel obsługujący wycieczkę w której uczestniczą dzieci (szczególnie wycieczkę szkolną) musi być dla nich bezpieczny, co oznacza m. in., że historia zatrudnienia tych osób powinna wskazywać, że nie skrzywdziły w przeszłości żadnego dziecka.</li>
 	<li>Biuro Podróży RAFA sprawdza każdą osobę zatrudnianą do pracy z dziećmi w Rejestrze Sprawców Przestępstw na Tle Seksualnym oraz w Krajowym Rejestrze Karnym. W sytuacji kiedy uzyskanie odpowiedniego wypisu z rejestru przed podjęciem stosunku pracy jest niemożliwe, osoba zatrudniona składa pisemne oświadczenie o niekaralności, o nietoczeniu się postępowań o czyny przeciwko dzieciom i nie figurowaniu w Rejestrze Sprawców Przestępstw na Tle Seksualnym oraz w Krajowym Rejestrze Karnym.</li>
 	<li>Sprawdzenie osoby w Rejestrze odbywa się poprzez wydruk wyników wyszukiwania osoby w odpowiednim rejestrze:
<ol type="a">

<li>sprawdzenie pilota grupy (oraz innych pracowników zatrudnionych bezpośrednio przez Biuro Podróży RAFA), leży w gestii Biura Podróży RAFA,</li>
<li>sprawdzenie wychowawców i nauczycieli biorących udział w wycieczce szkolnej, leży w gestii dyrektora placówki oświatowej,</li>
<li>sprawdzenie kierowcy/ów, leży w gestii firmy transportowej,</li>
<li>sprawdzenie pracowników obiektów noclegowych, leży w gestii właściciela obiektu,</li>
<li>sprawdzenie przewodników miejscowych, przewodników muzealnych, animatorów sportowych, survivalowych i innych leży w gestii właściciela danej firmy.</li>
</ol>
</li>
</ol>
<div class="font-weight-bold h6">Słowniczek:</div>
Na potrzeby tego dokumentu zostało doprecyzowane znaczenie poniższych pojęć:
<ol>
<li>Dziecko to każda osoba poniżej 18 roku życia.</li>
<li>Obca osoba dorosła to każdy człowiek powyżej 18 roku życia, który nie jest dla dziecka jego rodzicem lub opiekunem prawnym.</li>
<li>Krzywdzenie dziecka oznacza popełnienie na jego szkodę przestępstwa.</li>
<li>Przestępstwo na szkodę dziecka – na szkodę dzieci mogą być popełnione wszystkie przestępstwa, jakie mogą być popełnione przeciwko osobom dorosłym, a dodatkowo przestępstwa, które mogą być popełnione wyłącznie przeciwko dzieciom (np. Wykorzystywanie seksualne z art. 200 kodeku karnego ). Z uwagi na specyfikę obiektów turystycznych, w których łatwo można uzyskać możliwość odosobnienia, przestępstwami, do których najczęściej może dojść na ich terenie będą przestępstwa przeciwko wolności seksualnej i obyczajności, w szczególności zgwałcenie (art. 197 kodeksu karnego), seksualne wykorzystanie niepoczytalności i bezradności (art. 198 kk), seksualne wykorzystanie zależności lub krytycznego położenia (art. 199 kk), seksualne wykorzystanie osoby poniżej 15 roku życia (art. 200 kk), grooming (uwiedzenie małoletniego za pomocą środków porozumiewania się na odległość - art. 200a kk).</li>
</ol>

<div class="page_break"></div>



    <!-- Start - Obowiązki Pilota -->

    <div class="font-weight-bold h4">Obowiązki pilota wycieczek pracującego dla Biura Podróży RAFA</div>
    <ol>
        <li> Podczas wycieczki pilot jest przedstawicielem i reprezentantem Biura Podróży RAFA. </li>
        <li>Na miejscu zbiórki (lub podstawienia autokaru) pilot stawia się na 30 minut przed planowaną godziną odjazdu.</li>
        <li>Pilot powinien dbać o to, aby jego ubiór był dopasowany do charakteru wyjazdu.</li>
        <li>Rozpoczynając wycieczkę obowiązkiem pilota jest:
            <ul>
                <li>początkowego stanu licznika autokaru,</li>
                <li>przywitanie grupy w imieniu Biura Podróży które organizuje wyjazd oraz w swoim własnym,</li>
                <li>przedstawienie siebie i kierowcy autokaru,</li>
                <li>poinformowanie podróżnych o ramowym programie wycieczki (w tym o planie zwiedzania, trasie, orientacyjnych czasach przejazdu i postojów, itp.),</li>
                <li>poinstruowanie podróżnych o zasadach bezpieczeństwa podczas przejazdu autokarem (w szczególności: obowiązek siedzenia na swoich miejscach, zapięcia pasów bezpieczeństwa, nie chodzeniu po autokarze, zakazie spożywania posiłków podczas jazdy, nie wkładaniu butelek do siatek na gazety zamontowanych przy fotelach).</li>
            </ul>
        </li>
        <li>Podczas jazdy autokarem pilot powinien na bieżąco informować podróżnych o ważniejszych atrakcjach turystycznych, które są mijane po drodze i widoczne z okien autokaru.</li>
        <li>Dysponentem autokaru jest pilot – to pilot decyduje o trasie autokaru, czasie i miejscu postoi itp. (oczywiście z uwzględnieniem czasu pracy kierowcy).</li>
        <li>Podczas trwania wycieczki pilot pełni funkcję opiekuna grupy – dba o to, żeby klienci podczas wycieczki czuli się bezpieczni, dotarli na czas do wszystkich atrakcji przewidzianych programem itp.</li>
        <li>Podczas trwania wycieczki pilot cały czas jest w pracy – zabrania się więc spożywana alkoholu oraz innych środków odurzających.</li>
        <li>Obowiązkiem pilota jest pobranie faktur i rachunków za wszystkie wydatki (paragony z kasy fiskalnej są dopuszczalne tylko z NIPem). W przypadku braku faktury pilot zostanie obciążony kosztami pozyskania tego dokumentu lub jeśli będzie to niemożliwe kosztami podatku VAT i dochodowego jakie będzie z tego tytułu ponosiło Biuro Podróży. Możliwe jest wysłanie dokumentó pocztą tradycyjną lub elektroniczną.</li>
        <li>Podczas zwiedzania atrakcji z przewodnikiem lokalnym pilot powinien uczestniczyć w zwiedzaniu – wówczas grupę prowadzi przewodnik, a pilot idąc na końcu „zamyka grupę”. Pilot czuwa również nad prawidłowym wykonaniem pracy przez przewodnika lokalnego. </li>
        <li>Obowiązkiem pilota jest zakwaterowanie grupy w obiekcie noclegowym. Pilot zawsze otrzymuje rooming listę. Pilot pomaga również podróżnym w rozwiązywaniu ewentualnych nieprzewidzianych wcześniej problemów przy zakwaterowaniu.</li>
        <li>Pilot powinien w miarę swoich możliwości pomóc turyście który znalazł się w sytuacji trudnej – nawet jeśli „sytuacja trudna” jest spowodowana działaniem lub zaniechaniem turysty.</li>
        <li>Kiedy wycieczka dobiega końca pilot powinien podsumować wyjazd (przypomnieć miejsca które podczas wycieczki grupa obejrzała) oraz pożegnać się w imieniu swoim, biura podróży RAFA i kierowcy oraz spisać końcowy stan licznika autokaru.</li>
    </ol>

    <!-- End - Obowiązki pilota -->










</body>

</html>