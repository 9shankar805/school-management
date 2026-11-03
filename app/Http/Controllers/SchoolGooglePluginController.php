<?php

namespace App\Http\Controllers;

use App\Models\GoogleClassroomCourse;
use App\Models\GoogleClassroomRoster;
use App\Models\GoogleSyncLog;
use App\Models\GoogleWorkspaceAccount;
use App\Models\SchoolGoogleConfig;
use App\Services\Google\GoogleClientFactory;
use App\Services\Google\GoogleMigrationService;
use App\Traits\SchoolSession;
use Illuminate\Http\Request;

class SchoolGooglePluginController extends Controller
{
    use SchoolSession;

    public function __construct()
    {
        $this->middleware(['auth', 'role:super-admin|admin|principal|academic-coordinator']);
    }

    /**
     * GET /school/google-workspace
     * Individual School's Google Workspace & Classroom Plugin Box
     */
    public function index()
    {
        $schoolId = config('tenant.school_id') ?? auth()->user()->school_id;

        $config = SchoolGoogleConfig::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->first();

        $classrooms = GoogleClassroomCourse::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->with(['schoolClass', 'section', 'course'])
            ->latest()
            ->paginate(12);

        $stats = [
            'total_classrooms' => GoogleClassroomCourse::withoutGlobalScopes()->where('school_id', $schoolId)->count(),
            'total_students'   => GoogleClassroomRoster::withoutGlobalScopes()->where('school_id', $schoolId)->where('role', 'STUDENT')->count(),
            'total_teachers'   => GoogleClassroomRoster::withoutGlobalScopes()->where('school_id', $schoolId)->where('role', 'TEACHER')->count(),
            'total_accounts'   => GoogleWorkspaceAccount::withoutGlobalScopes()->where('school_id', $schoolId)->count(),
        ];

        $latestLog = GoogleSyncLog::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->latest()
            ->first();

        return view('academics.google-plugin-page', compact('config', 'classrooms', 'stats', 'latestLog'));
    }

    /**
     * POST /school/google-workspace/sync-rosters
     * Re-sync student rosters to Google Classrooms
     */
    public function syncRosters(GoogleMigrationService $migrationService)
    {
        $schoolId = config('tenant.school_id') ?? auth()->user()->school_id;

        try {
            $report = $migrationService->migrateSchool($schoolId, auth()->id());
            return back()->with('status', "Rosters and Classrooms synchronized! {$report['classrooms_created']} classrooms updated, {$report['students_enrolled']} students verified.");
        } catch (\Throwable $e) {
            return back()->with('error', "Roster synchronization error: " . $e->getMessage());
        }
    }

    /**
     * POST /school/google-workspace/test
     */
    public function testConnection()
    {
        $schoolId = config('tenant.school_id') ?? auth()->user()->school_id;

        $config = SchoolGoogleConfig::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->first();

        if (!$config || !$config->hasValidCredentials()) {
            return back()->with('error', "Google Education Workspace has not been configured with valid credentials by the Super Admin yet.");
        }

        $result = GoogleClientFactory::testConnection($config);

        if ($result['success']) {
            return back()->with('status', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * GET /school/google-workspace/classroom/{googleCourseId}/stream
     * Load coursework, assignments & announcements live from Google Workspace into the ERP
     */
    public function stream(string $googleCourseId, GoogleMigrationService $migrationService)
    {
        $schoolId = config('tenant.school_id') ?? auth()->user()->school_id;
        $data = $migrationService->pullClassroomStream($schoolId, $googleCourseId);
        return response()->json($data);
    }

    /**
     * GET /school/google-workspace/classroom/{googleCourseId}/submissions/{courseWorkId}
     * Load student submissions & grades live from Google Workspace into the ERP
     */
    public function submissions(string $googleCourseId, string $courseWorkId, GoogleMigrationService $migrationService)
    {
        $schoolId = config('tenant.school_id') ?? auth()->user()->school_id;
        $submissions = $migrationService->pullSubmissions($schoolId, $googleCourseId, $courseWorkId);
        return response()->json([
            'course_id'     => $googleCourseId,
            'coursework_id' => $courseWorkId,
            'submissions'   => $submissions,
        ]);
    }

    /**
     * POST /school/google-workspace/archive-report-cards
     * Archive & store report cards and marksheets to school's Google Drive folder
     */
    public function archiveReportCards(Request $request, GoogleMigrationService $migrationService)
    {
        $request->validate([
            'class_id'    => 'required|integer',
            'semester_id' => 'required|integer',
        ]);

        $schoolId = config('tenant.school_id') ?? auth()->user()->school_id;

        try {
            $result = $migrationService->archiveReportCardsToDrive($schoolId, (int) $request->class_id, (int) $request->semester_id);
            return back()->with('status', "Report cards archived to Google Drive successfully! Folder created: {$result['folder_name']}");
        } catch (\Throwable $e) {
            return back()->with('error', "Google Drive archive error: " . $e->getMessage());
        }
    }
}

