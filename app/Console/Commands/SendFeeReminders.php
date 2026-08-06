<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Notifications\FeeReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendFeeReminders extends Command
{
    /**
     * Send fee due / overdue reminders to students and their parents.
     *
     * Run daily via Kernel schedule:
     *   $schedule->command('fees:reminders')->dailyAt('08:00');
     */
    protected $signature   = 'fees:reminders
                                {--days=3 : Send reminder N days before due date}
                                {--overdue : Also send reminders for already-overdue invoices}';

    protected $description = 'Send fee due-date reminders (and optionally overdue alerts) to students and parents.';

    public function handle(): int
    {
        $daysBefore = (int) $this->option('days');
        $includeOverdue = $this->option('overdue');

        $targetDate = Carbon::today()->addDays($daysBefore)->toDateString();

        // Upcoming invoices due in N days
        $upcoming = Invoice::with(['student.parents'])
            ->where('status', 'unpaid')
            ->whereDate('due_date', $targetDate)
            ->get();

        $this->info("Upcoming invoices due on {$targetDate}: {$upcoming->count()}");

        foreach ($upcoming as $invoice) {
            $this->sendReminder($invoice, false);
        }

        if ($includeOverdue) {
            $overdue = Invoice::with(['student.parents'])
                ->where('status', 'unpaid')
                ->where('due_date', '<', today())
                ->get();

            $this->info("Overdue invoices: {$overdue->count()}");

            foreach ($overdue as $invoice) {
                $this->sendReminder($invoice, true);
            }
        }

        $this->info('Fee reminders sent successfully.');
        return self::SUCCESS;
    }

    private function sendReminder(Invoice $invoice, bool $isOverdue): void
    {
        $notification = new FeeReminderNotification(
            invoiceNumber: (string) $invoice->id,
            amount:        number_format($invoice->amount, 2),
            dueDate:       $invoice->due_date?->toDateString() ?? 'N/A',
        );

        // Notify student
        if ($invoice->student) {
            $invoice->student->notify($notification);
        }

        // Notify parents
        foreach ($invoice->student?->parents ?? [] as $parent) {
            $parent->notify($notification);
        }

        $this->line(($isOverdue ? '[OVERDUE] ' : '') . "Sent reminder for invoice #{$invoice->id}");
    }
}
