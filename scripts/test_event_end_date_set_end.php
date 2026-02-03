<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Event;

$event = Event::find(16);
if (! $event) {
    echo "Event 16 not found\n";
    exit(1);
}

$event->start_date = '2026-02-01';
$event->end_date = '2026-02-04';
$event->duration_days = null; // let server compute
$event->save();

$e = Event::find(16);
echo "start_date={$e->start_date}\n";
echo "end_date={$e->end_date}\n";
echo "duration_days={$e->duration_days}\n";
