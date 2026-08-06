<?php

namespace App\Console\Commands;

use App\Services\BillingService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Generate monthly fee invoices for all active students.
 *
 * Scheduled: 1st of every month at 06:00.
 * Can also be run manually: php artisan fees:generate-monthly [--month=8] [--year=2026]
 */
class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'fees:generate-monthly
                            {--month= : Month to generate (1-12). Defaults to current month.}
                            {--year=  : Year to generate. Defaults to current year.}
                            {--session= : School session ID. Defaults to current active session.}
                            {--dry-run : Preview what would be generated without creating anything.}';

    protected $description = 'Auto-generate monthly fee invoices for all active students from fee structures.';

    public function handle(BillingService $billing): int
    {
        $month     = (int) ($this->option('month') ?: now()->month);
        $year      = (int) ($this->option('year')  ?: now()->year);
        $sessionId = $this->option('session') ? (int) $this->option('session') : null;
        $dryRun    = $this->option('dry-run');

        $label = Carbon::create($year, $month)->format('F Y');

        $this->info("── Monthly Invoice Generation ──────────────────────────");
        $this->info("  Period    : {$label}");
        $this->info("  Session   : " . ($sessionId ? "#{$sessionId}" : 'current active session'));
        $this->info("  Dry run   : " . ($dryRun ? 'YES — no data will be written' : 'NO'));
        $this->line('');

        if ($dryRun) {
            $this->warn('Dry-run mode: showing preview only.');
            return self::SUCCESS;
        }

        $this->info('Generating invoices...');
        $bar = $this->output->createProgressBar();
        $bar->start();

        $result = $billing->generateMonthlyForAll($month, $year, $sessionId);

        $bar->finish();
        $this->line('');
        $this->line('');

        $this->table(['Metric', 'Count'], [
            ['Invoices generated', $result['generated']],
            ['Students skipped',   $result['skipped']],
            ['Errors',             $result['errors']],
        ]);

        if ($result['errors'] > 0) {
            $this->warn("Check storage/logs/monthly-invoices.log for error details.");
        }

        $this->info("Done. Monthly invoices generated for {$label}.");
        return $result['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
