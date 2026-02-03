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
<h2>Umowa indywidualna na usługę turystyczną</h2>
<p>Strony niniejszej umowy ustalają, że organizator zrealizuje usługę wynikającą z imprezy <strong>[impreza_nazwa]</strong> zaplanowanej w terminie <strong>[termin]</strong>.</p>
<p>Zamawiającym jest <strong>[klient_nazwa]</strong> (e-mail: [klient_email], telefon: [klient_telefon]). Umowa obejmuje usługę dla <strong>[liczba_osob]</strong> uczestników.</p>
<h3>Zakres usług</h3>
<ol>
    <li>Opis programu zgodny z harmonogramem: [program]</li>
    <li>Rozliczenie finansowe zawiera się w tabeli: [kalkulacja]</li>
    <li>Cena całkowita: <strong>[cena_calkowita]</strong>, cena za osobę: <strong>[cena_osoba]</strong>.</li>
</ol>
<h3>Postanowienia dodatkowe</h3>
<p>Wszelkie zmiany w liczbie uczestników wymagają pisemnego potwierdzenia. W przypadku rezygnacji stosuje się zasady określone w osobnym załączniku do niniejszej umowy.</p>
<p>W sprawach nieuregulowanych niniejszą umową zastosowanie mają przepisy Kodeksu cywilnego oraz ustawy o usługach turystycznych.</p>
<p>Umowę sporządzono w dwóch jednakowo brzmiących egzemplarzach – po jednym dla każdej ze stron.</p>
HTML;

        DB::table('contract_templates')->insert([
            'name' => 'Umowa indywidualna klienta',
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
        DB::table('contract_templates')->where('name', 'Umowa indywidualna klienta')->delete();
    }
};
