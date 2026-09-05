<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Mark;
use App\Models\Notice;
use App\Models\Syllabus;
use App\Models\User;
use App\Repositories\NoticeRepository;
use App\Repositories\PromotionRepository;
use App\Repositories\StudentAcademicInfoRepository;
use App\Repositories\StudentParentInfoRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * OfflineSyncController
 *
 * Receives batched offline operations from POST /api/offline/sync
 * and applies each one to the database, returning per-operation results.
 *
 * Request body (JSON):
 * {
 *   "operations": [
 *     {
 *       "local_id":  1,
 *       "module":    "student",
 *       "action":    "store",
 *       "endpoint":  "/student/create",
 *       "method":    "POST",
 *       "payload":   { ...form fields... }
 *     },
 *     ...
 *   ]
 * }
 *
 * Response:
 * {
 *   "results": [
 *     { "local_id": 1, "ok": true,  "server_id": 42, "message": "Student created." },
 *     { "local_id": 2, "ok": false, "message": "Validation failed: first_name required." },
 *     ...
 *   ],
 *   "synced":  5,
 *   "failed":  1
 * }
 */
class OfflineSyncController extends Controller
{
    // Maximum operations per batch to prevent abuse
    const MAX_OPS = 100;

    public function sync(Request $request): JsonResponse
    {
        $operations = $request->input('operations', []);

        if (!is_array($operations) || empty($operations)) {
            return response()->json(['error' => 'No operations provided.'], 422);
        }

        $operations = array_slice($operations, 0, self::MAX_OPS);

        $results = [];
        $synced  = 0;
        $failed  = 0;

        foreach ($operations as $op) {
            $localId = $op['local_id'] ?? null;
            $module  = $op['module']   ?? '';
            $action  = $op['action']   ?? 'store';
            $payload = $op['payload']  ?? [];

            Log::info("[OfflineSync] Processing local_id={$localId} module={$module} action={$action}");

            try {
                $result = $this->dispatch($module, $action, $payload, $request);
                $results[] = array_merge(['local_id' => $localId, 'ok' => true], $result);
                $synced++;
            } catch (\Throwable $e) {
                Log::warning("[OfflineSync] Failed local_id={$localId}: " . $e->getMessage());
                $results[] = [
                    'local_id' => $localId,
                    'ok'       => false,
                    'message'  => $e->getMessage(),
                ];
                $failed++;
            }
        }

        return response()->json([
            'results' => $results,
            'synced'  => $synced,
            'failed'  => $failed,
        ]);
    }

    // ── Dispatch to module handler ─────────────────────────────────────────────

    private function dispatch(string $module, string $action, array $payload, Request $request): array
    {
        return match ($module) {
            'student'        => $this->handleStudent($payload, $request),
            'syllabus'       => $this->handleSyllabus($payload, $request),
            'assignment'     => $this->handleAssignment($payload, $request),
            'marks'          => $this->handleMarks($payload, $request),
            'notice'         => $this->handleNotice($payload, $request),
            'attendance'     => $this->handleAttendance($payload, $request),
            'admission'      => $this->handleAdmission($payload, $request),
            'fee_collection' => $this->handleFeeCollection($payload, $request),
            'expense'        => $this->handleExpense($payload, $request),
            'income'         => $this->handleIncome($payload, $request),
            default          => throw new \InvalidArgumentException("Unknown module: {$module}"),
        };
    }

    // ── Module handlers ────────────────────────────────────────────────────────

