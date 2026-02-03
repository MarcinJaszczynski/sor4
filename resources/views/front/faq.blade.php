@extends('front.layout.master')

@section('main_content')

<style>
    .faq-container {
        max-width: 900px;
        margin: 0 auto;
    }
    .faq-item {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-bottom: 12px;
        background: #fff;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
    }
    .faq-item:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        border-color: #d1d5db;
    }
    .faq-question {
        padding: 18px 25px;
        cursor: pointer;
        position: relative;
        font-weight: 600;
        font-size: 16px;
        color: #333;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fff;
        transition: background-color 0.2s;
    }
    .faq-question:hover {
        background-color: #f9fafb;
        color: #1a56db;
    }
    .faq-question.active {
        background-color: #eff6ff;
        color: #1a56db;
        border-bottom: 1px solid #e5e7eb;
    }
    .faq-answer {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.35s ease-out;
        background: #fff;
    }
    .faq-answer-inner {
        padding: 20px 25px;
        color: #555;
        line-height: 1.6;
    }
    .faq-answer-inner p:last-child {
        margin-bottom: 0;
    }
    
    /* Plus/Minus Icon */
    .faq-icon {
        width: 24px;
        height: 24px;
        position: relative;
        flex-shrink: 0;
        margin-left: 15px;
    }
    .faq-icon::before,
    .faq-icon::after {
        content: '';
        position: absolute;
        background-color: #1a56db;
        transition: transform 0.3s ease;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    .faq-icon::before {
        width: 14px; /* horizontal line */
        height: 2px;
    }
    .faq-icon::after {
        width: 2px; /* vertical line */
        height: 14px;
    }
    .faq-question.active .faq-icon::after {
        transform: translate(-50%, -50%) rotate(90deg); /* Rotate to make it flat like minus if both are same color */
        /* Actually simpler: just hide vertical line to make minus */
        opacity: 0; 
    }
    /* Or better rotation for cross -> minus */
    /* Let's stick to rotating the whole icon or fading the vertical bar */

</style>

<div class="page-top">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="breadcrumb-container">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Start</a></li>
                        <li class="breadcrumb-item active">F.A.Q.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container pt_50 pb_50">
    <div class="header mb-5 text-center">
        <h1 style="font-weight: 700; color: #1a56db; margin-bottom: 15px;">Najczęściej zadawane pytania</h1>
        <p class="text-muted">Znajdź odpowiedzi na pytania dotyczące naszych wycieczek, rezerwacji i płatności.</p>
    </div>

    <div class="faq-container">
        
        <!-- Pytanie 1 -->
        <div class="faq-item">
            <div class="faq-question">
                Jakie wycieczki oferuje Biuro Podróży RAFA?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Organizujemy wycieczki dla grup zorganizowanych — przede wszystkim dla szkół, firm i instytucji. W ofercie znajdują się wycieczki jednodniowe i wielodniowe, zarówno krajowe, jak i zagraniczne. Realizujemy wyjazdy autokarowe, lotnicze, promowe oraz z przejazdem koleją.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 2 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy mogę zaplanować wycieczkę „szytą na miarę”?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak — przygotowujemy programy indywidualne dopasowane do wieku uczestników, celu wyjazdu (edukacja, integracja, przygoda), budżetu i oczekiwań Zamawiającego.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 3 -->
        <div class="faq-item">
            <div class="faq-question">
                Skąd startują wycieczki autokarowe?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Autokary podstawiamy w miejsce wskazane przez Zamawiającego – zazwyczaj pod szkołę. Wyszukiwarka na stronie umożliwia wybór miasta lub jego okolic jako miejsca rozpoczęcia wyjazdu, co pozwala wygodnie znaleźć wycieczki z najbliższej lokalizacji.</p>
                    <p>Na stronie możesz wybrać wyjazd z następujących miast: Biała Podlaska, Białystok, Bielsko-Biała, Bydgoszcz, Bytom, Chełm, Ciechanów, Częstochowa, Elbląg, Gdańsk, Gdynia, Gorzów Wielkopolski, Jelenia Góra, Kalisz, Katowice, Kielce, Konin, Koszalin, Kraków, Krosno, Legionowo, Legnica, Leszno, Lublin, Łomża, Łódź, Mińsk Mazowiecki, Nowy Sącz, Olsztyn, Opole, Ostrołęka, Piła, Piotrków Trybunalski, Płock, Poznań, Przemyśl, Radom, Rzeszów, Siedlce, Sieradz, Skierniewice, Słupsk, Sopot, Suwałki, Szczecin, Tarnobrzeg, Tarnów, Toruń, Wałbrzych, Warszawa, Włocławek, Wrocław, Zakopane, Zamość, Zielona Góra.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 4 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy organizujecie wycieczki zagraniczne?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – organizujemy wycieczki zagraniczne do wszystkich destynacji europejskich. Nie realizujemy wyjazdów poza Europę.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 5 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy organizujecie wycieczki lotnicze?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – organizujemy wycieczki z transportem lotniczym.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 6 -->
        <div class="faq-item">
            <div class="faq-question">
                Jak dokonać rezerwacji wycieczki?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Rezerwacji można dokonać telefonicznie lub mailowo. Po zaakceptowaniu oferty zostanie przygotowana umowa/potwierdzenie rezerwacji.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 7 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy trzeba wpłacić zaliczkę?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – standardowo przy zawarciu umowy wymagana jest wpłata zaliczki w wysokości do 30% wartości imprezy.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 8 -->
        <div class="faq-item">
            <div class="faq-question">
                Kiedy należy opłacić całość?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Dopłata do pełnej ceny powinna zostać uregulowana:</p>
                    <ul>
                        <li>14 dni przed wyjazdem przy wycieczkach krajowych</li>
                        <li>30 dni przed wyjazdem przy wycieczkach zagranicznych.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Pytanie 9 -->
        <div class="faq-item">
            <div class="faq-question">
                Jak mogę zapłacić?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Płatności można dokonać przelewem bankowym, blikiem lub gotówką w siedzibie firmy. Szczegóły płatności podawane są w umowie/potwierdzeniu rezerwacji.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 10 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy mogę zapłacić online?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Przyjmujemy płatności przelewem bankowym oraz przez BLIK.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 11 -->
        <div class="faq-item">
            <div class="faq-question">
                Jak otrzymać fakturę za wycieczkę?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Fakturę za zakup wycieczki wystawiamy po jej zakończeniu. Aby otrzymać fakturę należy przed rozpoczęciem wycieczki przysłać wniosek o fakturę na adres: <a href="mailto:rafa@bprafa.pl">rafa@bprafa.pl</a>. Wniosek powinien zawierać: Imię i nazwisko Zamawiającego, imię i nazwisko uczestnika wycieczki, nr rezerwacji, nazwę wycieczki, termin wycieczki.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 12 -->
        <div class="faq-item">
            <div class="faq-question">
                Gdzie mogę znaleźć dokumenty takie jak warunki uczestnictwa lub ubezpieczenie?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Wszystkie niezbędne dokumenty, w tym Warunki uczestnictwa, ubezpieczenia, politykę RODO i formularze, są dostępne w zakładce „Dokumenty” na stronie.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 13 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy uczestnicy wycieczek szkolnych są ubezpieczeni?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – wszyscy uczestnicy wycieczek są objęci ubezpieczeniem. W kraju zapewniamy NNW i Assistance (do 30 000 zł), a za granicą ubezpieczenie kosztów leczenia, OC i bagażu (KL do 300 000 euro).</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 14 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy ubezpieczenie NNW RP jest konieczne?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Nie – nie jest obowiązkowe, jednak wszyscy uczestnicy są objęci ubezpieczeniem NNW do 30 000 zł.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 15 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy ubezpieczenie KL jest konieczne?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – wszyscy uczestnicy są objęci obowiązkowym ubezpieczeniem kosztów leczenia do 300 000 euro.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 16 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy ubezpieczenie KL obejmuje transport medyczny?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – ubezpieczenie obejmuje transport medyczny bez limitu kosztów i nie pomniejsza sumy przeznaczonej na leczenie.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 17 -->
        <div class="faq-item">
            <div class="faq-question">
                Co jeśli chcę zrezygnować z wycieczki?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Rezygnacja (odstąpienie od umowy) jest możliwa w każdym czasie przed wyjazdem, lecz wiąże się z opłatą za odstąpienie, która zostanie naliczona zgodnie z zapisami punktu 6 Ogólnych Warunków Uczestnictwa.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 18 -->
        <div class="faq-item">
            <div class="faq-question">
                Jeśli zrezygnuję z wycieczki to otrzymam zwrot wszystkich wpłaconych pieniędzy?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Nie. W przypadku rezygnacji z wycieczki potrącane są koszty rezygnacji. Ich wysokość zależy od terminu rezygnacji, rodzaju wycieczki oraz faktycznych kosztów poniesionych przez Organizatora. Zasady ustalania kosztów rezygnacji określone są w Ogólnych Warunkach Uczestnictwa (pkt 6).<br>
                    Wyjątek: posiadanie ubezpieczenia kosztów rezygnacji.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 19 -->
        <div class="faq-item">
            <div class="faq-question">
                Co jeśli uczeń nie pojedzie w ostatniej chwili?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Obowiązują warunki rezygnacji opisane w Ogólnych Warunkach uczestnictwa.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 20 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy cena wycieczki obejmuje ubezpieczenie kosztów rezygnacji?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Nie. W cenie wycieczki nie uwzględniono ubezpieczenia kosztów rezygnacji. Jest to opcja dodatkowa, którą każdy podróżny może dokupić indywidualnie. Ubezpieczenie kosztów rezygnacji kosztuje 3,2% ceny wycieczki.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 21 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy mogę zwiększyć sumę ubezpieczenia?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – podróżny może zwiększyć sumę ubezpieczenia. Prosimy w tej sprawie o kontakt z biurem przed zawarciem umowy.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 22 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy wycieczka szkolna musi mieć opiekuna?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak — zgodnie z praktyką organizacji imprez turystycznych, wycieczki szkolne powinny mieć wyznaczonych opiekunów (nauczycieli) odpowiedzialnych za grupę. Ceny wycieczek prezentowane na stronie internetowej zawierają miejsca gratis dla opiekunów.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 23 -->
        <div class="faq-item">
            <div class="faq-question">
                Jak zgłosić listę uczestników?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>W przypadku imprez grupowych listę uczestników wraz z danymi osobowymi (imiona, nazwiska oraz daty urodzenia) należy przesłać najpóźniej 7 dni przed wyjazdem. Ze względu na RODO prosimy o przesłanie listy w postaci zaszyfrowanego pliku lub — na życzenie — udostępnimy link do bezpośredniego przesłania danych na nasz serwer.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 24 -->
        <div class="faq-item">
            <div class="faq-question">
                Jakie są warunki uczestnictwa?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Warunki udziału w imprezach, w tym zasady płatności, ubezpieczenia i standardy realizacji wyjazdów, opisane są w Ogólnych Warunkach Uczestnictwa, dostępnych w sekcji „dokumenty” na stronie www.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 25 -->
        <div class="faq-item">
            <div class="faq-question">
                Jak złożyć reklamację?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Reklamację można zgłosić mailowo lub osobiście w siedzibie biura najpóźniej do 30 dni od zakończenia imprezy.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 26 -->
        <div class="faq-item">
            <div class="faq-question">
                Jak mogę się z Wami skontaktować?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Nasze dane kontaktowe to: Biuro Podróży RAFA, ul. Marii Konopnickiej 6, 00-491 Warszawa, tel. +48 606 102 243, e-mail: <a href="mailto:rafa@bprafa.pl">rafa@bprafa.pl</a></p>
                </div>
            </div>
        </div>

        <!-- Pytanie 27 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy opiekunowie płacą za wycieczkę?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Nie – opiekunowie mają zapewnione miejsca gratis (1 miejsce gratis na każde rozpoczęte 15 płatnych uczestników). W przypadku chęci zwiększenia liczby opiekunów, nadliczbowi opiekunowie są płatni (zazwyczaj tak samo jak uczniowie).</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 28 -->
        <div class="faq-item">
            <div class="faq-question">
                Ilu opiekunów powinno jechać z grupą?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Zalecamy 1 opiekuna na 15 uczniów. W przypadku wyjazdu dla młodzieży ze szkół ponadpodstawowych dopuszcza się 1 opiekuna na 20 uczniów.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 29 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy opiekunowie muszą płacić za bilety wstępu?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Nie – opiekunowie uczestniczą w wycieczce gratis. Dotyczy to również biletów wstępu do zwiedzanych obiektów.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 30 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy pilot jest zapewniony?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak, w cenie wycieczki zapewniamy pilota na cały czas trwania wycieczki. Pilot nie pełni funkcji przewodnika.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 31 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy przewodnik jest zapewniony?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak, w cenie wycieczki zapewniamy lokalnych przewodników tam, gdzie jest to wymagane lub zalecane. W praktyce niemal wszystkie punkty objęte programem są oprowadzane przez przewodników lokalnych.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 32 -->
        <div class="faq-item">
            <div class="faq-question">
                Jakim autokarem odbywa się przejazd?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Korzystamy wyłącznie z licencjonowanych przewoźników, nowoczesnych autokarów spełniających normy UE, wyposażonych w pasy bezpieczeństwa na wszystkich fotelach, klimatyzację, nagłośnienie i WC (przy trasach długodystansowych).</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 33 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy można sprawdzić autokar przed wyjazdem?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – na życzenie szkoły możliwa jest kontrola autokaru przez Policję lub ITD przed wyjazdem. Formalności związane z poproszeniem odpowiednich służb o przeprowadzenie kontroli spoczywają na Zamawiającym lub zlecającym kontrolę. Kontrola odbywa się w miejscu podstawienia autokaru na 30 minut przed planowaną godziną wyjazdu.</p>
                    <p>Na życzenie Zamawiającego możemy również dostarczyć wydruk z portalu bezpiecznyautobus.gov.pl który potwierdza aktualne badania techniczne oraz ubezpieczenia pojazdu.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 34 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy kierowcy mają wymagane przerwy?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – przejazdy są planowane zgodnie z przepisami o czasie pracy kierowców.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 35 -->
        <div class="faq-item">
            <div class="faq-question">
                Jakiego standardu są hotele i ośrodki noclegowe?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Współpracujemy z hotelami, pensjonatami i ośrodkami wypoczynkowymi sprawdzonymi pod kątem bezpieczeństwa i standardów sanitarnych. W zdecydowanej większości przypadków grupy kwaterowane są w hotelach i pensjonatach o standardzie **/***, w pokojach max 4 osobowych.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 36 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy pokoje są z łazienkami?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – uczestnicy zawsze są zakwaterowani w pokojach z prywatnymi łazienkami.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 37 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy można zamówić dietę (wegetariańską, bezglutenową itp.)?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – wystarczy zgłosić to przed wyjazdem, a my przekażemy informacje do punktów żywieniowych. W niektórych przypadkach zapewnienie specjalistycznej diety może wiązać się z dodatkowymi opłatami.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 38 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy wymagane są dokumenty tożsamości?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>W przypadku wycieczek krajowych dokumenty tożsamości nie są wymagane.</p>
                    <p>Przy wyjazdach zagranicznych wymagany jest ważny dowód osobisty lub paszport (również dla dzieci).</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 39 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy w strefie przygranicznej na wycieczce szkolnej wystarczą legitymacje szkolne?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Nie – każdy wyjazd za granicę RP wymaga posiadania ważnego dowodu osobistego lub paszportu.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 40 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy potrzebna jest karta EKUZ?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Nie jest obowiązkowa, ale zalecana – uzupełnia standardowe ubezpieczenie turystyczne.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 41 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy pomagacie w odprawach lotniczych?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – pilot lub koordynator pomaga w odprawach grupowych na lotnisku. W przypadku lotów na których przewoźnik wymaga odprawy on-line każdy podróżny dokonuje samodzielnie odprawy on-line.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 42 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy walutę trzeba wymieniać wcześniej?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Zalecamy aby walutę wymienić przed rozpoczęciem wycieczki. W większości krajów można płacić kartą lub wypłacić gotówkę na miejscu. Przed wyjazdem przekazujemy szczegółowe informacje.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 43 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy cena wycieczki może się zmienić?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Cena może ulec zmianie tylko w wyjątkowych przypadkach, np. gwałtownych zmian kursów walut, cen paliwa lub opłat lotniskowych, zgodnie z umową i Ustawą o Imprezach Turystycznych. Dla wszystkich Imprez Turystycznych dla których od dnia zawarcia Umowy do dnia rozpoczęcia Imprezy pozostaje nie więcej niż 90 dni udzielamy bezpłatnej gwarancji niezmienności ceny.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 44 -->
        <div class="faq-item">
            <div class="faq-question">
                Co dokładnie zawiera cena?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Cena zawiera wszystkie koszty niezbędne do realizacji wycieczki takie jak m.in. transport, noclegi, wyżywienie, program, bilety wstępu, pilota, ubezpieczenie i opiekę organizacyjną. W każdej ofercie prezentowanej na stronie internetowej w sekcji „W cenie” znajdują się szczegółowe informacje co zawiera cena.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 45 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy ceny na stronie internetowej są aktualne?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak, ceny widoczne na stronie internetowej są na bieżąco aktualizowane w czasie rzeczywistym i zawsze są aktualne.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 46 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy są jakieś ukryte koszty?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Nie – wszystkie koszty są jasno określone w ofercie i umowie.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 47 -->
        <div class="faq-item">
            <div class="faq-question">
                Co jeśli któryś z uczestników zachoruje przed wyjazdem?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>W zależności od terminu rezygnacji uczestnik otrzymuje zwrot części ceny zgodnie z postanowieniami punktu 6 Ogólnych Warunków Uczestnictwa. Jeśli podróżny posiadał ubezpieczenie kosztów rezygnacji, a powodem rezygnacji było nagłe zachorowanie, wówczas ubezpieczyciel zwróci podróżnemu 100% poniesionych kosztów.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 48 -->
        <div class="faq-item">
            <div class="faq-question">
                Co jeśli pogoda uniemożliwi realizację programu?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>W takich przypadkach pilot organizuje program zastępczy o porównywalnej wartości.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 49 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy program może ulec zmianie?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – ale tylko w wyjątkowych sytuacjach (np. strajki, pogoda, decyzje lokalnych władz) program może zostać zmodyfikowany dla dobra grupy.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 50 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy terminy wycieczek szkolnych są narzucone przez Biuro Podróży?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Nie. Wycieczki organizujemy w terminach wskazanych przez Zamawiającego i optymalnych dla potrzeb i oczekiwań grupy.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 51 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy można zarezerwować wycieczkę z dużym wyprzedzeniem?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – wiele szkół rezerwuje wyjazdy nawet 6–12 miesięcy wcześniej.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 52 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy organizujecie wyjazdy firmowe i integracyjne?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – oferujemy wyjazdy incentive, szkoleniowe, integracyjne i eventowe.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 53 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy można zorganizować wyjazd last minute?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>W wielu przypadkach tak. Dużo zależy jednak od terminu i dostępności poszczególnych świadczeń. Zapraszamy do kontaktu w tej sprawie.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 54 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy rodzice mogą płacić indywidualnie?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – możliwe są wpłaty indywidualne za każdego uczestnika osobno.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 55 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy każdy uczestnik dostaje potwierdzenie wpłaty?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – na życzenie wystawiamy faktury dla każdego uczestnika.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 56 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy można płacić w ratach?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Płatność za każdą wycieczkę rozbita jest na dwie raty – zaliczkę i dopłatę do pełnej ceny.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 57 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy mogę zapłacić od razu całość ceny za wycieczkę i nie rozbijać jej na zaliczkę i dopłatę?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Nie – zgodnie z przepisami organizator na więcej niż 30 dni przed wyjazdem może przyjąć przedpłatę w wysokości maksymalnie 30% ceny. Dopłata do pełnej ceny jest więc możliwa nie wcześniej niż 30 dni przed planowanym rozpoczęciem wycieczki.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 58 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy faktura może być wystawiona na radę rodziców lub stowarzyszenie?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – fakturę można wystawić na dowolny podmiot wskazany we wniosku.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 59 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy wystawiacie faktury zaliczkowe?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – na życzenie możemy wystawić fakturę zaliczkową za wpłaconą część kwoty.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 60 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy można otrzymać duplikat faktury?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – w każdej chwili można zwrócić się do biura o duplikat dokumentu</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 61 -->
        <div class="faq-item">
            <div class="faq-question">
                Ile bagażu można zabrać do autokaru?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Do autokaru można zabrać bagaż główny do 30 kg oraz bagaż podręczny do 5 kg.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 62 -->
        <div class="faq-item">
            <div class="faq-question">
                Ile bagażu można zabrać do pociągu?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Nie ma formalnego limitu liczby sztuk bagażu, ale bagaż nie powinien przeszkadzać innym pasażerom ani zagrażać bezpieczeństwu.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 63 -->
        <div class="faq-item">
            <div class="faq-question">
                Ile bagażu można zabrać do samolotu?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Zgodnie z regulaminem przewoźnika. Zazwyczaj bagaż główny do 23 kg i bagaż podręczny do 8 kg.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 64 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy można wykupić przelot dla grupy tylko z bagażem podręcznym?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Nie – wszystkie bilety grupowe są wystawiane w opcji z bagażem podręcznym i rejestrowanym.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 65 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy dzieci muszą mieć specjalny strój?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Nie. Strój musi być jednak adekwatny do spodziewanej pogody oraz rodzaju wycieczki.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 66 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy trzeba zabierać własne ręczniki?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Zależy od obiektu noclegowego – zazwyczaj ręczniki są zapewnione, zdarzają się jednak pensjonaty, które ich nie zapewniają.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 67 -->
        <div class="faq-item">
            <div class="faq-question">
                Co jeśli uczeń zachoruje w trakcie wycieczki?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Zapewniamy pomoc pilota, kontakt z ubezpieczycielem i opiekę medyczną.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 68 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy wszystkie bilety są wliczone w cenę?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – wszystkie atrakcje zawarte w programie są uwzględnione w cenie wycieczki (w przypadku wycieczek zagranicznych jest to część ceny wyrażona w walucie obcej).</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 69 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy można dodać dodatkowe atrakcje?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – na etapie planowania wycieczki można rozszerzyć program o muzea, parki rozrywki, warsztaty itp.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 70 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy można skrócić lub wydłużyć wycieczkę?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – program jest elastyczny i dopasowywany do potrzeb grupy. Na etapie planowania wycieczki można wprowadzić modyfikacje czasu trwania wycieczki.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 71 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy biuro posiada wpis do rejestru organizatorów turystyki?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – działamy jako legalny organizator turystyki zgodnie z polskim prawem.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 72 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy wyjazdy są objęte gwarancją ubezpieczeniową?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – każda impreza jest objęta obowiązkową gwarancją turystyczną.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 73 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy wracają do Was ci sami klienci?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – wiele szkół organizuje z nami wyjazdy co roku.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 74 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy można dopisać ucznia po zamknięciu listy?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak, o ile są wolne miejsca w autokarze i noclegach.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 75 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy można zamienić uczestnika (np. inne dziecko)?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – w większości przypadków wystarczy korekta listy lub aneks do umowy.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 76 -->
        <div class="faq-item">
            <div class="faq-question">
                Kto podpisuje umowę – szkoła czy rodzice?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Zależnie od modelu i decyzji grupy. Możliwe są następujące rozwiązania:</p>
                    <ul>
                        <li>Umowy indywidualne z każdym rodzicem z osobna</li>
                        <li>Umowa zbiorcza zawarta z przedstawicielem rodziców który uzyskał od pozostałych rodziców zgodę na reprezentowanie</li>
                        <li>Umowa zbiorcza ze szkołą</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Pytanie 77 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy szkoła dostaje fakturę zbiorczą?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – jeśli to szkoła jest Zamawiającym.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 78 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy rodzice mogą dostać fakturę imienną?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Tak – na życzenie (jeśli Zamawiającym nie jest szkoła).</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 79 -->
        <div class="faq-item">
            <div class="faq-question">
                Co jeśli pada deszcz?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Program jest realizowany z modyfikacjami.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 80 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy zwracacie pieniądze za niewykorzystane atrakcje?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Jeśli coś nie może się odbyć – tak, lub jest zastępowane równoważną atrakcją.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 81 -->
        <div class="faq-item">
            <div class="faq-question">
                Jak złożyć reklamację po wycieczce?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Mailowo lub pisemnie – w ciągu 30 dni od zakończenia wyjazdu.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 82 -->
        <div class="faq-item">
            <div class="faq-question">
                Co może być podstawą reklamacji?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Niezrealizowane świadczenia, niewywiązanie się przez Organizatora z umowy w całości lub w części.</p>
                </div>
            </div>
        </div>

        <!-- Pytanie 83 -->
        <div class="faq-item">
            <div class="faq-question">
                Czy odpowiadacie za rzeczy zagubione przez ucznia?
                <div class="faq-icon"></div>
            </div>
            <div class="faq-answer">
                <div class="faq-answer-inner">
                    <p>Nie – ale pomagamy w ich odzyskaniu.</p>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const questions = document.querySelectorAll('.faq-question');
        
        questions.forEach(question => {
            question.addEventListener('click', function() {
                const isActive = this.classList.contains('active');
                
                // Close all other FAQs
                document.querySelectorAll('.faq-question').forEach(q => {
                    q.classList.remove('active');
                    q.nextElementSibling.style.maxHeight = null;
                });

                // If not active before, activate this one
                if (!isActive) {
                    this.classList.add('active');
                    const answer = this.nextElementSibling;
                    answer.style.maxHeight = answer.scrollHeight + "px";
                }
            });
        });
    });
</script>
@endpush

@endsection
