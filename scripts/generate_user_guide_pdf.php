<?php
require __DIR__ . "/../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

$htmlFile = __DIR__ . '/../docs/USER_GUIDE.html';
$pdfFile = __DIR__ . '/../docs/USER_GUIDE.pdf';

if (!file_exists($htmlFile)) {
    echo "Brak pliku HTML: $htmlFile\n";
    exit(1);
}

$html = file_get_contents($htmlFile);

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

file_put_contents($pdfFile, $dompdf->output());

echo "Wygenerowano PDF: $pdfFile\n";
