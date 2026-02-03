<!DOCTYPE html>
<html lang="pl">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Umowa</title>
    <link href="{{ public_path('css/print.css') }}" rel="stylesheet">


</head>

<body>

    <footer>

    </footer>

    <h1>Potwierdzenie zawarcia umowy o organizację imprezy turystycznej nr {{ $request->eventOfficeId }}</h1>
    <h2><b>Umowa zawarta dnia {{ $request->contractDate }} pomiędzy:</b></h2>
    <div><b>zamawiającym:</b></div>
    <div>{{ $request->eventPurchaserContactPerson }}</div>
    <div><b>a organizatorem:</b></div>
    <div><b>Biuro Podróży RAFA - Rafał Latos</b></div>
    <div>ul. Marii Konopnickiej 6, 00-491 Warszawa, NIP 716-250-87-61, REGON 432298189</div>
    <div>Wpis do Rejestru Organizatorów Turystyki i Pośredników Turystycznych Województwa Mazowieckiego nr 1270, Polisa OC Organizatora Turystyki nr M 520855 Signal Iduna Polska Towarzystwo Ubezpieczeń S.A.</div>

    <h2>Informacje o imprezie turystycznej</h2>
    <div>Rodzaj imprezy turystycznej: <b>{{ $request->eventType }}</b></div>
    <div>Nazwa imprezy/destynacja: <b>{{ $request->eventName }}</b></div>
    <div>Termin wycieczki: <b> {{ date('Y.m.d',  strtotime($request->eventStartDateTime)) }} - {{ date('Y.m.d',  strtotime($request->eventEndDateTime)) }}</b></div>
    <div>Środek transportu: <b>{{ $request->coach }}</b></div>
    <div>Ilość uczestników (łącznie z opiekunami): <b> {{ $request->eventTotalQty }}</b></div>
    <div>Ilość opiekunów: <b> {{ $request->eventGuardiansQty }}</b></div>
    <div>Wyjazd: <b> {{ date('Y.m.d\ \g\o\d\z. H:i',  strtotime($request->eventStartDateTime)) }} - {{ $request->eventStartDescription  }} </b></div>
    <div>Powrót: <b> {{ date('Y.m.d\ \g\o\d\z. H:i',  strtotime($request->eventEndDateTime)) }} - {{ $request->eventStartDescription }}</b></div>
    <div>Obiekt noclegowy: 
        {!! $request->eventHotel !!}</div>
    <div>Wyżywienie: <b> {{ $request->eventFood }}</b></div>
    <div>Ubezpieczenie: <b> {{ $request->eventInsurance }}</b></div>
    <div>Dodatkowe informacje: <b> {!! $request->eventAddInfo !!}</b></div>

    <h2>Podstawienie i miejsce zbiórki</h2>
    <div>Miejsce podstawienia autokaru: <b>{{ $request->eventStartDescription }}</b></div>
    <div>Godzina podstawienia: <b>{{ date('Y-m-d\ \g\o\d\z. H:i',  strtotime($request->busBoardTime)) }}</b></div>
    <br />
    <div> <b>Uwaga!</b> Autokar zostanie podstawiony w miejscu wskazanym w umowie. Zamawiający lub Podróżny mają prawo do poproszenia odpowiednich służb o przeprowadzenie kontroli stanu technicznego autokaru oraz trzeźwości kierowcy. Kontrola taka odbywa się <u>w miejscu podstawienia autokaru</u> na 30 minut przed planowaną godziną wyjazdu. Wszelkie formalności związane z wezwaniem służb uprawnionych do kontroli pozostają po stronie Zamawiającego/Podróżnego zlecającego kontrolę.</div>

    <h2>Cena imprezy i harmonogram wpłat</h2>

    <h3><b>Cena brutto: {{ $request->eventPriceBrutto}}</b></h3>
    <div>Słownie: <b>{{ $request->eventPrice }}</b></div>
    <div>Sposób płatności: <b>{{ $request->eventPriceType }} </b></div>
    <br>
    <div>Cena obejmuje:<b> {{ $request->eventPriceInclude }} </b></div>
    <br>

    <h3>Terminy rozliczenia harmonogramu wpłat: </h3>

    <div>Zaliczka w kwocie: <b><span class="textred">{{ $request->eventAdvance}}</span></b> płatna do dnia: <b><span class="textred">{{ $request->eventAdvanceTime}}</span></b></div>
    <br>
    <div>Dopłata do całości w kwocie: <b><span class="textred">{{ $request->eventSupplement }}</span></b> płatna do dnia: <b><span class="textred">{{ $request->eventSupplementTime }}</span></b></div>
    <br>
    <div>Konto organizatora Bank Millenium S.A. nr <b>10 1160 2202 0000 0002 0065 6958</b></div>
    <br>
    <div><u>W opisie przelewu należy podać:</u> <b><span class="textred">{{ $request->eventName }}, rezerwacja nr {{ $request->eventOfficeId }} </span></b></div>
    <br>
    <hr>
    <div><b>Wpłata zaliczki na konto Biura Podróży RAFA jest jednoznaczna z zawarciem umowy oraz akceptacją warunków uczestnictwa w imprezach organizowanych przez Biuro Podróży RAFA. Umowa zostaje zawarta w chwili zaksięgowania zapłaty na rachunku Biura Podróży RAFA</b></div>
    <br>
    <div><b>Brak zapłaty w wyznaczonym terminie jest jednoznaczny z rezygnacją przez Zamawiającego z organizacji imprezy turystycznej.</b></div>









    </div>
    <hr>

    <br>
    <h2>Załączniki do umowy</h2>
    <ol>
        <li>Warunki uczestnictwa w imprezach organizowanych przez Biuro Podróży RAFA - załącznik nr 1 do niniejszej umowy</li>
        <li>Program zwiedzania (oferta) - załącznik nr 2 do niniejszej umowy</li>
        <li>Standardowy formularz informacyjny - załącznik n3 do niniejsze umowy</li>
        <li>Warunki ubezieczenia NNW przy wyjazdach krajowych i KL przy wyjazdach zagranicznych - załąznik nr 4 niniejszej umowy</li>
    </ol>


    <!-- Start - Warunki uczestnictwa  -->

