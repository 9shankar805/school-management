<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Promotion;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\Section;
use App\Models\StudentStatus;
use App\Models\StudentTransfer;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * InterSchoolTransferService
 *
 * Handles the three transfer scenarios for a principal / admin:
 *
 * Scenario 1 — Same-platform transfer
 *   Both source and destination schools are on this SaaS instance.
 *   On approval: student's school_id is reassigned, a new Promotion is
 *   created at the destination school, old promotion is soft-deleted,
 *   historical marks/attendance/documents remain readable (school_id
 *   still references old school so history is not lost), and the
 *   destination school admin is notified.
 *
 * Scenario 2 — Outgoing to external school
 *   Destination is outside the platform. A Transfer Certificate (TC) is
 *   generated with a unique token + serial number. Student status is set
 *   to 'transferred'. TC can be printed as PDF.
 *
 * Scenario 3 — Incoming from another school
 *   Admit a student who comes with a TC from another school. Creates the
 *   user record (or links existing), stores the previous TC reference,
 *   creates a Promotion at the current school, and marks them active.
 */
class InterSchoolTransferService
{
    // ─────────────────────────────────────────────────────────────────────────
    // PUBLIC API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Approve a transfer. Routes to the correct scenario handler.
     */
    public function approve(StudentTransfer $transfer, array $options = []): StudentTransfer
    {
        DB::transaction(function () use ($transfer, $options) {
            $transfer->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'notes'       => $options['notes'] ?? $transfer->notes,
            ]);

            $scenario = $transfer->scenario;

            if ($scenario === 1) {
                $this->executeSamePlatformTransfer($transfer, $options);
            } elseif ($scenario === 2) {
                $this->executeOutgoingTransfer($transfer);
            } elseif ($scenario === 3) {
                $this->executeIncomingTransfer($transfer, $options);
            } else {
                // Intra-school: just update promotion
                $this->executeIntraSchoolTransfer($transfer);
            }
        });

        return $transfer->fresh();
    }

    /**
     * Scenario 1: reassign the student to the destination school.
     * Called after approve() OR when destination school clicks "Accept".
     */
    public function executeSamePlatformTransfer(StudentTransfer $transfer, array $options = []): void
    {
        $student     = User::withoutGlobalScope(\App\Scopes\TenantScope::class)->findOrFail($transfer->student_id);
        $toSchool    = School::findOrFail($transfer->to_school_id);
        $toClassId   = $options['to_class_id']   ?? $transfer->to_class_id;
        $toSectionId = $options['to_section_id']  ?? $transfer->to_section_id;

        // 1. Resolve (or create) a session at the destination school
        $toSession = SchoolSession::where('school_id', $toSchool->id)
            ->latest()
            ->first();

        if (!$toSession) {
            $toSession = SchoolSession::create([
                'school_id'    => $toSchool->id,
                'session_name' => now()->year . '-' . (now()->year + 1),
            ]);
        }

        // 2. Soft-delete old promotion (history preserved via soft delete)
        Promotion::where('student_id', $student->id)
            ->where('session_id', $transfer->from_session_id)
            ->delete();

        // 3. Reassign school_id on the user record
        DB::table('users')
            ->where('id', $student->id)
            ->update(['school_id' => $toSchool->id]);

        // 4. Create new Promotion at destination school
        if ($toClassId || $toSectionId) {
            Promotion::create([
                'school_id'  => $toSchool->id,
                'student_id' => $student->id,
                'class_id'   => $toClassId,
                'section_id' => $toSectionId,
                'session_id' => $toSession->id,
            ]);
        }

        // 5. Reassign school_id on auxiliary records
        $relatedTables = [
            'student_academic_infos',
            'student_parent_infos',
        ];
        foreach ($relatedTables as $tbl) {
            DB::table($tbl)
                ->where('student_id', $student->id)
                ->update(['school_id' => $toSchool->id]);
        }

        // 6. Update student status at old school → transferred
        $this->markAsTransferred($student, $transfer, $toSchool->name);

        // 7. Mark transfer accepted
        $transfer->update([
            'accepted_at' => now(),
            'accepted_by' => auth()->id(),
        ]);

        // 8. Issue a TC as reference document
        $this->issueTC($transfer);

        // 9. Notify destination school admin
        $this->notifyDestinationSchool($transfer, $toSchool);

        Log::info('Scenario 1 transfer completed', [
            'student_id'  => $student->id,
            'from_school' => $transfer->from_school_id,
            'to_school'   => $toSchool->id,
        ]);
    }

    /**
     * Scenario 2: outgoing to external school — issue TC, freeze student.
     */
    public function executeOutgoingTransfer(StudentTransfer $transfer): void
    {
        $student = User::withoutGlobalScope(\App\Scopes\TenantScope::class)->findOrFail($transfer->student_id);

        // 1. Issue TC with unique token
        $this->issueTC($transfer);

        // 2. Mark student as transferred
        $this->markAsTransferred($student, $transfer, $transfer->to_school ?? 'External School');

        Log::info('Scenario 2 transfer completed (TC issued)', [
            'student_id' => $student->id,
            'to_school'  => $transfer->to_school,
            'tc_token'   => $transfer->tc_token,
        ]);
    }

    /**
     * Scenario 3: accept an incoming student with TC from another school.
     */
    public function executeIncomingTransfer(StudentTransfer $transfer, array $options = []): void
    {
        $student = User::withoutGlobalScope(\App\Scopes\TenantScope::class)->findOrFail($transfer->student_id);

        $currentSchool = School::find(config('tenant.school_id'));
        $sessionId     = $options['to_session_id'] ?? $transfer->to_session_id;
        $classId       = $options['to_class_id']   ?? $transfer->to_class_id;
        $sectionId     = $options['to_section_id'] ?? $transfer->to_section_id;

        // 1. Assign student to current school
        DB::table('users')
            ->where('id', $student->id)
            ->update(['school_id' => $currentSchool?->id ?? $student->school_id]);

        // 2. Create Promotion at this school
        if ($classId) {
            Promotion::create([
                'school_id'  => $currentSchool?->id ?? $student->school_id,
                'student_id' => $student->id,
                'class_id'   => $classId,
                'section_id' => $sectionId,
                'session_id' => $sessionId,
            ]);
        }

        // 3. Set student status to active at this school
        StudentStatus::create([
            'school_id'      => $currentSchool?->id,
            'student_id'     => $student->id,
            'status'         => 'active',
            'session_id'     => $sessionId,
            'class_id'       => $classId,
            'effective_date' => $transfer->joining_date ?? now()->toDateString(),
            'notes'          => 'Enrolled via transfer from ' . ($transfer->previous_school_name ?? 'another school') . '. Previous TC: ' . ($transfer->previous_tc_number ?? 'N/A'),
            'is_current'     => true,
            'processed_by'   => auth()->id(),
        ]);

        // 4. Mark transfer accepted
        $transfer->update([
            'accepted_at' => now(),
            'accepted_by' => auth()->id(),
        ]);

        Log::info('Scenario 3 incoming transfer completed', [
            'student_id'    => $student->id,
            'from_school'   => $transfer->previous_school_name,
            'previous_tc'   => $transfer->previous_tc_number,
        ]);
    }

    /**
     * Intra-school transfer (inter_class / inter_section): update Promotion only.
     */
    public function executeIntraSchoolTransfer(StudentTransfer $transfer): void
    {
        Promotion::where('student_id', $transfer->student_id)
            ->where('session_id', $transfer->to_session_id)
            ->update([
                'class_id'   => $transfer->to_class_id   ?? DB::raw('class_id'),
                'section_id' => $transfer->to_section_id ?? DB::raw('section_id'),
            ]);
    }

    /**
     * Generate and save a unique TC token + serial number.
     */
    public function issueTC(StudentTransfer $transfer): void
    {
        if ($transfer->tc_token) {
            return; // already issued
        }

        $year     = now()->year;
        $serial   = str_pad(StudentTransfer::whereYear('tc_issued_at', $year)->count() + 1, 4, '0', STR_PAD_LEFT);
        $tcNumber = 'TC-' . $year . '-' . $serial;
        $token    = Str::upper(Str::random(12)) . '-' . $transfer->id;

        $transfer->update([
            'tc_token'    => $token,
            'tc_issued_at'=> now(),
            'tc_number'   => $tcNumber,
            'status'      => 'approved',
        ]);
    }

    /**
     * Verify a TC token. Returns the transfer record or null.
     */
    public function verifyTC(string $token): ?StudentTransfer
    {
        return StudentTransfer::with([
            'student', 'fromSchool', 'toSchool', 'fromClass', 'toClass',
        ])
        ->where('tc_token', $token)
        ->where('status', 'approved')
        ->first();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function markAsTransferred(User $student, StudentTransfer $transfer, string $destinationName): void
    {
        // Deactivate any current status
        StudentStatus::where('student_id', $student->id)
            ->where('is_current', true)
            ->update(['is_current' => false]);

        StudentStatus::create([
            'school_id'        => $transfer->school_id,
            'student_id'       => $student->id,
            'status'           => 'transferred',
            'session_id'       => $transfer->from_session_id,
            'class_id'         => $transfer->from_class_id,
            'effective_date'   => $transfer->transfer_date ?? now()->toDateString(),
            'reason'           => $transfer->reason,
            'destination_school' => $destinationName,
            'is_current'       => true,
            'processed_by'     => auth()->id(),
        ]);
    }

    private function notifyDestinationSchool(StudentTransfer $transfer, School $toSchool): void
    {
        try {
            $admins = User::where('school_id', $toSchool->id)
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'principal']))
                ->get();

            foreach ($admins as $admin) {
                $admin->notify(new GeneralNotification(
                    'Incoming Student Transfer',
                    'Student ' . optional($transfer->student)->first_name . ' '
                        . optional($transfer->student)->last_name
                        . ' has been transferred to your school. '
                        . 'Please assign them to a class.',
                    route('student.transfers.index')
                ));
            }
        } catch (\Throwable $e) {
            Log::warning('Could not notify destination school admins: ' . $e->getMessage());
        }
    }
}