    private function handleStudent(array $p, Request $req): array
    {
        $v = Validator::make($p, [
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email',
            'class_id'   => 'required|exists:school_classes,id',
            'session_id' => 'required|exists:school_sessions,id',
        ]);
        if ($v->fails()) throw new \InvalidArgumentException($v->errors()->first());

        return DB::transaction(function () use ($p) {
            $student = User::create([
                'first_name'     => $p['first_name'],
                'last_name'      => $p['last_name'],
                'email'          => $p['email'],
                'password'       => Hash::make($p['password'] ?? 'Password@123'),
                'gender'         => $p['gender']      ?? null,
                'birthday'       => $p['dob']         ?? $p['birthday'] ?? null,
                'phone'          => $p['phone']        ?? null,
                'address'        => $p['address']      ?? null,
                'role'           => 'student',
                'admission_date' => today()->toDateString(),
                'student_status' => 'active',
                'admission_no'   => 'ADM-' . now()->year . '-' . str_pad(
                    User::whereYear('created_at', now()->year)->count() + 1, 5, '0', STR_PAD_LEFT
                ),
            ]);

            $student->assignRole('student');

            // Academic info
            (new StudentAcademicInfoRepository())->store(['registration_no' => null], $student->id);

            // Parent / guardian info
            if (!empty($p['guardian_name'])) {
                (new StudentParentInfoRepository())->store([
                    'guardian_name'  => $p['guardian_name'],
                    'guardian_phone' => $p['guardian_phone'] ?? null,
                    'guardian_email' => $p['guardian_email'] ?? null,
                ], $student->id);
            }

            // Assign class / section
            (new PromotionRepository())->assignClassSection([
                'session_id'     => $p['session_id'],
                'class_id'       => $p['class_id'],
                'section_id'     => $p['section_id'] ?? null,
                'id_card_number' => null,
            ], $student->id);

            return ['server_id' => $student->id, 'message' => 'Student created successfully.'];
        });
    }

    private function handleSyllabus(array $p, Request $req): array
    {
        $v = Validator::make($p, [
            'class_id'   => 'required|exists:school_classes,id',
            'course_id'  => 'required',
            'session_id' => 'required',
        ]);
        if ($v->fails()) throw new \InvalidArgumentException($v->errors()->first());

        $syllabus = Syllabus::create([
            'title'       => $p['title']       ?? $p['topic']       ?? 'Topic',
            'class_id'    => $p['class_id'],
            'course_id'   => $p['course_id'],
            'section_id'  => $p['section_id']  ?? null,
            'session_id'  => $p['session_id'],
            'details'     => $p['details']     ?? $p['description'] ?? null,
            'created_by'  => auth()->id(),
        ]);

        return ['server_id' => $syllabus->id, 'message' => 'Syllabus created.'];
    }

    private function handleAssignment(array $p, Request $req): array
    {
        $v = Validator::make($p, [
            'title'      => 'required|string|max:255',
            'class_id'   => 'required|exists:school_classes,id',
            'course_id'  => 'required',
            'session_id' => 'required',
        ]);
        if ($v->fails()) throw new \InvalidArgumentException($v->errors()->first());

        $assignment = Assignment::create([
            'title'       => $p['title'],
            'class_id'    => $p['class_id'],
            'section_id'  => $p['section_id']  ?? null,
            'course_id'   => $p['course_id'],
            'session_id'  => $p['session_id'],
            'teacher_id'  => auth()->id(),
            'due_date'    => $p['due_date']     ?? null,
            'description' => $p['description']  ?? null,
            'total_marks' => $p['total_marks']  ?? null,
        ]);

        return ['server_id' => $assignment->id, 'message' => 'Assignment created.'];
    }

    private function handleMarks(array $p, Request $req): array
    {
        $v = Validator::make($p, [
            'student_id' => 'required',
            'course_id'  => 'required',
            'exam_id'    => 'required',
        ]);
        if ($v->fails()) throw new \InvalidArgumentException($v->errors()->first());

        $mark = Mark::updateOrCreate(
            [
                'student_id' => $p['student_id'],
                'course_id'  => $p['course_id'],
                'exam_id'    => $p['exam_id'],
            ],
            [
                'marks'       => $p['marks']      ?? 0,
                'session_id'  => $p['session_id'] ?? null,
            ]
        );

        return ['server_id' => $mark->id, 'message' => 'Marks saved.'];
    }

    private function handleNotice(array $p, Request $req): array
    {
        $v = Validator::make($p, [
            'notice_title' => 'required|string|max:255',
        ]);
        if ($v->fails()) throw new \InvalidArgumentException($v->errors()->first());

        try {
            $repo = new NoticeRepository();
            $notice = $repo->store([
                'notice_title'       => $p['notice_title'] ?? $p['title'] ?? '',
                'description'        => $p['description']  ?? $p['notice_description'] ?? '',
                'notice_date'        => $p['notice_date']  ?? today()->toDateString(),
                'class_id'           => $p['class_id']     ?? null,
                'school_session_id'  => $p['school_session_id'] ?? null,
            ]);

            $id = is_object($notice) ? $notice->id : null;
        } catch (\Throwable $e) {
            // Fallback: direct create if repository interface differs
            $notice = Notice::create([
                'notice_title' => $p['notice_title'] ?? $p['title'] ?? '',
                'description'  => $p['description']  ?? '',
                'notice_date'  => $p['notice_date']   ?? today()->toDateString(),
                'class_id'     => $p['class_id']      ?? null,
                'created_by'   => auth()->id(),
            ]);
            $id = $notice->id;
        }

        return ['server_id' => $id, 'message' => 'Notice posted.'];
    }