<h1>Ogólne Warunki Uczestnictwa w Imprezach Turystycznych organizowanych przez Biuro Podróży RAFA</h1>
<h2><b>1.</b> Postanowienia ogólne</h2>
<div><b>1.1</b> Organizatorem Imprez Turystycznych o których mowa w niniejszych Warunkach Uczestnictwa jest Biuro Podróży RAFA – Rafał Latos (posługujące się również nazwą handlową Biuro Podróży RAFA), ul. Marii Konopnickiej 6, 00-491 Warszawa, NIP 716-250-87-61, REGON 432298189, wpis do rejestru Organizatorów i Pośredników Turystycznych nr 1270, posiadające Gwarancję Ubezpieczeniową Organizatora Turystyki w Towarzystwie Ubezpieczeniowym Signal Iduna S.A.</div>
<div><b>1.2</b> Pojęcia: „impreza turystyczna”, zwana dalej „Imprezą”, „umowa o udział w imprezie turystycznej”, zwana dalej „Umową”, „Podróżny”, „Organizator”, nieuniknione i nadzwyczajne okoliczności oraz „trwały nośnik”; są używane w Warunkach Uczestnictwa, w znaczeniu nadanym im przez przepisy Ustawy o Imprezach Turystycznych i Powiązanych Usługach Turystycznych z dnia 24 listopada 2017 r., zwanej dalej „Ustawą”.</div>
<h2>2. Obowiązki informacyjne wobec Podróżnych</h2>
<div><b>2.1</b> Przed zawarciem umowy udziela się Podróżnemu:</div>
<div><b>a)</b> standardowych informacji za pośrednictwem odpowiedniego Standardowego Formularza Informacyjnego.</div>
<div><b>b)</b> informacji określonych w art. 40 ust. 1 i 3 Ustawy, zwanych dalej „informacjami o imprezie&quot;.</div>
<div><b>2.2</b> Informacje o imprezie zawarte są: w Ofercie, w Warunkach Uczestnictwa, informacjach dodatkowych do oferty turystycznej oraz w Umowie/Potwierdzeniu rezerwacji.</div>
<h2>3. Zawarcie umowy o organizację imprezy turystycznej, przedmiot umowy, cena imprezy oraz warunki zapłaty</h2>
<div><b>3.1</b> Umowa może zostać zawarta: w formie papierowej w fizycznej obecności stron lub w formie elektronicznej poprzez stronę www.bprafa.pl lub poprzez elektroniczne potwierdzenie rezerwacji wystawione przez Organizatora i przesłane na e-mail wskazany przez Podróżnego. W przypadku zawierania Umowy w formie elektronicznej do zawarcia Umowy dochodzi poprzez dokonanie wpłaty zgodnie z warunkami opisanymi w Umowie i Warunkach Uczestnictwa.</div>
<div><b>3.2</b> Osoba dokonująca rezerwacji musi posiadać pełną zdolność do czynności prawnych. W przypadku zawierania Umowy na rzecz osoby trzeciej (osób trzecich), osoba zawierająca Umowę wskazuje tę osobę (osoby) w momencie zawarcia Umowy. W przypadku rezerwacji grupowych należy podać liczbę uczestników objętych Umową.</div>
<div><b>3.3</b> W przypadku zawierania Umowy na rzecz osoby małoletniej Umowę zawiera rodzic lub opiekun prawny lub osoba posiadająca upoważnienie rodziców lub opiekunów prawnych do zawarcia Umowy. Zawarcie Umowy jest jednoznaczne ze złożeniem oświadczenia o posiadaniu odpowiedniego upoważnienia.</div>
<div><b>3.4</b> W chwili zawarcia Umowy lub niezwłocznie po jej zawarciu udostępnia się Podróżnemu na trwałym nośniku kopię Umowy lub potwierdzenie jej zawarcia. Podróżny jest uprawniony do żądania kopii Umowy w postaci papierowej, jeżeli została zawarta w jednoczesnej fizycznej obecności stron.</div>
<div><b>3.5</b> Na Umowę składa się łącznie treść następujących dokumentów: Umowa lub Potwierdzenie Rezerwacji, Warunki Uczestnictwa, Standardowy Formularz Informacyjny, Oferta zawierająca opis Imprezy wybranej przez Podróżnego i stanowiącej przedmiot Umowy.</div>
<div><b>3.6</b> Zawarcie Umowy jest jednoznaczne z zapoznaniem się z Warunkami Uczestnictwa i akceptacją ich postanowień.</div>
<div><b>3.7</b> Cena określona w Umowie jest ceną brutto i zawiera świadczenia opisane w Ofercie/Opisie Imprezy.</div>
<div><b>3.8</b> Organizator zastrzega sobie prawo zmiany ceny Imprezy Turystycznej (podwyższenia ceny) na skutek bezpośrednich zmian ceny przewozów pasażerskich wynikających ze zmiany kosztów paliwa lub innych źródeł zasilania, wysokości podatków lub opłat od usług turystycznych objętych Umową o udział w Imprezie Turystycznej, nałożonych przez podmioty, które nie biorą bezpośredniego udziału w realizacji Imprezy Turystycznej, w tym podatków turystycznych, opłat lotniskowych lub opłaty za wejście na pokład i zejście na ląd w portach oraz na lotniskach, kursów walut mających znaczenie dla danej Imprezy Turystycznej. W przypadku konieczności podwyższenia ceny Organizator powiadomi o tym fakcie Podróżnego wraz z uzasadnieniem podwyżki wskazując sposób jej naliczenia. W okresie 20 dni przed rozpoczęciem Imprezy Turystycznej, cena opisana w umowie z Podróżnym nie może być podwyższona. W przypadku obniżenia kosztów o których mowa powyżej Podróżny ma prawo do obniżenia ceny Imprezy Turystycznej.</div>
<div><b>3.9</b> Organizator udziela bezpłatnej gwarancji niezmienności ceny dla wszystkich Imprez Turystycznych dla których od dnia zawarcia Umowy do dnia rozpoczęcia Imprezy pozostaje nie więcej niż 90 dni. W pozostałych przypadkach Podróżny może dokupić gwarancję niezmienności ceny za dodatkową opłatą.</div>
<div><b>3.10</b> W dniu zawarcia Umowy, Podróżny dokonuje przedpłaty w wysokości 30% wartości imprezy (za każdego Podróżnego określonego w Umowie) – chyba, że Umowa stanowi inaczej.</div>
<div><b>3.11</b> Wpłaty pozostałej kwoty Podróżny dokonuje najpóźniej na:</div>
<div><b>a)</b> 14 dni przed datą rozpoczęcia Imprezy w przypadku imprez krajowych – chyba, że Umowa stanowi inaczej;</div>
<div><b>b)</b> 30 dni przed datą rozpoczęcia Imprezy w przypadku imprez zagranicznych – chyba, że Umowa stanowi inaczej.</div>
<div><b>3.12</b> W przypadku nie wywiązania się przez Podróżnego z obowiązków wynikających z punktów 3.10 lub 3.11 Organizator zastrzega sobie prawo do anulowania nieopłaconej rezerwacji. Konsekwencją niewywiązania się przez Podróżnego z punktu 3.11 będzie pobranie opłaty za rezygnację zgodnie z postanowieniami punktu 6 Warunków Uczestnictwa.</div>
<h2>4. Ubezpieczenia</h2>
<div><b>4.1</b> Wszyscy Podróżni objęci są ubezpieczeniami Signal Iduna Polska TU S.A., ul. Siedmiogrodzka 9, 01-204 Warszawa, infolinia 801 120 120, +48 (22) 50 56 506. Stronami umowy ubezpieczenia jest Podróżny i Ubezpieczyciel.</div>
<div><b>4.2</b> Podróżni uczestniczący w imprezach krajowych objęci są ubezpieczeniem NNW na kwotę 10 000 PLN w wariancie Standard.</div>
<div><b>4.3</b> Podróżni uczestniczący w imprezach zagranicznych objęci są ubezpieczeniem obejmującym w pakiecie podstawowym: koszty leczenia KL do kwoty 25 000 EUR, NNW do kwoty 20 000 PLN, bagaż podróżny do kwoty 1 100 PLN.</div>
<div><b>4.4</b> Ubezpieczenie KL w wersji podstawowej obejmuje ryzyko zaostrzenia choroby przewlekłej.</div>
<div><b>4.5</b> Niepełnoletni Podróżni uczestniczący w zagranicznych wyjazdach dla dzieci i młodzieży podczas których dziecku nie towarzyszy rodzic ani opiekun prawny (wycieczki szkolne, kolonie, obozy itp.), objęci są rozszerzeniem ubezpieczenia o zwrot kosztów transportu i pobytu opiekuna w sytuacji hospitalizacji która nie zakończy się do dnia powrotu do kraju zamieszkania (do kwoty 1000 EUR).</div>
<div><b>4.6</b> Opcjonalne ubezpieczenie Kosztów Rezygnacji nie obejmuje w wersji podstawowej ryzyka zaostrzenia choroby przewlekłej, ryzyka zachorowania na Covid i objęcia kwarantanną – ubezpieczenie można za dopłatą rozszerzyć o te ryzyka. </div>
<div><b>4.7</b> Każdy Podróżny może zawrzeć dodatkowe ubezpieczenie na własny koszt na sumę ubezpieczenia wyższą niż standardowa.</div>
<div><b>4.8</b> Podróżny oświadcza, że został poinformowany o możliwości wykupienia dodatkowego ubezpieczenia kosztów rezygnacji – dodatkowy koszt takiego ubezpieczenia w wersji podstawowej to 3% ceny imprezy. Ubezpieczenie takie wykupić należy w dniu zawarcia Umowy (lub jeśli do rozpoczęcia Imprezy pozostało więcej niż 30 dni - w ciągu 7 dni od daty zawarcia Umowy).</div>

