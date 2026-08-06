<?php

namespace App\Services;

use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Promotion;
use App\Models\SchoolSession;
use App\Models\StudentFeeDiscount;
use App\Models\StudentLedger;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * BillingService — the core fee billing engine.
 *
 * Responsibilities:
 *  1. generateForStudent()    — generate all applicable fee invoices for one student
 *  2. generateMonthlyForAll() — 1st-of-month bulk run for all active students
 *  3. generateOnAdmission()   — admission + one-time fees when student enrolls
 *  4. generateOnExamPublish() — exam fee invoices when an exam is published
 *
 * Each method:
 *  - Reads applicable FeeStructures for the student's class/session
 *  - Applies student discounts
 *  - Creates Invoice + InvoiceItems
 *  - Posts a debit entry to the student's ledger
 *  - Is idempotent: skips if an invoice for the same student/month/fee_type already exists
 */
class BillingService
{
    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Generate monthly invoices for ALL active students in a session.
     * Called by GenerateMonthlyInvoices artisan command on the 1st of each month.
     *
     * @return array{generated: int, skipped: int, errors: int}
     */
    public function generateMonthlyForAll(int $month, int $year, ?int $sessionId = null): array
    {
        $sessionId = $sessionId ?? $this->currentSessionId();
        $counts = ['generated' => 0, 'skipped' => 0, 'errors' => 0];

        // Get all active students with their class (via promotions table)
        $students = Promotion::with('student')
            ->where('session_id', $sessionId)
            ->get();

        foreach ($students as $promotion) {
            $student = $promotion->student;
            if (! $student || $student->student_status !== 'active') {
                $counts['skipped']++;
                continue;
            }

            try {
                $result = $this->generateMonthlyForStudent(
                    $student, $promotion->class_id, $sessionId, $month, $year
                );
                $counts['generated'] += $result;
            } catch (\Throwable $e) {
                $counts['errors']++;
                Log::error("BillingService: monthly invoice error for student #{$student->id}: " . $e->getMessage());
            }
        }

        return $counts;
    }

    /**
     * Generate monthly fee invoices for a single student.
     * Returns number of invoices created.
     */
    public function generateMonthlyForStudent(
        User|int $student,
        int      $classId,
        int      $sessionId,
        int      $month,
        int      $year
    ): int {
        $student = $student instanceof User ? $student : User::findOrFail($student);

        // Get applicable fee structures for this class/session with monthly/quarterly/etc. trigger
        $structures = FeeStructure::with(['feeType', 'category'])
            ->where('is_active', true)
            ->where(function ($q) use ($sessionId) {
                $q->where('session_id', $sessionId)->orWhereNull('session_id');
            })
            ->where(function ($q) use ($classId) {
                $q->where('class_id', $classId)->orWhereNull('class_id');
            })
            ->whereHas('feeType', function ($q) {
                $q->whereIn('trigger', ['monthly', 'manual']);
            })
            ->whereIn('frequency', $this->frequenciesForMonth($month))
            ->get();

        if ($structures->isEmpty()) {
            return 0;
        }

        // Filter out already-generated invoices for this month
        $existing = Invoice::where('student_id', $student->id)
            ->where('session_id', $sessionId)
            ->where('billing_month', $month)
            ->where('billing_year', $year)
            ->where('is_auto_generated', true)
            ->pluck('fee_type_id')
            ->toArray();

        $structures = $structures->filter(function ($s) use ($existing) {
            return ! in_array($s->fee_type_id, $existing);
        });

        if ($structures->isEmpty()) {
            return 0;
        }

        return DB::transaction(function () use ($student, $sessionId, $classId, $structures, $month, $year) {
            return $this->createInvoiceFromStructures(
                student:    $student,
                structures: $structures,
                sessionId:  $sessionId,
                month:      $month,
                year:       $year,
                trigger:    'monthly',
                title:      Carbon::create($year, $month)->format('F Y') . ' Fee',
                dueDate:    Carbon::create($year, $month, 10)->toDateString(),
                isAuto:     true,
            );
        });
    }

