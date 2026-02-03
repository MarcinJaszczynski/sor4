<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$existing = DB::table('contract_templates')->whereNotNull('id')->first();
if ($existing) {
    echo "Existing contract_template with id={$existing->id}\n";
    exit(0);
}

$id = DB::table('contract_templates')->insertGetId([
    'name' => 'Testowy szablon 2',
    'content' => '<p>Umowa testowa 2: {{event.name}}</p>',
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "Inserted contract_template id={$id}\n";
