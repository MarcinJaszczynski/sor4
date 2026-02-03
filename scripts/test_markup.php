<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\EventTemplateCalculationEngine;
use App\Models\EventTemplate;

$engine = new EventTemplateCalculationEngine();
$template = EventTemplate::find(1);
if (!$template) {
    echo "EventTemplate id=1 not found\n";
    exit(1);
}

$baseValues = [100, 200, 500, 1000];
foreach ($baseValues as $base) {
    // call private method via reflection
    $r = new ReflectionClass($engine);
    $m = $r->getMethod('calculateMarkupForTemplate');
    $m->setAccessible(true);
    $result = $m->invoke($engine, $template, $base);
    echo "Base={$base} => markup_amount={$result}\n";
}

// Also run full calculateDetailed and dump markup for first qty
$details = $engine->calculateDetailed($template);
$first = reset($details);
if ($first) {
    echo "Sample qty={$first['qty']} markup_amount={$first['markup_amount']}\n";
}
