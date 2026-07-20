<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Notifications\FeeReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendFeeReminders extends Command
{
    protected $signature   = 'fees:send-reminders';
    protected $description = 'Send fee reminder notifications to parents for invoices due in 3 days';

    public function handle(): int
    {
        $targetDate = Carbon::today()->addDays(3)->toDateString();

        $invoices = Invoice::where('status', 'unpaid')
            ->whereNotNull('due_date')
            ->whereDate('due_date', $targetDate)
            ->with('student.parents')
            ->get();

        $count = 0;
        foreach ($invoices as $invoice) {
            $student = $invoice->student;
            if (! $student) { continue; }

            foreach ($student->parents as $parent) {
                $parent->notify(new FeeReminderNotification(
                    invoiceNumber: (string) $invoice->id,
                    amount:        number_format($invoice->amount, 2),
                    dueDate:       $invoice->due_date,
                ));
                $count++;
            }
        }

        $this->info("Sent {$count} fee reminder notification(s) for due date: {$targetDate}");
        return self::SUCCESS;
    }
}
