<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\User;
use App\Notifications\FeeReminderNotification;
use App\Services\SmsService;
use App\Services\WhatsAppService;
use App\Services\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Send fee reminder via in-app notification, SMS, and push to student + parents.
 *
 * Dispatched from:
 *  - PaymentController::store() (on new invoice)
 *  - SendFeeReminders console command (scheduled daily)
 *  - Parent portal pay page
 */
class SendFeeReminderNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly int    $invoiceId,
        public readonly bool   $isOverdue = false,
    ) {}

    public function handle(SmsService $sms, PushNotificationService $push, WhatsAppService $whatsapp): void
    {
        $invoice = Invoice::with(['student.parents'])->find($this->invoiceId);
        if (! $invoice) {
            Log::warning("SendFeeReminderNotification: invoice #{$this->invoiceId} not found.");
            return;
        }

        $student    = $invoice->student;
        $amount     = number_format($invoice->amount, 2);
        $dueDate    = $invoice->due_date?->toDateString() ?? 'N/A';
        $invoiceNum = (string) $invoice->id;

        $subject    = $this->isOverdue
            ? "OVERDUE: Fee payment required"
            : "Fee payment reminder";

        $smsText    = $this->isOverdue
            ? "OVERDUE: Invoice #{$invoiceNum} for \${$amount} was due on {$dueDate}. Please pay immediately."
            : "Reminder: Invoice #{$invoiceNum} for \${$amount} is due on {$dueDate}.";

        $pushTitle  = $this->isOverdue ? 'Fee Overdue Alert' : 'Fee Reminder';

        // Collect all recipients: student + parents
        $recipients = collect();
        if ($student) {
            $recipients->push($student);
            foreach ($student->parents as $parent) {
                $recipients->push($parent);
            }
        }

        foreach ($recipients->unique('id') as $recipient) {
            // In-app + email notification
            $recipient->notify(new FeeReminderNotification(
                invoiceNumber: $invoiceNum,
                amount:        $amount,
                dueDate:       $dueDate,
            ));

            // SMS
            if ($recipient->phone) {
                $sms->send($recipient->phone, $smsText);
                if ($student?->school) {
                    $whatsapp->setSchool($student->school)->setModule('fees')->sendText($recipient->phone, $smsText);
                }
            }
        }

        // Push broadcast to relevant role segments
        $push->broadcast('student', $pushTitle, $smsText, [
            'invoice_id' => $invoice->id,
            'type'       => 'fee_reminder',
        ]);
        $push->broadcast('parent', $pushTitle, $smsText, [
            'invoice_id' => $invoice->id,
            'type'       => 'fee_reminder',
        ]);

        Log::info("SendFeeReminderNotification: sent for invoice #{$this->invoiceId}");
    }
}
