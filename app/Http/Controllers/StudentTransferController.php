<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\Section;
use App\Models\StudentTransfer;
use App\Models\User;
use App\Services\InterSchoolTransferService;
use App\Traits\SchoolSession as SchoolSessionTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentTransferController extends Controller
{
    use SchoolSessionTrait;

    public function __construct(private InterSchoolTransferService $svc)
    {
        $this->middleware(['auth']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $this->authorize('view students');

        $query = StudentTransfer::with([
            'student', 'fromClass', 'toClass',
            'fromSection', 'toSection',
            'fromSchool', 'toSchool',
            'approver',
        ])->latest();

        // Filter by type
        if ($type = $request->query('type')) {
            $query->where('transfer_type', $type);
        }

        // Filter by status
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $transfers = $query->paginate(20)->withQueryString();

        $stats = [
            'total'    => StudentTransfer::count(),
            'pending'  => StudentTransfer::where('status', 'pending')->count(),
            'approved' => StudentTransfer::where('status', 'approved')->count(),
            'incoming' => StudentTransfer::where('transfer_type', 'incoming')->count(),
        ];

        return view('students.transfers.index', compact('transfers', 'stats'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────────────────────────────────────

    public function create(int $studentId)
    {
        $this->authorize('create students');

        $student   = User::findOrFail($studentId);
        $sessionId = $this->getSchoolCurrentSession();

        $fromPromotion = Promotion::with('schoolClass', 'section')
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->first();

        $classes        = SchoolClass::where('session_id', $sessionId)->get();
        $sections       = Section::where('session_id', $sessionId)->get();
        $platformSchools = School::where('status', 'active')
            ->when(auth()->user()->school_id, fn ($q, $id) => $q->where('id', '!=', $id))
            ->orderBy('name')
            ->get();

        return view('students.transfers.create', compact(
            'student', 'fromPromotion', 'classes', 'sections', 'platformSchools'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CREATE INCOMING (admits a new student from another school with TC)
    // ─────────────────────────────────────────────────────────────────────────

    public function createIncoming()
    {
        $this->authorize('create students');

        $sessionId = $this->getSchoolCurrentSession();
        $classes   = SchoolClass::where('session_id', $sessionId)->get();
        $sections  = Section::where('session_id', $sessionId)->get();
        $sessions  = SchoolSession::where('school_id', auth()->user()->school_id)
                        ->latest()->get();

        return view('students.transfers.create-incoming', compact('classes', 'sections', 'sessions'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────────────────────────────────

    public function store(Request $request, int $studentId)
    {
        $this->authorize('create students');

        $data = $request->validate([
            'transfer_type'   => 'required|in:inter_class,inter_section,inter_school',
            'to_class_id'     => 'nullable|exists:school_classes,id',
            'to_section_id'   => 'nullable|exists:sections,id',
            'to_school'       => 'nullable|string|max:255',
            'to_school_id'    => 'nullable|exists:schools,id',
            'transfer_date'   => 'required|date',
            'reason'          => 'nullable|string|max:1000',
            'carry_marksheet' => 'boolean',
            'carry_character' => 'boolean',
            'carry_birth_cert'=> 'boolean',
            'carry_medical'   => 'boolean',
        ]);

        $sessionId     = $this->getSchoolCurrentSession();
        $fromPromotion = Promotion::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->first();

        StudentTransfer::create(array_merge($data, [
            'student_id'      => $studentId,
            'school_id'       => auth()->user()->school_id,
            'from_school_id'  => auth()->user()->school_id,
            'from_school'     => auth()->user()->school?->name,
            'from_session_id' => $sessionId,
            'from_class_id'   => $fromPromotion?->class_id,
            'from_section_id' => $fromPromotion?->section_id,
            'to_session_id'   => $sessionId,
            'status'          => 'pending',
            'carry_marksheet' => (bool) ($data['carry_marksheet'] ?? false),
            'carry_character' => (bool) ($data['carry_character'] ?? false),
            'carry_birth_cert'=> (bool) ($data['carry_birth_cert'] ?? false),
            'carry_medical'   => (bool) ($data['carry_medical'] ?? false),
        ]));

        return redirect()->route('student.transfers.index')
            ->with('status', 'Transfer request submitted successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STORE INCOMING
    // ─────────────────────────────────────────────────────────────────────────

    public function storeIncoming(Request $request)
    {
        $this->authorize('create students');

        $data = $request->validate([
            // New student fields
            'first_name'          => 'required|string|max:100',
            'last_name'           => 'required|string|max:100',
            'email'               => 'nullable|email|unique:users,email',
            'gender'              => 'nullable|in:male,female,other',
            'birthday'            => 'nullable|date',
            'phone'               => 'nullable|string|max:20',
            'address'             => 'nullable|string|max:255',
            'nationality'         => 'nullable|string|max:80',
            'religion'            => 'nullable|string|max:80',
            // Placement at this school
            'to_class_id'         => 'nullable|exists:school_classes,id',
            'to_section_id'       => 'nullable|exists:sections,id',
            'to_session_id'       => 'nullable|exists:school_sessions,id',
            'joining_date'        => 'required|date',
            // Previous school TC info
            'previous_school_name'=> 'required|string|max:255',
            'previous_tc_number'  => 'nullable|string|max:100',
            'reason'              => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($data) {
            $schoolId  = auth()->user()->school_id;
            $sessionId = $data['to_session_id'] ?? $this->getSchoolCurrentSession();

            // Create or find the student user record
            $password = \Illuminate\Support\Str::random(10);
            $student  = User::create([
                'school_id'    => $schoolId,
                'first_name'   => $data['first_name'],
                'last_name'    => $data['last_name'],
                'email'        => $data['email'] ?? (strtolower($data['first_name'] . '.' . $data['last_name']) . rand(100, 999) . '@student.local'),
                'password'     => bcrypt($password),
                'gender'       => $data['gender'] ?? null,
                'birthday'     => $data['birthday'] ?? null,
                'phone'        => $data['phone'] ?? null,
                'address'      => $data['address'] ?? null,
                'nationality'  => $data['nationality'] ?? null,
                'religion'     => $data['religion'] ?? null,
                'role'         => 'student',
            ]);
            $student->assignRole('student');

            // Transfer record (incoming type)
            $transfer = StudentTransfer::create([
                'school_id'           => $schoolId,
                'student_id'          => $student->id,
                'transfer_type'       => 'incoming',
                'to_class_id'         => $data['to_class_id'] ?? null,
                'to_section_id'       => $data['to_section_id'] ?? null,
                'to_session_id'       => $sessionId,
                'previous_school_name'=> $data['previous_school_name'],
                'previous_tc_number'  => $data['previous_tc_number'] ?? null,
                'reason'              => $data['reason'] ?? null,
                'transfer_date'       => $data['joining_date'],
                'joining_date'        => $data['joining_date'],
                'status'              => 'pending',
            ]);

            // Auto-approve the incoming (admin is registering them directly)
            $this->svc->approve($transfer, [
                'to_class_id'    => $data['to_class_id'] ?? null,
                'to_section_id'  => $data['to_section_id'] ?? null,
                'to_session_id'  => $sessionId,
            ]);
        });

        return redirect()->route('student.transfers.index')
            ->with('status', 'Incoming student admitted via transfer successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────────────────────────────────────

    public function show(int $id)
    {
        $this->authorize('view students');

        $transfer = StudentTransfer::with([
            'student', 'fromClass', 'toClass',
            'fromSection', 'toSection',
            'fromSchool', 'toSchool',
            'approver', 'acceptedBy',
        ])->findOrFail($id);

        return view('students.transfers.show', compact('transfer'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // APPROVE
    // ─────────────────────────────────────────────────────────────────────────

    public function approve(Request $request, int $id)
    {
        $this->authorize('create students');

        $request->validate([
            'status'         => 'required|in:approved,rejected',
            'notes'          => 'nullable|string|max:1000',
            'to_class_id'    => 'nullable|exists:school_classes,id',
            'to_section_id'  => 'nullable|exists:sections,id',
        ]);

        $transfer = StudentTransfer::with('student')->findOrFail($id);

        if ($request->status === 'rejected') {
            $transfer->update([
                'status'      => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'notes'       => $request->notes,
            ]);
            return back()->with('status', 'Transfer rejected.');
        }

        // Approve — route to service
        $this->svc->approve($transfer, [
            'notes'          => $request->notes,
            'to_class_id'    => $request->to_class_id,
            'to_section_id'  => $request->to_section_id,
        ]);

        $msg = match ($transfer->scenario) {
            1 => 'Transfer approved — student reassigned to destination school.',
            2 => 'Transfer approved — Transfer Certificate issued (TC: ' . $transfer->fresh()->tc_number . ').',
            3 => 'Incoming transfer accepted — student enrolled.',
            default => 'Transfer approved.',
        };

        return back()->with('status', $msg);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ACCEPT (Destination school action for Scenario 1)
    // ─────────────────────────────────────────────────────────────────────────

    public function accept(Request $request, int $id)
    {
        $this->authorize('create students');

        $request->validate([
            'to_class_id'   => 'required|exists:school_classes,id',
            'to_section_id' => 'nullable|exists:sections,id',
        ]);

        $transfer = StudentTransfer::findOrFail($id);

        if ($transfer->to_school_id !== auth()->user()->school_id) {
            abort(403, 'You are not the destination school for this transfer.');
        }

        $this->svc->executeSamePlatformTransfer($transfer, [
            'to_class_id'   => $request->to_class_id,
            'to_section_id' => $request->to_section_id,
        ]);

        return redirect()->route('student.transfers.index')
            ->with('status', 'Transfer accepted — student is now enrolled in your school.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC PDF DOWNLOAD
    // ─────────────────────────────────────────────────────────────────────────

    public function downloadTC(int $id)
    {
        $this->authorize('view students');

        $transfer = StudentTransfer::with([
            'student', 'fromClass', 'toClass', 'fromSchool', 'toSchool',
        ])->findOrFail($id);

        // Issue TC if not already issued
        if (!$transfer->isTcIssued()) {
            $this->svc->issueTC($transfer);
            $transfer->refresh();
        }

        $student = $transfer->student;
        $school  = $transfer->fromSchool ?? auth()->user()->school;

        // Guardian name
        $guardian = $student->parents()->withoutGlobalScope(\App\Scopes\TenantScope::class)->first();
        $guardianName = $guardian
            ? ($guardian->first_name . ' ' . $guardian->last_name)
            : ($student->guardian_id ? (User::find($student->guardian_id)?->first_name . ' ' . User::find($student->guardian_id)?->last_name) : 'Guardian');

        $fromClass = $transfer->fromClass?->class_name ?? 'N/A';

        $pdf = Pdf::loadView('students.transfers.tc-pdf', compact(
            'transfer', 'student', 'school', 'guardianName', 'fromClass'
        ))
        ->setPaper('A4', 'portrait')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => false,
            'defaultFont'          => 'dejavu sans',
        ]);

        $filename = 'TC-' . $transfer->tc_number . '-' . str_replace(' ', '_', $student->first_name . '_' . $student->last_name) . '.pdf';

        return $pdf->download($filename);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TC VERIFICATION (public — no auth)
    // ─────────────────────────────────────────────────────────────────────────

    public function verifyTC(string $token)
    {
        $transfer = $this->svc->verifyTC($token);

        return view('students.transfers.verify-tc', compact('transfer', 'token'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────────────────────────────────────

    public function destroy(int $id)
    {
        $this->authorize('create students');
        StudentTransfer::findOrFail($id)->delete();
        return back()->with('status', 'Transfer record deleted.');
    }
}