    private function handleAttendance(array $p, Request $req): array
    {
        // Delegate to a fake request that reuses AttendanceController@store logic.
        // Rebuild as a proper Request so the controller can handle it cleanly.
        $fakeRequest = Request::create('/attendances/store', 'POST', $p);
        $fakeRequest->setUserResolver(fn () => $req->user());

        try {
            $controller = app(\App\Http\Controllers\AttendanceController::class);
            $response   = $controller->store($fakeRequest);
            return ['server_id' => null, 'message' => 'Attendance saved.'];
        } catch (\Throwable $e) {
            throw new \RuntimeException('Attendance save failed: ' . $e->getMessage());
        }
    }

    private function handleAdmission(array $p, Request $req): array
    {
        // Create an admission application (not full enrolment — needs review)
        $v = Validator::make($p, [
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
        ]);
        if ($v->fails()) throw new \InvalidArgumentException($v->errors()->first());

        $class = '\App\Models\AdmissionApplication';
        if (!class_exists($class)) {
            throw new \RuntimeException('AdmissionApplication model not found on this installation.');
        }

        $app = $class::create([
            'first_name'      => $p['first_name'],
            'last_name'       => $p['last_name'],
            'gender'          => $p['gender']          ?? null,
            'birthday'        => $p['birthday']        ?? $p['dob'] ?? null,
            'phone'           => $p['phone']            ?? null,
            'address'         => $p['address']          ?? null,
            'guardian_name'   => $p['guardian_name']   ?? null,
            'guardian_phone'  => $p['guardian_phone']  ?? null,
            'guardian_email'  => $p['guardian_email']  ?? null,
            'status'          => 'under_review',
            'submitted_at'    => now(),
        ]);

        return ['server_id' => $app->id, 'message' => 'Admission application queued for review.'];
    }

    private function handleFeeCollection(array $p, Request $req): array
    {
        // Cash payment record only (online gateways can't work offline anyway)
        $v = Validator::make($p, [
            'invoice_id' => 'required',
            'amount'     => 'required|numeric|min:0',
        ]);
        if ($v->fails()) throw new \InvalidArgumentException($v->errors()->first());

        $fakeRequest = Request::create('/finance/payments', 'POST', $p);
        $fakeRequest->setUserResolver(fn () => $req->user());

        try {
            $controller = app(\App\Http\Controllers\PaymentController::class);
            $controller->processPayment($fakeRequest);
            return ['server_id' => null, 'message' => 'Payment recorded.'];
        } catch (\Throwable $e) {
            throw new \RuntimeException('Payment failed: ' . $e->getMessage());
        }
    }

    private function handleExpense(array $p, Request $req): array
    {
        $v = Validator::make($p, [
            'title'  => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);
        if ($v->fails()) throw new \InvalidArgumentException($v->errors()->first());

        $fakeRequest = Request::create('/finance/expenses', 'POST', $p);
        $fakeRequest->setUserResolver(fn () => $req->user());

        try {
            $controller = app(\App\Http\Controllers\ExpenseController::class);
            $response   = $controller->store($fakeRequest);
            return ['server_id' => null, 'message' => 'Expense saved.'];
        } catch (\Throwable $e) {
            throw new \RuntimeException('Expense save failed: ' . $e->getMessage());
        }
    }

    private function handleIncome(array $p, Request $req): array
    {
        $v = Validator::make($p, [
            'title'  => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);
        if ($v->fails()) throw new \InvalidArgumentException($v->errors()->first());

        $fakeRequest = Request::create('/finance/income', 'POST', $p);
        $fakeRequest->setUserResolver(fn () => $req->user());

        try {
            $controller = app(\App\Http\Controllers\IncomeController::class);
            $response   = $controller->store($fakeRequest);
            return ['server_id' => null, 'message' => 'Income saved.'];
        } catch (\Throwable $e) {
            throw new \RuntimeException('Income save failed: ' . $e->getMessage());
        }
    }
}