<div><b>4.9</b> W przypadku imprez grupowych, najpóźniej na 3 dni robocze przed datą rozpoczęcia Imprezy, należy dostarczyć do Biura Podróży listę uczestników zawierającą następujące dane osobowe osób uczestniczących w wycieczce: imiona, nazwiska, daty urodzenia.</div>
<div><b>4.10</b> Dochodzenie roszczeń wynikających z ubezpieczenia następuje bezpośrednio przez Podróżnego od Ubezpieczyciela.</div>
<div><b>4.11</b> W przypadku zaistnienia szkody podczas Imprezy należy kontaktować się z czynną całą dobę centralą alarmową Signal Iduna tel. + 48 (22) 846 55 26.</div>
<h2>5. Informacje o obowiązujących przepisach paszportowych i wizowych</h2>
<div><b>5.1</b> Podróżny oświadcza, że został poinformowany o obowiązujących przepisach paszportowych oraz wizowych na wybranej trasie podróży.</div>
<div><b>5.2</b> Podróżny wyjeżdżający do krajów w obrębie Unii Europejskiej musi posiadać ważny dowód osobisty lub paszport (dokument musi być ważny w momencie wjazdu, pobytu oraz wyjazdu z kraju poza granicami RP, bez określenia minimalnego okresu ważności.).</div>
<div><b>5.3</b> Podróżny wyjeżdżający poza obszar Unii Europejskiej musi posiadać ważny stały paszport (ważny minimum 6 miesięcy od planowanej daty powrotu do Polski).</div>
<div><b>5.4</b> Wymóg posiadania paszportu lub dowodu osobistego dotyczy także dzieci niezależnie od ich wieku. Legitymacja szkolna nie jest dokumentem uprawniającym do przekroczenia granicy.</div>
<div><b>5.5</b> Organizator nie ponosi odpowiedzialności za ewentualne: nie posiadanie przez Podróżnego ważnego paszportu/dowodu osobistego, nie przyznanie Podróżnemu wizy przez placówki konsularne państw, do których obowiązuje ruch wizowy, zatrzymanie jego paszportu oraz za odmowę zgody na wjazd przez służby graniczne państw, w których ostateczną decyzję o przekroczeniu granicy podejmują miejscowe służby imigracyjne lub celne, a także za opóźnienie w wydaniu przez placówkę dyplomatyczną wizy, o której mowa powyżej chyba, że opóźnienie to można przypisać Organizatorowi.</div>
<div><b>5.6</b> Informacje o przepisach paszportowych i wizowych opisane w Ofertach, Umowach i Warunkach Uczestnictwa dotyczą obywateli Polskich. Obywatelom innych państw zaleca się kontakt z odpowiednimi placówkami dyplomatycznymi/konsulatami w celu sprawdzenia obowiązujących ich przepisów paszportowych i wizowych.</div>
<h2>6. Rezygnacja z udziału w Imprezie Turystycznej z inicjatywy Podróżnego/Odstąpienie przez Podróżnego od Umowy/Opłaty za odstąpienie od Umowy</h2>
<div><b>6.1</b> Podróżny ma prawo odstąpić od Umowy (zrezygnować z udziału w Imprezie Turystycznej) w każdym czasie przed rozpoczęciem Imprezy.</div>
<div><b>6.2</b> W razie odstąpienia od Umowy (rezygnacji), z zastrzeżeniem wyjątków przewidzianych w Ustawie, Podróżny jest zobowiązany do zapłacenia na rzecz Biura Podróży opłaty za odstąpienie od Umowy (zgodnie z przepisami art. 47 ust. 2 Ustawy), która odpowiada cenie Imprezy Turystycznej pomniejszonej o zaoszczędzone koszty lub wpływy z tytułu alternatywnego wykorzystania danych usług turystycznych.</div>
<div><b>6.3</b> W przypadku rezygnacji z udziału w Imprezie Turystycznej na 30 dni lub więcej niż 30 dni przed datą rozpoczęcia Imprezy, Organizator ustanawia opłatę manipulacyjną za rezygnację w wysokości:</div>
<div><b>a)</b> 50 zł/osoba przy rezygnacji z wycieczki krajowej jednodniowej;</div>
<div><b>b)</b> 100 zł/osoba przy rezygnacji z wycieczki krajowej dwudniowej;</div>
<div><b>c)</b> 250 zł/osoba przy rezygnacji z wycieczki krajowej trzydniowej lub dłuższej;</div>
<div><b>d)</b> 300 zł/osoba przy rezygnacji z wycieczki zagranicznej;</div>
<div><b>6.4</b> Do wyliczenia ostatecznie poniesionych kosztów Biuro Podróży może przystąpić dopiero po dacie zakończenia wyjazdu i rozliczeniu kosztów Imprezy, z której Podróżny nie skorzystał. Rozliczenie i wypłata środków pieniężnych nastąpi niezwłocznie, nie później niż 30 dni kalendarzowych po zakończeniu Imprezy. Organizator informuje, że ukształtowana historycznie orientacyjna średnia wysokość potrącanych kosztów w przypadku rezygnacji z udziału w imprezy turystycznej wynosi:</div>
<div><b>a)</b> około 50% ceny Imprezy – jeśli rezygnacja następuje na 29-14 dni przed rozpoczęciem podróży</div>
<div><b>b)</b> około 70% ceny Imprezy – jeśli rezygnacja następuje na 14-8 dni przed rozpoczęciem podróży</div>
<div><b>c)</b> około 95% ceny Imprezy – jeśli rezygnacja następuje na 7-1 dni przed rozpoczęciem podróży lub w dniu rozpoczęcia podróży.</div>

