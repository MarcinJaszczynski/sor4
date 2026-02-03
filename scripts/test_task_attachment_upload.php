<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Storage;
use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Support\Str;

// 1. Ensure public disk is linked (we assume storage/app/public is served)

// 2. Create or get a Task
$task = Task::first();
if (! $task) {
    $task = Task::create([
        'title' => 'Test upload task ' . Str::random(6),
        'description' => 'Generated for testing attachments',
        'author_id' => 1,
    ]);
    echo "Created Task id={$task->id}\n";
} else {
    echo "Using Task id={$task->id}\n";
}

// 3. Create a tiny 1x1 PNG (base64) and store it
$pngBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAAWgmWQ0AAAAASUVORK5CYII='; // 1x1 PNG
$bin = base64_decode($pngBase64);
$filename = 'test-upload-' . time() . '.png';
$path = 'task-attachments/' . $filename;
Storage::disk('public')->put($path, $bin);

// 4. Create TaskAttachment record
$attachment = TaskAttachment::create([
    'task_id' => $task->id,
    'user_id' => 1,
    'name' => $filename,
    'file_path' => $path,
    'mime_type' => 'image/png',
    'size' => strlen($bin),
]);

echo "Stored file at: " . Storage::disk('public')->url($path) . "\n";
echo "Created attachment id={$attachment->id}\n";

return 0;
