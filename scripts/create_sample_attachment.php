<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\EmailAccount;
use App\Models\EmailMessage;
use App\Models\EmailAttachment;
use Illuminate\Support\Facades\Storage;

// Ensure at least one EmailAccount exists
$acct = EmailAccount::first();
if (! $acct) {
    $acct = EmailAccount::create([
        'account_name' => 'Test',
        'email' => 'test@example.com',
        'user_id' => 1,
        'visibility' => 'private',
        'password' => 'secret',
        'smtp_host' => 'localhost',
        'smtp_port' => 1025,
        'smtp_encryption' => 'tls',
    ]);
}

// Create a simple EmailMessage
$m = EmailMessage::create([
    'email_account_id' => $acct->id,
    'message_id' => uniqid('msg_'),
    'subject' => 'Test message',
    'from_address' => 'sender@example.com',
    'to_address' => ['test@example.com'],
    'body_text' => 'Test body',
    'date' => now(),
    'folder' => 'inbox',
]);

$path = 'email_attachments/test.txt';
$fullPath = storage_path('app/public/' . $path);
if (! file_exists(dirname($fullPath))) {
    mkdir(dirname($fullPath), 0755, true);
}
file_put_contents($fullPath, 'hello');

$att = EmailAttachment::create([
    'email_message_id' => $m->id,
    'file_path' => $path,
    'file_name' => 'test.txt',
    'mime_type' => 'text/plain',
    'size' => filesize($fullPath),
]);

echo "Created attachment id: {$att->id}\n";
echo "Public URL: " . Storage::url($att->file_path) . "\n";
