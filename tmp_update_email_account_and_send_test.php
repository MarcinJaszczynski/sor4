<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Use Gmail account (id = 5) with provided App Password
    $accountId = 5;
    $appPassword = 'slmkgkfbugzkukzq';

    $account = App\Models\EmailAccount::find($accountId);
    if (! $account) {
        echo "EmailAccount id={$accountId} not found\n";
        exit(1);
    }
    // Ensure Gmail SMTP/IMAP settings and App Password are present
    $account->update([
        'account_name' => 'gmail',
        'from_name' => 'BP RAFA',
        'visibility' => 'private',
        'imap_host' => 'imap.gmail.com',
        'imap_port' => 993,
        'imap_encryption' => 'ssl',
        'username' => $account->email,
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_encryption' => 'tls',
        'password' => $appPassword,
    ]);

    echo "Updated EmailAccount id={$accountId} (email={$account->email})\n";

    $to = 'm.jaszczynski@gmail.com';
    $subject = 'Test wysyłki - prosty tekst';
    $body = 'Testowy e-mail (plain text) wysłany z aplikacji.';

    // Skonfiguruj dynamic mailer tak jak w EmailService i wyślij prosty tekst
    $config = [
        'transport' => 'smtp',
        'host' => $account->smtp_host,
        'port' => $account->smtp_port,
        'encryption' => $account->smtp_encryption,
        'username' => $account->username ?: $account->email,
        'password' => $account->password,
        'timeout' => null,
        'local_domain' => env('MAIL_EHLO_DOMAIN'),
    ];

    Illuminate\Support\Facades\Config::set('mail.mailers.dynamic', $config);

    try {
        Illuminate\Support\Facades\Mail::mailer('dynamic')->raw($body, function ($message) use ($account, $to, $subject) {
            $message->from($account->email, $account->account_name)
                ->to($to)
                ->subject($subject);
        });

        $email = App\Models\EmailMessage::create([
            'email_account_id' => $account->id,
            'subject'          => $subject,
            'from_address'     => $account->email,
            'from_name'        => $account->account_name,
            'to_address'       => $to,
            'body_html'        => $body,
            'date'             => now(),
            'is_read'          => true,
            'is_sent'          => true,
        ]);

        echo "Email record created id=" . $email->id . "\n";
    } catch (Throwable $e) {
        echo "Send Exception: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString();
        exit(1);
    }
} catch (Throwable $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    exit(1);
}
