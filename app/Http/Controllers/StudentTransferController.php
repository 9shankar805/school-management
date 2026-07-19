<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\StudentTransfer;
use App\Models\User;
use App\Traits\SchoolSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentTransferController extends Controller
{
    use SchoolSession;

    public function __construct()
    {
        $this->middleware(['can:view students']);
    }

    // ── List all pending + recent transfers ───────────────────────────────
    public function index()
    {
        $this->authorize('view students');

        $transfers = StudentTransfer::with([
            'student', 'fromClass', 'toClass', 'fromSection', 'toSection', 'approver',
        ])
        ->latest()
        ->paginate(20);

        return view('students.transfers.index', compact('transfers'));
    }

    // ── Show transfer form for a specific student ─────────────────────────
    public function create(int $studentId)
    {
        $this->authorize('create students');

        $student   = User::findOrFail($studentId);
        $sessionId = $this->getSchoolCurrentSession();

        $fromPromotion = Promotion::with('schoolClass', 'section')
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->first();

        $classes   = SchoolClass::where('session_id', $sessionId)->get();
        $sections  = Section::where('session_id', $sessionId)->get();

        return view('students.transfers.create', compact(
            'student', 'fromPromotion', 'classes', 'sections'
        ));
    }

    // ── Submit a transfer request ─────────────────────────────────────────
    public function store(Request $request, int $studentId)
    {
        $this->authorize('create students');

        $data = $request->validate([
            'transfer_type'   => 'required|in:inter_class,inter_section,inter_school',
            'to_class_id'     => 'nullable|exists:school_classes,id',
            'to_section_id'   => 'nullable|exists:sections,id',
            'to_school'       => 'nullable|string|max:255',
            'transfer_date'   => 'required|date',
            'reason'          => 'nullable|string|max:1000',
        ]);

        $sessionId = $this->getSchoolCurrentSession();

        $fromPromotion = Promotion::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->first();

        StudentTransfer::create(array_merge($data, [
            'student_id'      => $studentId,
            'from_session_id' => $sessionId,
            'from_class_id'   => $fromPromotion?->class_id,
            'from_section_id' => $fromPromotion?->section_id,
            'to_session_id'   => $sessionId,
            'status'          => 'pending',
        ]));

        return redirect()->route('student.transfers.index')
            ->with('status', 'Transfer request submitted.');
    }

    // ── Approve / reject ──────────────────────────────────────────────────
    public function approve(Request $request, int $id)
    {
        $this->authorize('create students');

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'notes'  => 'nullable|string|max:1000',
        ]);

        $transfer = StudentTransfer::with('student')->findOrFail($id);

        DB::transaction(function () use ($transfer, $request) {
            $transfer->update([
                'status'      => $request->status,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'notes'       => $request->notes,
            ]);

            // On approval, update the student's Promotion record
            if ($request->status === 'approved' && $transfer->transfer_type !== 'inter_school') {
                Promotion::where('student_id', $transfer->student_id)
                    ->where('session_id', $transfer->to_session_id)
                    ->update([
                        'class_id'   => $transfer->to_class_id   ?? DB::raw('class_id'),
                        'section_id' => $transfer->to_section_id ?? DB::raw('section_id'),
                    ]);
            }
        });

        return back()->with('status', 'Transfer ' . $request->status . '.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create students');
        StudentTransfer::findOrFail($id)->delete();
        return back()->with('status', 'Transfer request deleted.');
    }
}
