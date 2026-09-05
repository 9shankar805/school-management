<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Traits\SchoolSession as SchoolSessionTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * IemisExportController
 *
 * Generates CEHRD / Nepal Ministry of Education IEMIS-compatible data exports.
 *
 * Supported formats:
 *   GET  /iemis/export          — show export form
 *   POST /iemis/export/excel    — download Excel (.xlsx)
 *   POST /iemis/export/csv      — download CSV
 *   POST /iemis/export/pdf      — download PDF (A4 landscape)
 *
 * Fields match Nepal's EMIS Flash Report format (Section A — Student Enrolment).
 */
class IemisExportController extends Controller
{
    use SchoolSessionTrait;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|principal|super-admin');
    }

    // ── Export form ───────────────────────────────────────────────────────────

    public function index()
    {
        $sessions = SchoolSession::orderByDesc('id')->take(5)->get();
        $currentSessionId = $this->getSchoolCurrentSession();

        $classes = SchoolClass::where('session_id', $currentSessionId)
            ->orderBy('class_name')
            ->get();

        return view('iemis.export', compact('sessions', 'classes', 'currentSessionId'));
    }

    // ── Core data query ───────────────────────────────────────────────────────

    private function getStudentData(int $sessionId, ?int $classId = null): \Illuminate\Support\Collection
    {
        $query = User::role('student')
            ->with([
                'promotions' => fn($q) => $q->where('session_id', $sessionId)->with('schoolClass', 'section'),
                'studentParentInfo',
                'studentAcademicInfo',
            ])
            ->whereHas('promotions', fn($q) => $q->where('session_id', $sessionId));

        if ($classId) {
            $query->whereHas('promotions', fn($q) => $q->where('session_id', $sessionId)->where('class_id', $classId));
        }

        return $query->get()->map(function (User $student) use ($sessionId) {
            $promotion  = $student->promotions->firstWhere('session_id', $sessionId);
            $parentInfo = $student->studentParentInfo;
            $acInfo     = $student->studentAcademicInfo;

            return [
                'SN'                    => '',  // filled during render
                'Student ID'            => $student->id,
                'Admission No'          => $student->admission_no ?? '',
                'First Name'            => $student->first_name ?? '',
                'Last Name'             => $student->last_name  ?? '',
                'Full Name (Nepali)'    => $student->name_np    ?? '',
                'Date of Birth (AD)'    => $student->birthday?->format('Y-m-d') ?? '',
                'Date of Birth (BS)'    => $student->birthday
                    ? (\App\Services\NepaliDateService::adToBS($student->birthday->format('Y-m-d')) ?? '')
                    : '',
                'Gender'                => ucfirst($student->gender ?? ''),
                'Ethnicity / Caste'     => $student->ethnicity   ?? '',
                'Religion'              => $student->religion     ?? '',
                'Mother Tongue'         => $student->mother_tongue ?? '',
                'Nationality'           => $student->nationality  ?? 'Nepali',
                'Differently Abled'     => $student->is_disabled  ? 'Yes' : 'No',
                'Disability Type'       => $student->disability_type ?? '',
                'Province'              => $student->province     ?? '',
                'District'              => $student->district     ?? '',
                'Municipality / VDC'    => $student->municipality ?? '',
                'Ward No'               => $student->ward_no      ?? '',
                'Permanent Address'     => $student->permanent_address ?? '',
                'Phone'                 => $student->phone        ?? '',
                'Email'                 => $student->email        ?? '',
                'Class / Grade'         => $promotion?->schoolClass?->class_name ?? '',
                'Section'               => $promotion?->section?->section_name   ?? '',
                'Roll No'               => $promotion->roll_no ?? '',
                "Father's Name"         => $parentInfo?->father_name  ?? '',
                "Father's Occupation"   => $parentInfo?->father_occupation ?? '',
                "Father's Phone"        => $parentInfo?->father_phone ?? '',
                "Mother's Name"         => $parentInfo?->mother_name  ?? '',
                "Mother's Occupation"   => $parentInfo?->mother_occupation ?? '',
                "Mother's Phone"        => $parentInfo?->mother_phone ?? '',
                'Guardian Name'         => $parentInfo?->guardian_name ?? '',
                'Guardian Phone'        => $parentInfo?->guardian_phone ?? '',
                'Board Reg No'          => $acInfo?->board_reg_no ?? '',
                'Previous School'       => $acInfo?->previous_school ?? '',
                'Admission Date (AD)'   => $student->admission_date?->format('Y-m-d') ?? '',
                'Scholarship'           => $student->scholarships()->where('status','active')->exists() ? 'Yes' : 'No',
            ];
        });
    }

    // ── Excel export ──────────────────────────────────────────────────────────

    public function exportExcel(Request $request)
    {
        $this->authorize('view students');

        $sessionId = (int) $request->input('session_id', $this->getSchoolCurrentSession());
        $classId   = $request->input('class_id') ? (int) $request->input('class_id') : null;

        $rows     = $this->getStudentData($sessionId, $classId);
        $session  = SchoolSession::find($sessionId);
        $filename = 'IEMIS_Students_' . ($session?->session_name ?? $sessionId) . '_' . now()->format('Ymd') . '.xlsx';

        return Excel::download(
            new \App\Exports\IemisStudentExport($rows, $session),
            $filename
        );
    }

    // ── CSV export ────────────────────────────────────────────────────────────

    public function exportCsv(Request $request)
    {
        $this->authorize('view students');

        $sessionId = (int) $request->input('session_id', $this->getSchoolCurrentSession());
        $classId   = $request->input('class_id') ? (int) $request->input('class_id') : null;

        $rows    = $this->getStudentData($sessionId, $classId);
        $session = SchoolSession::find($sessionId);
        $filename = 'IEMIS_Students_' . ($session?->session_name ?? $sessionId) . '_' . now()->format('Ymd') . '.csv';

        $headers = array_keys($rows->first() ?? []);

        $callback = function () use ($rows, $headers) {
            $handle = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);
            foreach ($rows as $i => $row) {
                $row['SN'] = $i + 1;
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // ── PDF export ────────────────────────────────────────────────────────────

    public function exportPdf(Request $request)
    {
        $this->authorize('view students');

        $sessionId = (int) $request->input('session_id', $this->getSchoolCurrentSession());
        $classId   = $request->input('class_id') ? (int) $request->input('class_id') : null;

        $rows    = $this->getStudentData($sessionId, $classId);
        $session = SchoolSession::find($sessionId);
        $school  = auth()->user()->school;

        // Renumber SN
        $rows = $rows->values()->map(function ($row, $i) {
            $row['SN'] = $i + 1;
            return $row;
        });

        $pdf = Pdf::loadView('iemis.export-pdf', compact('rows', 'session', 'school'))
            ->setPaper('a4', 'landscape');

        $filename = 'IEMIS_Students_' . ($session?->session_name ?? $sessionId) . '_' . now()->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }
}
