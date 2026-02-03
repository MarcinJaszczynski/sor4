<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$accountId = 5;
$passwordPlain = 'slmkgkfbugzkukzq';

try {
    $encrypted = Illuminate\Support\Facades\Crypt::encryptString($passwordPlain);
    Illuminate\Support\Facades\DB::table('email_accounts')->where('id', $accountId)->update(['password' => $encrypted, 'updated_at' => now()]);
    echo "Encrypted and updated account id={$accountId}\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    exit(1);
}
