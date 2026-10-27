<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Interfaces\SchoolSessionInterface;
use App\Services\TeacherImportService;
use App\Traits\SchoolSession;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherImportController extends Controller
{
    use SchoolSession;

    protected SchoolSessionInterface $schoolSessionRepository;
    protected TeacherImportService   $importService;

    public function __construct(
        SchoolSessionInterface $schoolSessionRepository,
        TeacherImportService   $importService
    ) {
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->importService           = $importService;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Allowed roles (same as student import)
    // ─────────────────────────────────────────────────────────────────────────

    private const ALLOWED_ROLES = [
        'super-admin', 'organization-owner', 'admin',
        'principal', 'vice-principal', 'academic-coordinator',
        'hr-manager', 'hr',
    ];

    private function authorizeAccess(): void
    {
        $user = auth()->user();
        if (!$user || !$user->hasAnyRole(self::ALLOWED_ROLES)) {
            abort(403, 'Unauthorized to access bulk teacher import.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Show the bulk import UI
    // ─────────────────────────────────────────────────────────────────────────

    public function index()
    {
        $this->authorizeAccess();

        $currentSessionId = $this->getSchoolCurrentSession();
        $sessions         = $this->schoolSessionRepository->getAll();

        return view('teachers.bulk-import', [
            'current_school_session_id' => $currentSessionId,
            'sessions'                  => $sessions,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Download the CSV template
    // ─────────────────────────────────────────────────────────────────────────

    public function downloadSample(): StreamedResponse
    {
        $csvContent = $this->importService->generateSampleCsv();
        $fileName   = 'teachers_import_template_' . date('Y_m_d') . '.csv';

        return response()->streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, $fileName, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Process CSV file upload
    // ─────────────────────────────────────────────────────────────────────────

    public function importCsv(Request $request)
    {
        $this->authorizeAccess();

        $request->validate([
            'session_id'       => 'nullable|integer',
            'csv_file'         => 'required|file|max:5120',
            'default_password' => 'nullable|string|min:6|max:32',
        ]);

        $sessionId       = (int) ($request->input('session_id') ?: $this->getSchoolCurrentSession());
        $defaultPassword = $request->input('default_password') ?: 'Teacher@123';

        $file = $request->file('csv_file');
        $rows = $this->importService->parseCsvFile($file->getRealPath());

        if (empty($rows)) {
            return back()
                ->withInput()
                ->with('error', 'The uploaded CSV file is empty or formatted incorrectly.');
        }

        $result = $this->importService->importTeachers($rows, $sessionId, [
            'default_password' => $defaultPassword,
        ]);

        return $this->buildRedirect($result);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Process direct multi-row manual entry
    // ─────────────────────────────────────────────────────────────────────────

    public function importDirect(Request $request)
    {
        $this->authorizeAccess();

        $request->validate([
            'session_id'                   => 'nullable|integer',
            'teachers'                     => 'required|array|min:1',
            'teachers.*.first_name'        => 'required|string|max:100',
            'teachers.*.last_name'         => 'required|string|max:100',
            'default_password'             => 'nullable|string|min:6|max:32',
        ]);

        $sessionId       = (int) ($request->input('session_id') ?: $this->getSchoolCurrentSession());
        $defaultPassword = $request->input('default_password') ?: 'Teacher@123';

        $rows = $request->input('teachers', []);
        foreach ($rows as $i => &$row) {
            $row['_row_number'] = $i + 1;
        }
        unset($row);

        $result = $this->importService->importTeachers($rows, $sessionId, [
            'default_password' => $defaultPassword,
        ]);

        return $this->buildRedirect($result);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function buildRedirect(array $result)
    {
        $statusMessage = "Bulk Import Complete: {$result['imported']} teacher(s) successfully registered.";
        if ($result['skipped'] > 0) {
            $statusMessage .= " {$result['skipped']} row(s) were skipped due to validation errors.";
        }

        $redirect = redirect()->route('teachers.index');

        if ($result['imported'] > 0) {
            $redirect->with('status', $statusMessage);
        } else {
            $redirect->with('error', 'No teachers were imported. Please review the errors below.');
        }

        if (!empty($result['errors'])) {
            $redirect->with('import_errors', $result['errors']);
        }

        return $redirect;
    }
}
