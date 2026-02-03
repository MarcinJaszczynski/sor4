<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ContractTemplate;

$existing = ContractTemplate::first();
if ($existing) {
    echo "ContractTemplate already exists (id={$existing->id}).\n";
    exit(0);
}

$t = ContractTemplate::create([
    'name' => 'Testowy szablon',
    'content' => '<p>Umowa testowa dla imprezy: {{event.name}}</p>',
]);

echo "Created ContractTemplate id={$t->id}\n";
