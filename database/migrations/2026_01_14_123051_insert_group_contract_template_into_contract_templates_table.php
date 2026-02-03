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
<h2>Umowa grupowa na usługę turystyczną</h2>
<p>Organizator zobowiązuje się zrealizować usługę turystyczną dla grupy o charakterze zbiorowym, zgodnie z programem i kalkulacją zamieszczoną poniżej.</p>
<p>Wydarzenie: <strong>[impreza_nazwa]</strong> w terminie <strong>[termin]</strong>, przewidywana liczba uczestników: <strong>[liczba_osob]</strong>.</p>
<h3>Dane zamawiającego</h3>
<ul>
    <li>Nazwa / Grupa: <strong>[klient_nazwa]</strong></li>
    <li>Kontakt e-mail: [klient_email]</li>
    <li>Telefon kontaktowy: [klient_telefon]</li>
</ul>
<h3>Program i zakres</h3>
<p>[program]</p>
<h3>Kalkulacje finansowe</h3>
<p>Całkowita cena dla grupy: <strong>[cena_calkowita]</strong></p>
<p>Cena jednostkowa (na osobę): <strong>[cena_osoba]</strong></p>
<div>[kalkulacja]</div>
<h3>Warunki rezerwacji</h3>
<p>Zamawiający wpłaca zadatek lub część płatności zgodnie z ustalonym harmonogramem. W przypadku rezygnacji obowiązują opłaty zgodnie z załącznikiem i zapisami umowy.</p>
<h3>Postanowienia końcowe</h3>
<p>W sprawach nieuregulowanych stosuje się przepisy ustawy o usługach turystycznych oraz Kodeksu cywilnego. Umowa sporządzona w dwóch jednobrzmiących egzemplarzach.</p>
HTML;

        DB::table('contract_templates')->insert([
            'name' => 'Umowa grupowa organizatora',
            'content' => $content,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('contract_templates')->where('name', 'Umowa grupowa organizatora')->delete();
    }
};
