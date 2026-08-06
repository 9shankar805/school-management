<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\CheckAttendanceShortage::class,
        \App\Console\Commands\SendFeeReminders::class,
        \App\Console\Commands\GenerateMonthlyInvoices::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Run absence notifications + shortage check every school day at 18:00
        $schedule->command('attendance:check-shortage')
                 ->weekdays()
                 ->dailyAt('18:00')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/attendance-shortage.log'));

        // Send fee due-date reminders 3 days before (and overdue alerts) daily at 8am
        $schedule->command('fees:reminders --days=3 --overdue')
                 ->dailyAt('08:00')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/fee-reminders.log'));

        // Auto-generate monthly invoices on the 1st of every month at 06:00
        $schedule->command('fees:generate-monthly')
                 ->monthlyOn(1, '06:00')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/monthly-invoices.log'));

        // ── Module 23: Backup ─────────────────────────────────────────────
        // Full backup every day at 02:00 (off-peak)
        $schedule->command('backup:run --only-db')
                 ->dailyAt('02:00')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onFailure(function () {
                     \Illuminate\Support\Facades\Log::error('Daily DB backup failed.');
                 })
                 ->appendOutputTo(storage_path('logs/backup.log'));

        // Full backup including files every Sunday at 03:00
        $schedule->command('backup:run')
                 ->weeklyOn(0, '03:00')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->onFailure(function () {
                     \Illuminate\Support\Facades\Log::error('Weekly full backup failed.');
                 })
                 ->appendOutputTo(storage_path('logs/backup.log'));

        // Clean up old backups daily at 04:00
        $schedule->command('backup:clean')
                 ->dailyAt('04:00')
                 ->withoutOverlapping()
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/backup.log'));

        // Monitor backup health daily at 09:00
        $schedule->command('backup:monitor')
                 ->dailyAt('09:00')
                 ->appendOutputTo(storage_path('logs/backup.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