    /**
     * Generate one-time and admission fees when a student is enrolled.
     */
    public function generateOnAdmission(User $student, int $classId, int $sessionId): int
    {
        $structures = FeeStructure::with(['feeType', 'category'])
            ->where('is_active', true)
            ->where(function ($q) use ($sessionId) {
                $q->where('session_id', $sessionId)->orWhereNull('session_id');
            })
            ->where(function ($q) use ($classId) {
                $q->where('class_id', $classId)->orWhereNull('class_id');
            })
            ->whereHas('feeType', function ($q) {
                $q->where('trigger', 'admission');
            })
            ->get();

        if ($structures->isEmpty()) {
            return 0;
        }

        return DB::transaction(function () use ($student, $sessionId, $structures) {
            return $this->createInvoiceFromStructures(
                student:    $student,
                structures: $structures,
                sessionId:  $sessionId,
                month:      null,
                year:       null,
                trigger:    'admission',
                title:      'Admission Fees',
                dueDate:    today()->addDays(7)->toDateString(),
                isAuto:     true,
            );
        });
    }

    /**
     * Generate exam fee invoices for all students in a class when exam is published.
     */
    public function generateOnExamPublish(int $examId, int $classId, int $sessionId): array
    {
        // Find exam-type fee structures for this class
        $structures = FeeStructure::with(['feeType', 'category'])
            ->where('is_active', true)
            ->where(function ($q) use ($sessionId) {
                $q->where('session_id', $sessionId)->orWhereNull('session_id');
            })
            ->where(function ($q) use ($classId) {
                $q->where('class_id', $classId)->orWhereNull('class_id');
            })
            ->whereHas('feeType', function ($q) {
                $q->where('trigger', 'exam_publish');
            })
            ->get();

        if ($structures->isEmpty()) {
            return ['generated' => 0, 'skipped' => 0];
        }

        $students = Promotion::with('student')
            ->where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->get();

        $generated = 0;
        $skipped   = 0;

        foreach ($students as $promotion) {
            $student = $promotion->student;
            if (! $student || $student->student_status !== 'active') {
                $skipped++;
                continue;
            }

            // Idempotency: skip if exam invoice already exists for this student+exam
            $alreadyExists = Invoice::where('student_id', $student->id)
                ->where('session_id', $sessionId)
                ->where('description', 'like', "%exam_id:{$examId}%")
                ->exists();

            if ($alreadyExists) {
                $skipped++;
                continue;
            }

            try {
                DB::transaction(function () use ($student, $sessionId, $structures, $examId, &$generated) {
                    $this->createInvoiceFromStructures(
                        student:     $student,
                        structures:  $structures,
                        sessionId:   $sessionId,
                        month:       null,
                        year:        null,
                        trigger:     'exam_publish',
                        title:       'Exam Fee',
                        dueDate:     today()->addDays(14)->toDateString(),
                        isAuto:      true,
                        extraDesc:   "exam_id:{$examId}",
                    );
                    $generated++;
                });
            } catch (\Throwable $e) {
                Log::error("BillingService: exam fee error for student #{$student->id}: " . $e->getMessage());
            }
        }

        return ['generated' => $generated, 'skipped' => $skipped];
    }

    // ── Core invoice builder ──────────────────────────────────────────────────

