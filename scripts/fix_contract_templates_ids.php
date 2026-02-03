<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('contract_templates')->whereNull('id')->get();
if ($rows->isEmpty()) {
    echo "No contract_templates with null id found.\n";
    exit(0);
}

$max = DB::table('contract_templates')->max('id') ?: 0;
$i = $max + 1;
foreach ($rows as $r) {
    DB::table('contract_templates')->where('created_at', $r->created_at)->update(['id' => $i]);
    echo "Set id={$i} for template created_at={$r->created_at}\n";
    $i++;
}

echo "Done.\n";
