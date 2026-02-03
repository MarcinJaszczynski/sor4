<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\EmailAccount;
use App\Models\EmailMessage;
use App\Models\Event;
use App\Models\Task;
use Webklex\IMAP\Facades\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SyncEmailsCommand extends Command
{
    protected $signature = 'emails:sync {--account=} {--days=7}';
    protected $description = 'Synchronizuje wiadomości e-mail z kont IMAP';

    public function handle()
    {
        $accountId = $this->option('account');
        $days = (int) $this->option('days');
        
        $query = EmailAccount::query();
        
        if ($accountId) {
            $query->where('id', $accountId);
        }

        $accounts = $query->get();

        if ($accounts->isEmpty()) {
            $this->warn("Brak kont e-mail do synchronizacji.");
            return Command::SUCCESS;
        }

        foreach ($accounts as $account) {
            $this->info("Synchronizacja konta: {$account->account_name} ({$account->email})");
            
            try {
                $client = \Webklex\IMAP\Facades\Client::make([
                    'host'          => $account->imap_host,
                    'port'          => $account->imap_port,
                    'encryption'    => $account->imap_encryption,
                    'validate_cert' => true,
                    'username'      => $account->username ?: $account->email,
                    'password'      => $account->password,
                    'protocol'      => 'imap'
                ]);

                $client->connect();

                $folders = $client->getFolders();
                foreach($folders as $folder){
                    // Synchronizujemy tylko INBOX dla uproszczenia w pierwszej fazie
                    if (strtoupper($folder->name) !== 'INBOX') continue;

                    $this->line("Sprawdzanie folderu: " . $folder->name);
                    
                    $messages = $folder->query()->since(now()->subDays($days))->get();

                    foreach($messages as $message){
                        $messageId = $message->getMessageId()->toString();
                        
                        // Sprawdzamy czy już istnieje
                        $exists = EmailMessage::where('email_account_id', $account->id)
                            ->where('message_id', $messageId)
                            ->exists();

                        if (!$exists) {
                            $from = $message->getFrom()->first();
                            $to = $message->getTo()->first();

                            $email = EmailMessage::create([
                                'email_account_id' => $account->id,
                                'message_id'       => $messageId,
                                'subject'          => $message->getSubject()->toString(),
                                'from_address'     => $from ? $from->mail : 'unknown',
                                'from_name'        => $from ? $from->personal : null,
                                'to_address'       => $to ? $to->mail : $account->email,
                                'body_html'        => $message->getHTMLBody(),
                                'body_text'        => $message->getTextBody(),
                                'date'             => Carbon::parse($message->getDate()->toString()),
                                'is_read'          => false,
                            ]);

                            $this->line("Pobrano: " . $email->subject);
                            
                            // Automatyczne wiązanie
                            $this->autoLink($email);
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->error("Błąd dla konta {$account->email}: " . $e->getMessage());
                Log::error("Email Sync Error ({$account->email}): " . $e->getMessage());
            }
        }

        $this->info("Synchronizacja zakończona.");
        return Command::SUCCESS;
    }

    protected function autoLink(EmailMessage $email)
    {
        // Szukamy ID imprezy w temacie: [IMP-123] lub [ID: 123]
        if (preg_match('/\[IMP-(\d+)\]/i', $email->subject, $matches) || preg_match('/\[ID:\s*(\d+)\]/i', $email->subject, $matches)) {
            $eventId = $matches[1];
            if (Event::find($eventId)) {
                $email->relatedEvents()->syncWithoutDetaching([$eventId]);
                $this->info("  -> Powiązano z imprezą #{$eventId}");
            }
        }

        // Szukamy ID zadania w temacie: [ZAD-456] lub [TASK-456]
        if (preg_match('/\[ZAD-(\d+)\]/i', $email->subject, $matches) || preg_match('/\[TASK-(\d+)\]/i', $email->subject, $matches)) {
            $taskId = $matches[1];
            if (Task::find($taskId)) {
                $email->relatedTasks()->syncWithoutDetaching([$taskId]);
                $this->info("  -> Powiązano z zadaniem #{$taskId}");
            }
        }
    }
}
