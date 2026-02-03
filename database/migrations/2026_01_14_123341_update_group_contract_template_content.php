<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $content = <<<'HTML'
<h1>UMOWA GRUPOWA</h1>
<p><em>o organizację szkolnej imprezy turystycznej</em></p>
<p>§1. Strony</p>
<p><strong>Organizator Turystyki:</strong><br>
Biuro Podróży RAFA<br>
………………………………………</p>
<p><strong>Zamawiający (Szkoła / Placówka):</strong><br>
Nazwa: [klient_nazwa]<br>
Adres: [klient_email] / [klient_telefon] (dodatkowo w opisie klienta)<br>
Reprezentowana przez: ………………………………………</p>
<p>§2. Przedmiot umowy</p>
<p><strong>Organizator zobowiązuje się do organizacji imprezy turystycznej dla grupy szkolnej:</strong></p>
<p>liczba uczestników: [liczba_osob] osób (w tym uczniowie + opiekunowie),<br>
termin: [termin],<br>
charakter: ☐ krajowy ☐ zagraniczny.</p>
<p>Program imprezy stanowi załącznik nr 1 (szczegółowy harmonogram):</p>
<div>[program]</div>
<p>§3. Zakres świadczeń</p>
<ul>
    <li>transport,</li>
    <li>zakwaterowanie,</li>
    <li>wyżywienie,</li>
    <li>ubezpieczenie uczestników,</li>
    <li>realizację programu,</li>
    <li>pilota/przewodnika.</li>
</ul>
<p>Opiekę wychowawczą sprawują opiekunowie wyznaczeni przez Zamawiającego.</p>
<p>§4. Wynagrodzenie</p>
<p>Cena za jednego uczestnika: [cena_osoba] zł brutto.<br>
Całkowita wartość umowy: [cena_calkowita] zł brutto.</p>
<p>Warunki płatności:<br>
zaliczka ………%,<br>
pozostała kwota do dnia …………………</p>
<div>[kalkulacja]</div>
<p>§5. Odpowiedzialność</p>
<p>Organizator odpowiada za realizację świadczeń turystycznych.</p>
<p>Zamawiający odpowiada za:</p>
<ul>
    <li>dokumentację szkolną,</li>
    <li>listę uczestników,</li>
    <li>zgodność liczby opiekunów z przepisami MEN.</li>
</ul>
<p>§6. Reklamacje</p>
<p>Reklamacje należy zgłaszać pisemnie w terminie 30 dni od zakończenia imprezy. Organizat
or rozpatruje reklamację w terminie 30 dni.</p>
<p>3️⃣ <strong>KLAUZULA RODO (ZAŁĄCZNIK)</strong><br>
Klauzula informacyjna RODO</p>
<p>Administratorem danych osobowych jest Biuro Podróży RAFA.</p>
<p>Dane osobowe przetwarzane są w celu:<br>
realizacji umowy o udział w imprezie turystycznej,<br>
ubezpieczenia uczestników,<br>
realizacji obowiązków prawnych Organizatora.</p>
<p>Podstawą prawną przetwarzania danych jest:<br>
art. 6 ust. 1 lit. b i c RODO,<br>
art. 9 ust. 2 lit. h RODO (dane zdrowotne – karta kwalifikacyjna).</p>
<p>Dane mogą być przekazywane:<br>
ubezpieczycielom,<br>
przewoźnikom,<br>
kontrahentom zagranicznym (w przypadku wyjazdów zagranicznych).</p>
<p>Dane będą przechowywane przez okres wymagany przepisami prawa.</p>
<p>Osobie, której dane dotyczą, przysługuje prawo dostępu, sprostowania, usunięcia danych oraz wniesienia skargi do PUODO.</p>
<p>Zgody</p>
<p>☐ Wyrażam zgodę na przetwarzanie danych osobowych dziecka w celu realizacji imprezy turystycznej.<br>
☐ Wyrażam zgodę na nieodpłatne wykorzystanie wizerunku dziecka w materiałach promocyjnych BP RAFA. (zgoda dobrowolna)</p>
<p>Podpis opiekuna prawnego: ……………………………</p>
HTML;

        DB::table('contract_templates')
            ->where('name', 'Umowa grupowa organizatora')
            ->update(['content' => $content]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nie ma łatwego rollbacku treści, można zostawić pustą lub przywrócić poprzedni zapis ręcznie w razie potrzeby.
    }
};