<h2>7. Odwołanie Imprezy Turystycznej przez Biuro Podróży</h2>
<div><b>7.1</b> Organizator może rozwiązać Umowę i dokonać pełnego zwrotu Podróżnemu wpłat dokonanych z tytułu Imprezy, bez dodatkowego odszkodowania lub zadośćuczynienia, jeżeli:</div>
<div><b>a)</b> nie osiągnie zakładanego minimum grupy (zakładana ilość uczestników wskazana w Umowie) i powiadomi Podróżnego o rozwiązaniu Umowy nie później niż 20 dni przed rozpoczęciem Imprezy Turystycznej trwającej ponad 6 dni, 7 dni przed rozpoczęciem Imprezy Turystycznej trwającej 2-6 dni, 48 godzin przed rozpoczęciem Imprezy Turystycznej trwającej krócej niż 2 dni;</div>
<div><b>b)</b> wystąpią nieuniknione i nadzwyczajne okoliczności i powiadomi Uczestnika/Podróżnego o rozwiązaniu Umowy niezwłocznie przed rozpoczęciem imprezy. Biuro w ww. przypadkach dokonuje zwrotu wszystkich wpłat dokonanych z tytułu Umowy w terminie 14 dni od dnia jej rozwiązania.</div>

<h2>8. Odpowiedzialność Organizatora</h2>
<div><b>8.1</b> Organizator ponosi odpowiedzialność za należyte wykonanie wszystkich usług turystycznych objętych Umową, bez względu na to, czy usługi te mają być wykonane przez Organizatora, czy przez innych dostawców usług turystycznych.</div>
<div><b>8.2</b> Podróżnemu nie przysługuje odszkodowanie lub zadośćuczynienie za niezgodność w przypadku, gdy Organizator udowodni, że:</div>
<div><b>a)</b> winę za niezgodność ponosi Podróżny;</div>
<div><b>b)</b> winę za niezgodność ponosi osoba trzecia, niezwiązana z wykonywaniem usług turystycznych objętych Umową, a niezgodności nie dało się przewidzieć lub uniknąć;</div>
<div><b>c)</b> niezgodność została spowodowana nieuniknionymi i nadzwyczajnymi okolicznościami.</div>
<div><b>8.3</b> W przypadkach innych, niż określone w art. 50 ust. 5 ustawy, Organizator ogranicza odszkodowanie, jakie ma zostać wypłacone przez Organizatora, do trzykrotności ceny Imprezy Turystycznej względem każdego Podróżnego. Ograniczenia tego nie stosuje się w przypadku szkody na osobie lub szkody spowodowanej umyślnie lub w wyniku niedbalstwa.</div>
<div><b>8.4</b> W przypadku gdy Podróżny znalazł się w trudnej sytuacji w związku z wystąpieniem nieuniknionych i nadzwyczajnych okoliczności w rozumieniu art. 4 pkt 15 Ustawy, Biuro Podróży udziela Podróżnemu odpowiedniej pomocy. Organizator może żądać opłaty z tytułu udzielenia pomocy, w szczególności jeżeli trudna sytuacja powstała z wyłącznej winy umyślnej Podróżnego lub w wyniku jego rażącego niedbalstwa.</div>
<div><b>8.5</b> Organizator nie ponosi odpowiedzialności za skutki wynikłe dla Podróżnego z faktu niezgłoszenia się w terminie na miejsce zbiórki bądź zatrzymania przez krajowe czy zagraniczne służby graniczne, celne, policję lub inne władze bądź nie posiadania przez Podróżnego ważnego paszportu, dowodu osobistego lub wizy. W powyższych przypadkach Organizator rozliczy dokonane przez Podróżnego wpłaty według punktu 6.2 Warunków Uczestnictwa.</div>
<h2>9. Odpowiedzialność Podróżnego</h2>
<div><b>9.1</b> Podróżny zobowiązany jest przestrzegać przepisów celnych, dewizowych i porządkowych obowiązujących w Polsce, w krajach tranzytowych i w kraju docelowym, jak również zaleceń pilota wycieczki.</div>
<div><b>9.2</b> Za wszelkie zniszczenia, uszkodzenia i inne szkody powstałe z winy Podróżnego w trakcie trwania Imprezy Turystycznej odpowiedzialność (prawną, finansową, odszkodowawczą) ponosi Podróżny.</div>
<div><b>9.3</b> Za szkody wyrządzone podczas Imprezy przez osoby małoletnie odpowiadają rodzice, opiekunowie prawni lub osoby, którym na czas trwania Imprezy została powierzona opieka nad małoletnimi dziećmi.</div>
<h2>10. Przetwarzanie i ochrona danych osobowych</h2>
<div><b>10.1</b> Administratorem danych osobowych podanych przez Podróżnych jest Biuro Podróży RAFA – Rafał Latos.</div>
<div><b>10.2</b> Dane osobowe Podróżnych przetwarzane są w celu realizacji Umowy i będą udostępniane innym odbiorcom takim jak np.: linie lotnicze, autokarowe, promowe, hotelom, firmie ubezpieczeniowej, pilotowi grupy w celu realizacji zawartej Umowy. Podpisując Umowę Podróżny wyraża zgodę na przetwarzanie danych osobowych w wyżej wymienionych celach.</div>
<div><b>10.3</b> Podstawą przetwarzania danych osobowych jest zawarta Umowa.</div>
<div><b>10.4</b> Podanie danych osobowych jest dobrowolne, jednak niezbędne do zawarcia Umowy. W przypadku niepodania danych niemożliwe jest zawarcie Umowy.</div>
<div><b>10.5</b> Dane osobowe Podróżnych nie podlegają zautomatyzowanemu podejmowaniu decyzji ani profilowaniu.</div>
<div><b>10.6</b> W zależności od kierunku podróży, w celu niezbędnym do realizacji Umowy, dane osobowe mogą być przetwarzane przez odbiorców danych w Państwach Trzecich, które nie zapewniają odpowiedniego poziomu ochrony danych osobowych. Informacje czy dany kraj zapewnia skuteczną ochronę, można uzyskać bezpośrednio u Administratora.</div>
<div><b>10.7</b> Dane osobowe będą przechowywane przez okres, w którym osoba, której dane dotyczą może dochodzić roszczeń z tytułu niewykonania lub nienależytego wykonania Umowy.</div>
<h2>11. Reklamacje</h2>
<div><b>11.1</b> Jeżeli w trakcie Imprezy Podróżny stwierdza wadliwe wykonywanie Umowy powinien niezwłocznie zawiadomić o tym wykonawcę usługi (pilota) oraz Organizatora. Podróżnemu przysługuje również prawo do złożenia reklamacji w terminie do 30 dni kalendarzowych od daty zakończenia Imprezy, której reklamacja dotyczy. Reklamację złożyć można w siedzibie Organizatora, przekazać pismo reklamacyjne pilotowi wycieczki lub wysłać ją na email: rafa@bprafa.pl</div>
<div><b>11.2</b> Reklamacje rozpatrywane są przez Organizatora bez zbędnej zwłoki, jednak nie później niż w terminie 30 dni kalendarzowych licząc od dnia ich wpływu, przy czym do zachowania terminu wystarczy wysłanie (np. nadanie przesyłki w placówce pocztowej lub wysłanie maila) odpowiedzi przed jego upływem.</div>
<div><b>11.3</b> Pilot wycieczki nie jest uprawniony do uznawania roszczeń Podróżnego.</div>
<div><b>11.4</b> Jeżeli z przyczyn niezależnych od Podróżnego w trakcie trwania danej Imprezy Turystycznej Organizator nie wykonuje przewidzianych w Umowie usług, stanowiących istotną część programu tej Imprezy, wówczas Organizator wykona w ramach tej Imprezy, bez obciążania Podróżnego dodatkowymi kosztami, odpowiednie świadczenie zastępcze. Jeżeli jakość świadczenia zastępczego jest niższa od jakości usługi określonej w Umowie, Podróżny może zażądać odpowiedniego obniżenia ustalonej ceny Imprezy.</div>
<div><b>11.5</b> Podmiotami uprawnionymi do prowadzenia spraw z zakresu postępowań pozasądowych dotyczących usług turystycznych są Inspekcje Handlowe. Wykaz Inspekcji Handlowych znajduje się na stronie UOKiK.</div>
<h2>12. Postanowienia końcowe</h2>
<div><b>12.1</b> Ewentualne spory wynikające z tytułu realizacji Umowy będą rozstrzygane polubownie, a w przypadku braku porozumienia przez sądy powszechne właściwe według przepisów Kodeksu Postępowania Cywilnego.</div>
<div><b>12.2</b> W sprawach nie uregulowanych Umową mają zastosowanie przepisy Ustawy o Imprezach Turystycznych i Powiązanych Usługach Turystycznych, Kodeksu Cywilnego, oraz inne przepisy dotyczące ochrony konsumenta w tym rozporządzenie Unii Europejskiej dotyczące konsumentów usług turystycznych.</div>
<div><b>12.3</b> Podróżny może zapoznać się z treścią Ustawy, w tym przepisów powołanych w Warunkach Uczestnictwa, na stronie internetowej www.sejm.gov.pl.</div>
<div><b>12.4</b> Warunki Uczestnictwa obowiązują do umów zawartych po 1 września 2023.</div>


    <!-- end - Warunki uczestnictwa  -->


    



            <!-- End - Warunki uczestnictwa -->



            <!-- Start - Standardowy formularz rezerwacyjny -->

            <h1>STANDARDOWY FORMULARZ INFORMACYJNY DO UMÓW O UDZIAŁ W IMPREZIE TURYSTYCZNEJ</h1>

            <div>Zaoferowane Państwu połączenie usług turystycznych stanowi imprezę turystyczną w rozumieniu dyrektywy (UE) 2015/2302.</div>
            <div>W związku z powyższym będą Państwu przysługiwały wszystkie prawa UE mające zastosowanie do imprez turystycznych.</div>
            <div>Biuro Podróży RAFA będzie ponosiło pełną odpowiedzialność za należytą realizację całości imprezy turystycznej.</div>
            <div>Ponadto, zgodnie z wymogami prawa, Biuro Podróży RAFA posiada zabezpieczenie w celu zapewnienia zwrotu Państwu wpłat i, jeżeli transport jest elementem imprezy turystycznej, zapewnienia Państwa powrotu do kraju w przypadku, gdyby Biuro Podróży RAFA stało się niewypłacalne.</div>
            <br />
            <div>Najważniejsze prawa zgodnie z dyrektywą (UE) 2015/2302:</div>
            <ul>
                <li>Przed zawarciem umowy o udział w imprezie turystycznej podróżni otrzymają wszystkie niezbędne informacje na temat imprezy turystycznej.</li>
                <li>Zawsze co najmniej jeden przedsiębiorca ponosi odpowiedzialność za należyte wykonanie wszystkich usług turystycznych objętych umową.</li>
                <li>Podróżni otrzymują awaryjny numer telefonu lub dane punktu kontaktowego, dzięki którym mogą skontaktować się z organizatorem turystyki lub agentem turystycznym.</li>
                <li>Podróżni mogą przenieść imprezę turystyczną na inną osobę, powiadamiając o tym w rozsądnym terminie, z zastrzeżeniem ewentualnych dodatkowych kosztów.</li>
                <li>Cena imprezy turystycznej może zostać podwyższona jedynie wtedy, gdy wzrosną określone koszty (na przykład koszty paliwa) i zostało to wyraźnie przewidziane w umowie; w żadnym przypadku podwyżka ceny nie może nastąpić później niż 20 dni przed rozpoczęciem imprezy turystycznej. Jeżeli podwyżka ceny przekracza 8% ceny imprezy turystycznej, podróżny może rozwiązać umowę. Jeżeli organizator turystyki zastrzega sobie prawo do podwyższenia ceny, podróżny ma prawo do obniżki ceny, jeżeli obniżyły się odpowiednie koszty.</li>
                <li>Podróżni mogą rozwiązać umowę bez ponoszenia jakiejkolwiek opłaty za rozwiązanie i uzyskać pełen zwrot wszelkich wpłat, jeżeli jeden z istotnych elementów imprezy turystycznej, inny niż cena, zmieni się w znaczący sposób.</li>
                <li>Jeżeli przedsiębiorca odpowiedzialny za imprezę turystyczną odwoła ją przed jej rozpoczęciem, podróżni mają prawo do zwrotu wpłat oraz w stosownych przypadkach do rekompensaty.</li>
                <li>W wyjątkowych okolicznościach – na przykład jeżeli w docelowym miejscu podróży występują poważne problemy związane z bezpieczeństwem, które mogą wpłynąć na imprezę turystyczną – podróżni mogą, przed rozpoczęciem imprezy turystycznej, rozwiązać umowę bez ponoszenia jakiejkolwiek opłaty za rozwiązanie.</li>
                <li>Ponadto podróżni mogą w każdym momencie przed rozpoczęciem imprezy turystycznej rozwiązać umowę za odpowiednią i możliwą do uzasadnienia opłatą.</li>
                <li>Jeżeli po rozpoczęciu imprezy turystycznej jej znaczące elementy nie mogą zostać zrealizowane zgodnie z umową, będą musiały zostać zaproponowane, bez dodatkowych kosztów, odpowiednie alternatywne usługi. </li>
                <li>W przypadku gdy usługi nie są świadczone zgodnie z umową, co istotnie wpływa na realizację imprezy turystycznej, a organizator turystyki nie zdoła usunąć problemu, podróżni mogą rozwiązać umowę bez opłaty za rozwiązanie.</li>
                <li>Podróżni są również uprawnieni do otrzymania obniżki ceny lub rekompensaty za szkodę w przypadku niewykonania lub nienależytego wykonania usług turystycznych.</li>
                <li>Organizator turystyki musi zapewnić pomoc podróżnemu, który znajdzie się w trudnej sytuacji.</li>
                <li>W przypadku gdy organizator turystyki stanie się niewypłacalny, wpłaty zostaną zwrócone. Jeżeli organizator turystyki stanie się niewypłacalny po rozpoczęciu imprezy turystycznej i jeżeli impreza turystyczna obejmuje transport, zapewniony jest powrót podróżnych do kraju. Biuro Podróży RAFA wykupiło w Towarzystwie Ubezpieczeń Signal Iduna zabezpieczenie na wypadek niewypłacalności. Podróżni mogą kontaktować się z tym podmiotem lub, w odpowiednich przypadkach, z właściwym organem: Urząd Marszałkowski Województwa Mazowieckiego, Departament Kultury, Sportu i Turystyki, ul. Bertolta Brechta 3, 03-472 Warszawa, e-mail: dkpit@mazovia.pl, tel. (+48 22) 5979-501, (+48 22) 5979-54 jeżeli z powodu niewypłacalności Biura Podróży RAFA, dojdzie do odmowy świadczenia usług.</li>
            </ul>

            <div>Dyrektywa (UE) 2015/2302:</div>
            <div>https://eur-lex.europa.eu/legal-content/PL/TXT/PDF/?uri=CELEX:32015L2302&from=PL</div>
            <div>Przetransponowana do prawa krajowego:</div>
            <div>http://prawo.sejm.gov.pl/isap.nsf/download.xsp/WDU20170002361/O/D20172361.pdf</div>




            <!-- End - Standardowy formularz rezerwacyjny -->

            <h2>Dokument wygenerowany elektronicznie</h2>
            <p>nie wymaga pięczęci ani podpisu</p>











</body>

</html>