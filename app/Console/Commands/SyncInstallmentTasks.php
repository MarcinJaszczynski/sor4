<?php

namespace App\Console\Commands;

use App\Services\Finance\InstallmentTaskSyncService;
use Illuminate\Console\Command;

class SyncInstallmentTasks extends Command
{
    protected $signature = 'installments:sync-tasks {--days=14 : Ile dni do przodu tworzyć zadania} {--author=1 : ID użytkownika jako autor zadań}';

    protected $description = 'Synchronizuje harmonogram rat (umowy) z zadaniami: przeterminowane i nadchodzące.';

    public function handle(InstallmentTaskSyncService $service): int
    {
        $days = (int) $this->option('days');
        $authorId = (int) $this->option('author');

        $result = $service->sync([
            'days_ahead' => $days,
            'author_id' => $authorId,
        ]);

        $this->info('OK');
        $this->line('Utworzone: ' . ($result['created'] ?? 0));
        $this->line('Zaktualizowane: ' . ($result['updated'] ?? 0));
        $this->line('Zamknięte: ' . ($result['closed'] ?? 0));
        $this->line('Pominięte: ' . ($result['skipped'] ?? 0));

        return self::SUCCESS;
    }
}
