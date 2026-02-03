<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Contractor;
use App\Models\Bus;

$c = Contractor::firstOrCreate(['name' => 'Test Contractor'], ['email' => 'test@local.example']);
$b = Bus::create([
    'name' => 'Test Bus',
    'capacity' => 50,
    'contractor_id' => $c->id,
    'is_real' => true,
]);

echo "CONTRACTOR_ID={$c->id}\n";
echo "BUS_ID={$b->id}\n";
