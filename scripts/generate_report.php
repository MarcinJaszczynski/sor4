<?php

use App\Models\EventTemplate;
use App\Models\EventTemplateQty;
use App\Filament\Resources\EventTemplateResource\Widgets\EventTemplatePriceTable;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Helper to format currency
function formatMoney($amount, $currency = 'PLN') {
    return number_format($amount, 2, '.', ',') . ' ' . $currency;
}

// Parse args
$options = getopt("", ["template:", "qty:", "start_place:"]);
$templateId = $options['template'] ?? null;
$qtyRequest = $options['qty'] ?? null;
$startPlaceId = $options['start_place'] ?? 1;

if (!$templateId || !$qtyRequest) {
    echo "Usage: php scripts/generate_report.php --template=ID --qty=N [--start_place=ID]\n";
    echo "Example: php scripts/generate_report.php --template=60 --qty=40\n";
    exit(1);
}

// Load template
/** @var EventTemplate $template */
$template = EventTemplate::with(['bus', 'programPoints', 'programPoints.children', 'taxes', 'markup', 'programPoints.children.currency'])->find($templateId);

if (!$template) {
    echo "Error: Template ID $templateId not found.\n";
    exit(1);
}

// Instantiate the widget which holds calculation logic
// Note: This relies on the Widget staying independent of HTTP request context for the most part
$widget = new EventTemplatePriceTable();
$widget->record = $template;
$widget->startPlaceId = $startPlaceId;

// Run detailed calculations
$detailed = $widget->getDetailedCalculations();

$data = $detailed[$qtyRequest] ?? null;

if (!$data) {
    echo "Error: No calculation data for qty $qtyRequest.\n";
    echo "Available quantities in template: " . implode(", ", array_keys($detailed)) . "\n";
    exit(1);
}

// Get Variant info for header
$variant = EventTemplateQty::where('qty', $qtyRequest)->first();
$gratis = $variant->gratis ?? 0;
$staff = $variant->staff ?? 0;
$driver = $variant->driver ?? 0;
$totalPeople = $qtyRequest + $gratis + $staff + $driver;

echo "Wariant: $qtyRequest uczestników (plus $gratis gratis, $staff obsługa, $driver kierowców), razem: $totalPeople osób\n";

// We primarily output PLN section as per request
$plnData = $data['PLN'] ?? [];
if (empty($plnData)) {
    echo "Warning: No PLN data found.\n";
} else {
    echo "Waluta: PLN (PLN)\n";
    
    // Header
    // "Punkt programu", "Cena jednostkowa (za grupę)", "dla grupy", "Koszt całkowity (dla wszystkich)"
    $mask = "%-40s %-20s %-15s %-20s\n";
    printf($mask, "Punkt programu", "Cena jednostkowa", "dla grupy", "Koszt całkowity");
    echo str_repeat("-", 100) . "\n";

    $points = $plnData['points'] ?? [];
    foreach ($points as $p) {
        $name = $p['name'];
        if (mb_strlen($name) > 38) {
            $name = mb_substr($name, 0, 35) . '...';
        }
        
        $unitPriceStr = isset($p['unit_price']) ? formatMoney($p['unit_price']) : '';
        $groupSizeStr = isset($p['group_size']) ? $p['group_size'] . " osób" : 'osób';
        $costStr = formatMoney($p['cost']);
        
        printf($mask, $name, $unitPriceStr, $groupSizeStr, $costStr);
    }
    
    echo str_repeat("-", 100) . "\n";
    
    // Totals
    $totalNoMarkup = $plnData['total_before_markup'] ?? 0;
    echo "SUMA dla PLN (bez narzutu):\t\t" . formatMoney($totalNoMarkup) . "\n";

    $markupData = $data['markup'] ?? [];
    $markupAmount = $markupData['amount'] ?? 0;
    $percent = $markupData['percent_applied'] ?? 0;

    echo "Narzut (" . number_format($percent, 2) . "%):\t\t\t" . formatMoney($markupAmount) . "\n";

    // Taxes
    $taxesData = $data['taxes'] ?? [];
    $taxBreakdown = $taxesData['breakdown'] ?? [];
    $totalTax = $taxesData['total_amount'] ?? 0;

    foreach ($taxBreakdown as $tax) {
        echo "{$tax['name']} (" . number_format($tax['percentage'], 2) . "%):\t\t" . formatMoney($tax['amount']) . "\n";
    }

    echo "Suma podatków:\t\t\t\t" . formatMoney($totalTax) . "\n";

    $finalTotal = $plnData['total'] ?? 0;
    echo "SUMA KOŃCOWA dla PLN:\t\t\t" . formatMoney($finalTotal) . "\n";

    $pricePerPerson = $plnData['price_per_person_rounded'] ?? 0;
    $pricePerPersonRaw = $plnData['price_per_person_raw'] ?? 0;
    
    echo "Cena za osobę (uczestnik):\t\t" . formatMoney($pricePerPerson) . "\n";  // This is rounded in code
    echo "Cena za osobę (dokładna):\t\t" . formatMoney($pricePerPersonRaw) . "\n";
}
