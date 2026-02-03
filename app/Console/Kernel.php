<?php

namespace App\Console;

use App\Jobs\RecalculateAllEventTemplatePricesJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Codzienne przeliczanie w tle o 02:15 (opcjonalne)
        $schedule->call(function () {
            $userId = 1; // domyślny użytkownik do powiadomień
            RecalculateAllEventTemplatePricesJob::dispatch($userId);
        })->dailyAt('02:15');

        // Synchronizacja skrzynek e-mail co 15 minut
        $schedule->command('emails:sync --days=7')->everyFifteenMinutes();

        // Kontrola rat (umowy) -> zadania: przeterminowane i do 14 dni
        $schedule->command('installments:sync-tasks --days=14 --author=1')->dailyAt('07:05');

        // Kontrola rat (umowy) -> dzienny digest powiadomień (opiekunowie + admin dla nieprzypisanych)
        $schedule->command('installments:notify --days=14')->dailyAt('07:10');

        // Przypomnienia do klientów o ratach (domyślnie 3 dni przed terminem + przeterminowane)
        $schedule->command('installments:remind-clients --days=3 --include-overdue=1 --channels=sms,email')->dailyAt('07:20');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
