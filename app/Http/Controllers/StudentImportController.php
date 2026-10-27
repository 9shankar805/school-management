<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SectionInterface;
use App\Services\StudentImportService;
use App\Traits\SchoolSession;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentImportController extends Controller
{
    use SchoolSession;

    protected SchoolSessionInterface $schoolSessionRepository;
    protected SchoolClassInterface $schoolClassRepository;
    protected SectionInterface $schoolSectionRepository;
    protected StudentImportService $importService;

    public function __construct(
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface $schoolClassRepository,
        SectionInterface $schoolSectionRepository,
        StudentImportService $importService
    ) {
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository   = $schoolClassRepository;
        $this->schoolSectionRepository = $schoolSectionRepository;
        $this->importService           = $importService;
    }

    /**
     * Show the bulk student import interface.
     */
    public function index()
    {
        $authUser = auth()->user();
        if (!$authUser || !$authUser->hasAnyRole(['super-admin', 'organization-owner', 'admin', 'principal', 'vice-principal', 'academic-coordinator', 'admission-officer', 'receptionist'])) {
            abort(403, 'Unauthorized to access bulk student import.');
        }

        $currentSessionId = $this->getSchoolCurrentSession();
        $schoolClasses    = $this->schoolClassRepository->getAllBySession($currentSessionId);
        $sessions         = $this->schoolSessionRepository->getAll();

        return view('students.bulk-import', [
            'current_school_session_id' => $currentSessionId,
            'school_classes'            => $schoolClasses,
            'sessions'                  => $sessions,
        ]);
    }

    /**
     * Download the standard CSV template.
     */
    public function downloadSample(): StreamedResponse
    {
        $csvContent = $this->importService->generateSampleCsv();
        $fileName   = 'students_import_template_' . date('Y_m_d') . '.csv';

        return response()->streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, $fileName, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Process CSV file upload for student bulk import.
     */
    public function importCsv(Request $request)
    {
        $authUser = auth()->user();
        if (!$authUser || !$authUser->hasAnyRole(['super-admin', 'organization-owner', 'admin', 'principal', 'vice-principal', 'academic-coordinator', 'admission-officer', 'receptionist'])) {
            abort(403, 'Unauthorized to import students.');
        }

        $request->validate([
            'class_id'         => 'required|integer',
            'section_id'       => 'required|integer',
            'session_id'       => 'nullable|integer',
            'csv_file'         => 'required|file|max:5120',
            'default_password' => 'nullable|string|min:6|max:32',
        ]);

        $sessionId = $request->input('session_id') ?: $this->getSchoolCurrentSession();
        $classId   = (int) $request->input('class_id');
        $sectionId = (int) $request->input('section_id');
        $defaultPassword = $request->input('default_password') ?: 'Student@123';

        $file = $request->file('csv_file');
        $rows = $this->importService->parseCsvFile($file->getRealPath());

        if (empty($rows)) {
            return back()
                ->withInput()
                ->with('error', 'The uploaded CSV file is empty or formatted incorrectly.');
        }

        $result = $this->importService->importStudents(
            $rows,
            $classId,
            $sectionId,
            $sessionId,
            ['default_password' => $defaultPassword]
        );

        $statusMessage = "Bulk Import Complete: {$result['imported']} student(s) successfully registered.";
        if ($result['skipped'] > 0) {
            $statusMessage .= " {$result['skipped']} row(s) were skipped due to validation errors.";
        }

        $redirect = redirect()->route('students.index', [
            'class_id'   => $classId,
            'section_id' => $sectionId,
        ]);

        if ($result['imported'] > 0) {
            $redirect->with('status', $statusMessage);
        } else {
            $redirect->with('error', 'No students were imported. Please review the errors below.');
        }

        if (!empty($result['errors'])) {
            $redirect->with('import_errors', $result['errors']);
        }

        return $redirect;
    }

    /**
     * Process direct multi-row manual entry.
     */
    public function importDirect(Request $request)
    {
        $authUser = auth()->user();
        if (!$authUser || !$authUser->hasAnyRole(['super-admin', 'organization-owner', 'admin', 'principal', 'vice-principal', 'academic-coordinator', 'admission-officer', 'receptionist'])) {
            abort(403, 'Unauthorized to import students.');
        }

        $request->validate([
            'class_id'         => 'required|integer',
            'section_id'       => 'required|integer',
            'session_id'       => 'nullable|integer',
            'students'         => 'required|array|min:1',
            'students.*.first_name' => 'required|string|max:100',
            'students.*.last_name'  => 'required|string|max:100',
            'default_password' => 'nullable|string|min:6|max:32',
        ]);

        $sessionId = $request->input('session_id') ?: $this->getSchoolCurrentSession();
        $classId   = (int) $request->input('class_id');
        $sectionId = (int) $request->input('section_id');
        $defaultPassword = $request->input('default_password') ?: 'Student@123';

        $rows = $request->input('students', []);
        // Normalize array keys
        foreach ($rows as $i => &$row) {
            $row['_row_number'] = $i + 1;
        }

        $result = $this->importService->importStudents(
            $rows,
            $classId,
            $sectionId,
            $sessionId,
            ['default_password' => $defaultPassword]
        );

        $statusMessage = "Bulk Registration Complete: {$result['imported']} student(s) successfully registered.";
        if ($result['skipped'] > 0) {
            $statusMessage .= " {$result['skipped']} student(s) were skipped.";
        }

        $redirect = redirect()->route('students.index', [
            'class_id'   => $classId,
            'section_id' => $sectionId,
        ]);

        if ($result['imported'] > 0) {
            $redirect->with('status', $statusMessage);
        } else {
            $redirect->with('error', 'No students were imported. Please check errors.');
        }

        if (!empty($result['errors'])) {
            $redirect->with('import_errors', $result['errors']);
        }

        return $redirect;
    }
}
