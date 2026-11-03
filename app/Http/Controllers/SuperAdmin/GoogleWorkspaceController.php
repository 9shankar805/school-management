<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\GoogleClassroomCourse;
use App\Models\GoogleWorkspaceAccount;
use App\Models\School;
use App\Models\SchoolGoogleConfig;
use App\Services\Google\GoogleClientFactory;
use App\Services\Google\GoogleMigrationService;
use Illuminate\Http\Request;

class GoogleWorkspaceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super-admin']);
    }

    /**
     * GET /super/google-workspace
     * Super Admin Global Google Workspace Management Hub
     */
    public function index(Request $request)
    {
        $query = School::withTrashed()->with('googleConfig');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $schools = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total_schools'     => School::count(),
            'connected_schools' => SchoolGoogleConfig::where('connection_status', 'connected')->count(),
            'total_classrooms'  => GoogleClassroomCourse::count(),
            'total_accounts'    => GoogleWorkspaceAccount::count(),
        ];

        return view('saas.super.google.index', compact('schools', 'stats'));
    }

    /**
     * GET /super/schools/{school}/google
     * Manage Google Education settings & migration for a specific school
     */
    public function show(School $school)
    {
        $config = SchoolGoogleConfig::withoutGlobalScopes()
            ->firstOrNew(['school_id' => $school->id], [
                'workspace_domain'           => strtolower($school->slug ?: 'school') . '.edu.np',
                'admin_email'                => $school->email,
                'is_active'                  => true,
                'sync_classroom'             => true,
                'sync_directory_users'       => true,
                'sync_meet_attendance'       => true,
                'auto_create_student_emails' => false,
                'connection_status'          => 'untested',
            ]);

        $courses = GoogleClassroomCourse::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->with(['schoolClass', 'section', 'course'])
            ->latest()
            ->paginate(15);

        $logs = \App\Models\GoogleSyncLog::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->latest()
            ->take(10)
            ->get();

        $activeTab = 'google';
        return view('saas.super.schools.google-tab', compact('school', 'config', 'courses', 'logs', 'activeTab'));
    }

    /**
     * POST /super/schools/{school}/google/config
     * Save/Update Google Workspace Configuration & Credentials
     */
    public function saveConfig(Request $request, School $school)
    {
        $request->validate([
            'workspace_domain'     => 'required|string|max:255',
            'admin_email'          => 'required|email|max:255',
            'credentials_file'     => 'nullable|file|mimes:json,txt|max:2048',
            'service_account_json' => 'nullable|string',
        ]);

        $config = SchoolGoogleConfig::withoutGlobalScopes()
            ->firstOrNew(['school_id' => $school->id]);

        $jsonContent = null;
        if ($request->hasFile('credentials_file')) {
            $jsonContent = file_get_contents($request->file('credentials_file')->getRealPath());
        } elseif ($request->filled('service_account_json')) {
            $jsonContent = $request->service_account_json;
        }

        if ($jsonContent) {
            $decoded = json_decode($jsonContent, true);
            if (!is_array($decoded) || empty($decoded['client_email']) || empty($decoded['private_key'])) {
                return back()->with('error', 'Uploaded credentials file is not a valid Google Service Account JSON key.');
            }
            $config->service_account_json = $jsonContent;
            $config->client_id = $decoded['client_id'] ?? null;
            $config->connection_status = 'untested';
        }

        $config->workspace_domain           = strtolower(trim($request->workspace_domain));
        $config->admin_email                = strtolower(trim($request->admin_email));
        $config->is_active                  = $request->boolean('is_active', true);
        $config->sync_classroom             = $request->boolean('sync_classroom', true);
        $config->sync_directory_users       = $request->boolean('sync_directory_users', true);
        $config->sync_meet_attendance       = $request->boolean('sync_meet_attendance', true);
        $config->auto_create_student_emails = $request->boolean('auto_create_student_emails', false);
        $config->save();

        return back()->with('status', "Google Workspace configuration for {$school->name} saved successfully!");
    }

    /**
     * POST /super/schools/{school}/google/test
     * Test API connection with Google Workspace
     */
    public function testConnection(School $school)
    {
        $config = SchoolGoogleConfig::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->first();

        if (!$config || !$config->hasValidCredentials()) {
            return back()->with('error', "Please upload a valid Service Account JSON key first.");
        }

        $result = GoogleClientFactory::testConnection($config);

        if ($result['success']) {
            return back()->with('status', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * POST /super/schools/{school}/google/migrate
     * Migrate all data (accounts, classrooms, rosters) to Google Education
     * Standard POST fallback (used if JS/SSE is unavailable).
     */
    public function migrate(School $school, GoogleMigrationService $migrationService)
    {
        try {
            $report = $migrationService->migrateSchool($school->id, auth()->id());

            $msg = "Migration completed successfully! ";
            $msg .= "Provisioned {$report['accounts_migrated']} accounts, created {$report['classrooms_created']} Google Classrooms, and enrolled {$report['students_enrolled']} students.";

            if (!empty($report['errors'])) {
                $msg .= " (" . count($report['errors']) . " warning(s) logged).";
            }

            return back()->with('status', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', "Migration failed: " . $e->getMessage());
        }
    }

    /**
     * GET /super/schools/{school}/google/migrate-stream
     * Server-Sent Events stream — emits progress percentage as migration runs.
     * The browser connects via EventSource; each event carries JSON:
     *   { "pct": 0-100, "step": "label", "detail": "one-liner" }
     * A final event with pct=100 and type="done" signals completion.
     */
    public function migrateStream(School $school, GoogleMigrationService $migrationService, \Illuminate\Http\Request $request)
    {
        // Verify CSRF token passed as query param (EventSource can't set headers)
        abort_unless(
            $request->hasValidSignature() || hash_equals(
                (string) session()->token(),
                (string) $request->query('_token', '')
            ),
            403
        );

        $userId = auth()->id();

        return response()->stream(function () use ($school, $migrationService, $userId) {

            $send = function (array $payload, string $type = 'progress') {
                echo "event: {$type}\n";
                echo 'data: ' . json_encode($payload) . "\n\n";
                if (ob_get_level() > 0) ob_flush();
                flush();
            };

            // Keep-alive header ping so the browser doesn't time out immediately
            $send(['pct' => 0, 'step' => 'Starting', 'detail' => 'Migration starting…']);

            try {
                $migrationService->migrateWithProgress(
                    $school->id,
                    function (int $pct, string $step, string $detail) use ($send) {
                        $send(['pct' => $pct, 'step' => $step, 'detail' => $detail]);
                    },
                    $userId
                );

                $send(['pct' => 100, 'step' => 'Done', 'detail' => 'Migration completed successfully.'], 'done');
            } catch (\Throwable $e) {
                $send(['pct' => 0, 'step' => 'Error', 'detail' => $e->getMessage()], 'error');
            }

        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',   // disable Nginx buffering
            'Connection'        => 'keep-alive',
        ]);
    }
}