    /**
     * Create a single invoice with line items from a collection of FeeStructures.
     * Returns 1 (one invoice created) or 0 (nothing to create).
     */
    private function createInvoiceFromStructures(
        User       $student,
        Collection $structures,
        int        $sessionId,
        ?int       $month,
        ?int       $year,
        string     $trigger,
        string     $title,
        string     $dueDate,
        bool       $isAuto = true,
        string     $extraDesc = '',
    ): int {
        if ($structures->isEmpty()) {
            return 0;
        }

        // Load student discounts
        $discounts = StudentFeeDiscount::with('discount')
            ->where('student_id', $student->id)
            ->where('session_id', $sessionId)
            ->where(function ($q) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', today());
            })
            ->where(function ($q) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', today());
            })
            ->get();

        $items         = [];
        $totalGross    = 0;
        $totalDiscount = 0;
        $totalTax      = 0;
        $totalNet      = 0;

        foreach ($structures as $structure) {
            $basePrice = (float) $structure->amount;

            // Calculate discount for this line
            $discountAmount = 0.0;
            foreach ($discounts as $sd) {
                if ($sd->discount) {
                    $discountAmount += $sd->discount->calculate($basePrice);
                }
            }
            $discountAmount = min($discountAmount, $basePrice);
            $afterDiscount  = $basePrice - $discountAmount;

            // Calculate tax
            $taxRate   = $structure->is_taxable ? (float) $structure->tax_rate : 0.0;
            $taxAmount = round($afterDiscount * ($taxRate / 100), 2);
            $lineTotal = round($afterDiscount + $taxAmount, 2);

            $items[] = [
                'fee_type_id'      => $structure->fee_type_id,
                'fee_structure_id' => $structure->id,
                'description'      => $structure->name
                    . ($month ? ' — ' . Carbon::create($year, $month)->format('F Y') : ''),
                'unit_price'       => $basePrice,
                'quantity'         => 1,
                'discount_amount'  => $discountAmount,
                'tax_rate'         => $taxRate,
                'tax_amount'       => $taxAmount,
                'total'            => $lineTotal,
            ];

            $totalGross    += $basePrice;
            $totalDiscount += $discountAmount;
            $totalTax      += $taxAmount;
            $totalNet      += $lineTotal;
        }

        if ($totalNet <= 0) {
            return 0;
        }

        // Create the invoice
        $invoice = Invoice::create([
            'student_id'       => $student->id,
            'session_id'       => $sessionId,
            'fee_type_id'      => $structures->first()->fee_type_id,
            'title'            => $title,
            'description'      => $extraDesc ?: null,
            'total_amount'     => round($totalGross, 2),
            'discount_amount'  => round($totalDiscount, 2),
            'tax_amount'       => round($totalTax, 2),
            'amount'           => round($totalNet, 2),
            'status'           => 'unpaid',
            'due_date'         => $dueDate,
            'billing_month'    => $month,
            'billing_year'     => $year,
            'is_auto_generated'=> $isAuto,
        ]);

        // Create line items
        foreach ($items as $item) {
            InvoiceItem::create(array_merge($item, ['invoice_id' => $invoice->id]));
        }

        // Post debit to student ledger
        StudentLedger::debit(
            studentId:   $student->id,
            amount:      (float) $invoice->amount,
            description: $invoice->title . ' (#' . $invoice->invoice_number . ')',
            type:        'invoice',
            sessionId:   $sessionId,
            reference:   $invoice,
            date:        today()->toDateString(),
        );

        return 1;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Which fee structure frequencies should run in a given month?
     * Monthly:     every month
     * Quarterly:   months 1,4,7,10  (or configurable)
     * Half-yearly: months 1,7
     * Yearly:      month 1 only
     * Once:        never auto-run (manual only)
     */
    private function frequenciesForMonth(int $month): array
    {
        $frequencies = ['monthly'];

        if (in_array($month, [1, 4, 7, 10])) {
            $frequencies[] = 'quarterly';
        }
        if (in_array($month, [1, 7])) {
            $frequencies[] = 'half_yearly';
        }
        if ($month === 1) {
            $frequencies[] = 'yearly';
        }

        return $frequencies;
    }

    private function currentSessionId(): int
    {
        $session = SchoolSession::where('is_current', true)->first()
            ?? SchoolSession::latest()->first();

        if (! $session) {
            throw new \RuntimeException('No active school session found.');
        }

        return $session->id;
    }
}
