<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EventTemplatePricePerPerson;
use App\Models\Markup;

// Report rows where stored markup_amount is equal (within tolerance) to min_daily * duration OR where stored markup < min_total
// Output: CSV to stdout: id,event_template_id,event_template_name,event_template_qty_id,currency_id,price_base,markup_amount,duration,min_total,match_type,event_count,sample_event_ids

$tolerance = 0.05; // grosze tolerance
$rows = EventTemplatePricePerPerson::with(['eventTemplate'])->cursor();

// print header
fwrite(STDOUT, "id,event_template_id,event_template_name,event_template_qty_id,currency_id,price_base,markup_amount,duration,min_total,reason,event_count,sample_event_ids\n");
$found = 0;
foreach ($rows as $row) {
    $template = $row->eventTemplate;
    if (!$template) continue;

    // resolve min_daily
    $minDaily = 0;
    if (isset($template->markup) && $template->markup?->min_daily_amount_pln !== null) {
        $minDaily = (float) $template->markup->min_daily_amount_pln;
    } elseif (!empty($template->markup_id)) {
        $m = Markup::find($template->markup_id);
        if ($m) $minDaily = (float) $m->min_daily_amount_pln;
    } else {
        $default = Markup::where('is_default', true)->first();
        $minDaily = (float) ($default?->min_daily_amount_pln ?? 0);
    }

    $duration = $template->duration_days ?? 1;
    $minTotal = round($minDaily * $duration, 2);

    $markupAmount = (float) $row->markup_amount;

    if ($minTotal <= 0) continue;

    // exact match within tolerance
    if (abs($markupAmount - $minTotal) <= $tolerance) {
        $reason = 'equal_to_min_total';
    } elseif ($markupAmount < $minTotal - $tolerance) {
        $reason = 'below_min_total';
    } else {
        continue;
    }

    $found++;
    // fetch related events summary
    $eventCount = \App\Models\Event::where('event_template_id', $row->event_template_id)->count();
    $sampleEvents = \App\Models\Event::where('event_template_id', $row->event_template_id)->limit(5)->pluck('id')->toArray();
    $sampleEventsStr = implode('|', $sampleEvents);

    $out = [
        $row->id,
        $row->event_template_id,
        '"' . str_replace('"','""', $template->name ?? '') . '"',
        $row->event_template_qty_id,
        $row->currency_id,
        number_format((float)$row->price_base,2,'.',''),
        number_format($markupAmount,2,'.',''),
        $duration,
        number_format($minTotal,2,'.',''),
        $reason,
        $eventCount,
        '"' . $sampleEventsStr . '"',
    ];

    fwrite(STDOUT, implode(',', $out) . "\n");
}

fwrite(STDOUT, "# found={$found}\n");
