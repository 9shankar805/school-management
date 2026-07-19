<?php

namespace App\Http\Controllers;

use App\Models\StudentStatus;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\Promotion;
use App\Traits\SchoolSession as SchoolSessionTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GraduationController extends Controller
{
    use SchoolSessionTrait;

    public function __construct()
    {
        $this->middleware(['auth', 'can:view students']);
    }

    // ── List students eligible for graduation / currently in final class ──
    public function index(Request $request)
    {
        $sessionId = $this->getSchoolCurrentSession();
        $tab       = $request->query('tab', 'eligible');
        $search    = $request->query('search');

        // All classes in session to find "final" class
        $classes = SchoolClass::where('session_id', $sessionId)->get();

        // Graduated/Alumni students (all time)
        $graduatedQuery = User::role('student')
            ->whereHas('statusHistory', fn($q) => $q->whereIn('status', ['graduated', 'alumni'])->where('is_current', true))
            ->with('statusHistory', 'currentStatus');

        // Dropped out students
        $dropoutQuery = User::role('student')
            ->whereHas('statusHistory', fn($q) => $q->whereIn('status', ['dropped_out', 'withdrawn'])->where('is_current', true))
            ->with('currentStatus');

        // Active students (eligible for status change)
        $activeQuery = User::role('student')
            ->whereHas('promotions', fn($q) => $q->where('session_id', $sessionId))
            ->with('currentStatus');

        if ($search) {
            foreach ([$graduatedQuery, $dropoutQuery, $activeQuery] as $q) {
                $q->where(fn($q2) => $q2
                    ->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                );
            }
        }

        $graduated = $graduatedQuery->latest()->paginate(20, ['*'], 'gpage')->withQueryString();
        $dropouts  = $dropoutQuery->latest()->paginate(20, ['*'], 'dpage')->withQueryString();
        $active    = $activeQuery->latest()->paginate(20, ['*'], 'apage')->withQueryString();

        $counts = [
            'active'    => User::role('student')->whereHas('promotions', fn($q) => $q->where('session_id', $sessionId))->count(),
            'graduated' => StudentStatus::whereIn('status', ['graduated', 'alumni'])->where('is_current', true)->count(),
            'dropouts'  => StudentStatus::whereIn('status', ['dropped_out', 'withdrawn'])->where('is_current', true)->count(),
        ];

        return view('students.graduation.index', compact(
            'graduated', 'dropouts', 'active',
            'counts', 'tab', 'search', 'classes', 'sessionId'
        ));
    }

    // ── Process a status change for a single student ──────────────────────
    public function process(Request $request, int $studentId)
    {
        $this->authorize('create students');

        $data = $request->validate([
            'status'                   => 'required|in:' . implode(',', array_keys(StudentStatus::STATUSES)),
            'effective_date'           => 'required|date',
            'reason'                   => 'nullable|string|max:2000',
            'notes'                    => 'nullable|string|max:2000',
            'graduation_certificate_no'=> 'nullable|string|max:100',
            'alumni_batch'             => 'nullable|string|max:100',
            'destination_school'       => 'nullable|string|max:255',
        ]);

        $sessionId = $this->getSchoolCurrentSession();

        DB::transaction(function () use ($data, $studentId, $sessionId) {
            // Mark all previous as not current
            StudentStatus::where('student_id', $studentId)->update(['is_current' => false]);

            $promotion = Promotion::where('student_id', $studentId)
                ->where('session_id', $sessionId)
                ->first();

            StudentStatus::create(array_merge($data, [
                'student_id'   => $studentId,
                'session_id'   => $sessionId,
                'class_id'     => $promotion?->class_id,
                'is_current'   => true,
                'processed_by' => auth()->id(),
            ]));
        });

        $label = StudentStatus::STATUSES[$data['status']] ?? $data['status'];
        return back()->with('status', "Student marked as {$label}.");
    }

    // ── Bulk graduation: mark all students in a class as graduated ────────
    public function bulkGraduate(Request $request)
    {
        $this->authorize('create students');

        $request->validate([
            'class_id'     => 'required|exists:school_classes,id',
            'alumni_batch' => 'required|string|max:100',
            'effective_date'=> 'required|date',
        ]);

        $sessionId = $this->getSchoolCurrentSession();

        $studentIds = Promotion::where('session_id', $sessionId)
            ->where('class_id', $request->class_id)
            ->pluck('student_id');

        $count = 0;
        DB::transaction(function () use ($studentIds, $request, $sessionId, &$count) {
            foreach ($studentIds as $studentId) {
                StudentStatus::where('student_id', $studentId)->update(['is_current' => false]);
                StudentStatus::create([
                    'student_id'     => $studentId,
                    'status'         => 'graduated',
                    'session_id'     => $sessionId,
                    'class_id'       => $request->class_id,
                    'effective_date' => $request->effective_date,
                    'alumni_batch'   => $request->alumni_batch,
                    'is_current'     => true,
                    'processed_by'   => auth()->id(),
                ]);
                $count++;
            }
        });

        return back()->with('status', "{$count} students graduated successfully.");
    }

    // ── Alumni directory ──────────────────────────────────────────────────
    public function alumni(Request $request)
    {
        $search = $request->query('search');
        $batch  = $request->query('batch');

        $query = User::role('student')
            ->whereHas('statusHistory', fn($q) =>
                $q->whereIn('status', ['graduated', 'alumni'])->where('is_current', true)
            )
            ->with('currentStatus');

        if ($search) {
            $query->where(fn($q) => $q
                ->where('first_name', 'like', "%$search%")
                ->orWhere('last_name', 'like', "%$search%")
            );
        }

        if ($batch) {
            $query->whereHas('statusHistory', fn($q) =>
                $q->where('alumni_batch', $batch)->where('is_current', true)
            );
        }

        $alumni = $query->latest()->paginate(24)->withQueryString();

        $batches = StudentStatus::whereIn('status', ['graduated', 'alumni'])
            ->whereNotNull('alumni_batch')
            ->distinct()
            ->pluck('alumni_batch')
            ->sort()
            ->values();

        return view('students.graduation.alumni', compact('alumni', 'batches', 'search', 'batch'));
    }
}
