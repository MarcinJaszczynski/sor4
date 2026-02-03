<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Offer;
use App\Models\Contract;

$eventId = 19;

$offers = Offer::where('event_id', $eventId)->get(['id','name'])->toArray();
$contracts = Contract::where('event_id', $eventId)->get(['id','contract_number','uuid'])->toArray();

echo json_encode(['offers' => $offers, 'contracts' => $contracts], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
