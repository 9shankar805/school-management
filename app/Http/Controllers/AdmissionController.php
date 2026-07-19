<?php

namespace App\Http\Controllers;

use App\Models\AdmissionApplication;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\User;
use App\Repositories\PromotionRepository;
use App\Repositories\StudentParentInfoRepository;
use App\Repositories\StudentAcademicInfoRepository;
use App\Traits\SchoolSession as SchoolSessionTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdmissionController extends Controller
{
    use SchoolSessionTrait;

    public function __construct()
    {
        $this->middleware('auth');
    }

    // ── List all applications ─────────────────────────────────────────────
    public function index(Request $request)
    {
        $this->authorize('view students');

        $status    = $request->query('status', 'all');
        $search    = $request->query('search');
        $sessionId = $this->getSchoolCurrentSession();

        $query = AdmissionApplication::with('reviewer', 'schoolClass')
            ->where('session_id', $sessionId)
            ->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name',  'like', "%{$search}%")
                  ->orWhere('email',      'like', "%{$search}%")
                  ->orWhere('application_number', 'like', "%{$search}%");
            });
        }

        $applications = $query->paginate(20)->withQueryString();
        $counts = AdmissionApplication::where('session_id', $sessionId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $classes = SchoolClass::where('session_id', $sessionId)->get();

        return view('admissions.index', compact('applications', 'counts', 'status', 'search', 'classes'));
    }

    // ── New application form (public-accessible or staff-facing) ─────────
    public function create()
    {
        $sessionId = $this->getSchoolCurrentSession();
        $sessions  = SchoolSession::latest()->take(5)->get();
        $classes   = SchoolClass::where('session_id', $sessionId)->get();

        return view('admissions.create', compact('sessions', 'classes'));
    }

    // ── Submit application ────────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'        => 'required|string|max:100',
            'last_name'         => 'required|string|max:100',
            'email'             => 'nullable|email|max:255',
            'phone'             => 'nullable|string|max:30',
            'birthday'          => 'nullable|date',
            'gender'            => 'nullable|in:Male,Female,Other',
            'nationality'       => 'nullable|string|max:100',
            'religion'          => 'nullable|string|max:100',
            'blood_type'        => 'nullable|string|max:10',
            'address'           => 'nullable|string|max:500',
            'session_id'        => 'nullable|exists:school_sessions,id',
            'class_id'          => 'nullable|exists:school_classes,id',
            'guardian_name'     => 'nullable|string|max:255',
            'guardian_phone'    => 'nullable|string|max:30',
            'guardian_email'    => 'nullable|email|max:255',
            'guardian_relation' => 'nullable|string|max:100',
            'previous_school'   => 'nullable|string|max:255',
            'previous_class'    => 'nullable|string|max:100',
        ]);

        AdmissionApplication::create(array_merge($data, [
            'application_number' => AdmissionApplication::generateNumber(),
            'status'             => 'pending',
        ]));

        return redirect()->route('admissions.index')
            ->with('status', 'Application submitted successfully.');
    }

    // ── Show single application ───────────────────────────────────────────
    public function show(int $id)
    {
        $this->authorize('view students');
        $application = AdmissionApplication::with('reviewer', 'student', 'schoolClass')->findOrFail($id);
        $sessionId   = $this->getSchoolCurrentSession();
        $classes     = SchoolClass::where('session_id', $sessionId)->get();

        return view('admissions.show', compact('application', 'classes'));
    }

    // ── Advance status: pending → under_review → approved/rejected ────────
    public function review(Request $request, int $id)
    {
        $this->authorize('create students');

        $request->validate([
            'status'         => 'required|in:under_review,approved,rejected',
            'reviewer_notes' => 'nullable|string|max:2000',
        ]);

        $application = AdmissionApplication::findOrFail($id);
        $application->update([
            'status'         => $request->status,
            'reviewer_notes' => $request->reviewer_notes,
            'reviewed_by'    => auth()->id(),
            'reviewed_at'    => now(),
        ]);

        return back()->with('status', 'Application updated to: ' . ucfirst(str_replace('_', ' ', $request->status)));
    }

    // ── Enroll: convert approved application into a real student account ──
    public function enroll(Request $request, int $id)
    {
        $this->authorize('create students');

        $application = AdmissionApplication::findOrFail($id);

        if ($application->status !== 'approved') {
            return back()->withErrors('Application must be approved before enrollment.');
        }

        $request->validate([
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:8',
            'session_id' => 'required|exists:school_sessions,id',
            'class_id'   => 'required|exists:school_classes,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        DB::transaction(function () use ($application, $request) {
            // 1. Create user account
            $student = User::create([
                'first_name'  => $application->first_name,
                'last_name'   => $application->last_name,
                'email'       => $request->email,
                'password'    => Hash::make($request->password),
                'gender'      => $application->gender,
                'birthday'    => $application->birthday,
                'nationality' => $application->nationality,
                'religion'    => $application->religion,
                'blood_type'  => $application->blood_type,
                'address'     => $application->address,
                'phone'       => $application->phone,
                'role'        => 'student',
            ]);

            $student->assignRole('student');

            $student->givePermissionTo([
                'view attendances', 'view assignments', 'submit assignments',
                'view exams', 'view marks', 'view users',
                'view routines', 'view syllabi', 'view events', 'view notices',
            ]);

            // 2. Academic info
            $acInfo = new StudentAcademicInfoRepository();
            $acInfo->store(['registration_no' => null], $student->id);

            // 3. Parent/guardian info from application
            $parentRepo = new StudentParentInfoRepository();
            $parentRepo->store([
                'guardian_name'  => $application->guardian_name,
                'guardian_phone' => $application->guardian_phone,
                'guardian_email' => $application->guardian_email,
            ], $student->id);

            // 4. Assign class/section
            $promoRepo = new PromotionRepository();
            $promoRepo->assignClassSection([
                'session_id' => $request->session_id,
                'class_id'   => $request->class_id,
                'section_id' => $request->section_id,
                'id_card_number' => null,
            ], $student->id);

            // 5. Mark application as enrolled
            $application->update([
                'status'     => 'enrolled',
                'student_id' => $student->id,
            ]);
        });

        return redirect()->route('admissions.index')
            ->with('status', 'Student enrolled successfully.');
    }

    // ── Destroy (soft-delete) ─────────────────────────────────────────────
    public function destroy(int $id)
    {
        $this->authorize('create students');
        AdmissionApplication::findOrFail($id)->delete();
        return back()->with('status', 'Application deleted.');
    }
}
