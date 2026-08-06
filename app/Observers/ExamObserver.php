<?php

namespace App\Observers;

use App\Models\Exam;
use App\Services\BillingService;
use Illuminate\Support\Facades\Log;

/**
 * ExamObserver
 *
 * When an exam transitions to a "published" state (is_published = true, or
 * status field if added later), automatically generate exam fee invoices for
 * all enrolled students in that class/session.
 *
 * This fires on:
 *  - Exam::create()  if is_published starts as true
 *  - Exam::update()  when is_published flips from false → true
 */
class ExamObserver
{
    public function __construct(private readonly BillingService $billing) {}

    public function created(Exam $exam): void
    {
        if ($this->shouldGenerate($exam)) {
            $this->generateFees($exam);
        }
    }

    public function updated(Exam $exam): void
    {
        // Only trigger when is_published just flipped to true
        if ($exam->wasChanged('is_published') && $exam->is_published) {
            $this->generateFees($exam);
        }
    }

    private function shouldGenerate(Exam $exam): bool
    {
        // Check both possible published flags
        return (bool) ($exam->is_published ?? false);
    }

    private function generateFees(Exam $exam): void
    {
        if (! $exam->class_id || ! $exam->session_id) {
            return;
        }

        try {
            $result = $this->billing->generateOnExamPublish(
                examId:    $exam->id,
                classId:   $exam->class_id,
                sessionId: $exam->session_id,
            );

            Log::info(
                "ExamObserver: exam #{$exam->id} published. " .
                "Exam fee invoices — generated:{$result['generated']}, skipped:{$result['skipped']}"
            );
        } catch (\Throwable $e) {
            Log::error("ExamObserver: exam fee generation failed for exam #{$exam->id}: " . $e->getMessage());
        }
    }
}
