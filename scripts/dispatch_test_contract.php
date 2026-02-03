<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Jobs\GenerateContractJob;
use App\Models\Event;
use App\Models\ContractTemplate;
use Illuminate\Support\Facades\DB;

echo "Starting test dispatch...\n";

$event = Event::find(23) ?: Event::first();
if (! $event) {
    echo "No events found in DB. Abort.\n";
    exit(1);
}

$template = ContractTemplate::first();
if (! $template) {
    echo "No contract templates found in DB. Abort.\n";
    exit(1);
}

$contractNumber = 'TEST/' . date('Y') . '/' . rand(1000,9999);

echo "Dispatching GenerateContractJob sync for event {$event->id}, template {$template->id}\n";

echo "Event data: " . json_encode($event->toArray()) . "\n";
echo "Template data: " . json_encode($template->toArray()) . "\n";

echo "Raw DB contract_templates rows:\n";
$rows = DB::table('contract_templates')->get();
foreach ($rows as $r) {
    echo json_encode((array)$r) . "\n";
}

// Run job logic synchronously for test (construct and call handle)
$generator = $app->make(\App\Services\ContractGeneratorService::class);
$job = new GenerateContractJob((int)$event->id, (int)$template->id, $contractNumber, now(), 'TestCity');
$job->handle($generator);

echo "Job dispatched and processed. Checking notifications...\n";

$notes = DB::table('notifications')->orderBy('created_at', 'desc')->limit(5)->get();
foreach ($notes as $n) {
    echo "Notification: id={$n->id} type={$n->type} data=" . json_encode($n->data) . " created_at={$n->created_at}\n";
}

echo "Done.\n";

$contract = \App\Models\Contract::where('contract_number', $contractNumber)->first();
if ($contract) {
    echo "Contract created: id={$contract->id} event_id={$contract->event_id} number={$contract->contract_number}\n";
} else {
    echo "No contract record found for number {$contractNumber}\n";
}
