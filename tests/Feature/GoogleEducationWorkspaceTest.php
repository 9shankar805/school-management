<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\GoogleClassroomCourse;
use App\Models\GoogleClassroomRoster;
use App\Models\GoogleSyncLog;
use App\Models\GoogleWorkspaceAccount;
use App\Models\Promotion;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolGoogleConfig;
use App\Models\SchoolSession;
use App\Models\Section;
use App\Models\Semester;
use App\Models\User;
use App\Services\Google\GoogleMigrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GoogleEducationWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $schoolAdmin;
    protected School $school;
    protected SchoolSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'name'  => 'Mount Everest Secondary School',
            'slug'  => 'mount-everest',
            'email' => 'admin@mteverest.edu.np',
        ]);

        config(['tenant.school_id' => $this->school->id]);

        // Permissions & Roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
        $adminRole      = Role::firstOrCreate(['name' => 'admin']);

        $this->superAdmin = User::factory()->create([
            'email'     => 'superadmin@nepalschoolhub.com',
            'school_id' => null,
            'role'      => 'super-admin',
        ]);
        $this->superAdmin->assignRole('super-admin');

        $this->schoolAdmin = User::factory()->create([
            'email'     => 'admin@mteverest.edu.np',
            'school_id' => $this->school->id,
            'role'      => 'admin',
        ]);
        $this->schoolAdmin->assignRole('admin');

        $this->session = SchoolSession::create([
            'session_name' => '2083-2084',
            'school_id'    => $this->school->id,
        ]);
        session(['current_school_session_id' => $this->session->id]);
    }

    /**
     * Helper to mock a valid Google service account JSON key
     */
    protected function getSampleServiceAccountJson(): string
    {
        return json_encode([
            'type'                        => 'service_account',
            'project_id'                  => 'school-demo-project',
            'private_key_id'              => '1234567890abcdef',
            'private_key'                 => "-----BEGIN RSA PRIVATE KEY-----\nMIIEowIBAAKCAQEA0mockkey...\n-----END RSA PRIVATE KEY-----\n",
            'client_email'                => 'school-sa@school-demo-project.iam.gserviceaccount.com',
            'client_id'                   => '10938210391209381',
            'auth_uri'                    => 'https://accounts.google.com/o/oauth2/auth',
            'token_uri'                   => 'https://oauth2.googleapis.com/token',
            'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
        ]);
    }

    /**
     * Test Super Admin can access the Global Google Workspace Hub
     */
    public function test_super_admin_can_access_google_workspace_hub()
    {
        $response = $this->actingAs($this->superAdmin)->get(route('super.google.index'));

        $response->assertStatus(200);
        $response->assertSee('Google Education Workspace Hub');
        $response->assertSee($this->school->name);
    }

    /**
     * Test Super Admin can configure and save Google Workspace credentials for a school
     */
    public function test_super_admin_can_save_school_google_config()
    {
        $response = $this->actingAs($this->superAdmin)->post(route('super.schools.google.config', $this->school), [
            'workspace_domain'     => 'mteverest.edu.np',
            'admin_email'          => 'principal@mteverest.edu.np',
            'service_account_json' => $this->getSampleServiceAccountJson(),
            'sync_classroom'       => 1,
            'sync_directory_users' => 1,
            'is_active'            => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('school_google_configs', [
            'school_id'        => $this->school->id,
            'workspace_domain' => 'mteverest.edu.np',
            'admin_email'      => 'principal@mteverest.edu.np',
            'is_active'        => 1,
        ]);

        $config = SchoolGoogleConfig::withoutGlobalScopes()->where('school_id', $this->school->id)->first();
        $this->assertTrue($config->hasValidCredentials());
    }

    /**
     * Test Super Admin can run full data migration to Google Education
     */
    public function test_super_admin_can_migrate_all_school_data_to_google()
    {
        // 1. Setup School Google Configuration
        SchoolGoogleConfig::create([
            'school_id'            => $this->school->id,
            'workspace_domain'     => 'mteverest.edu.np',
            'admin_email'          => 'principal@mteverest.edu.np',
            'service_account_json' => $this->getSampleServiceAccountJson(),
            'is_active'            => true,
            'sync_classroom'       => true,
            'sync_directory_users' => true,
        ]);

        // 2. Setup Academic Data: Class, Section, Subject, Teacher, Student
        $class = SchoolClass::create(['class_name' => 'Class 10', 'school_id' => $this->school->id, 'session_id' => $this->session->id]);
        $section = Section::create(['section_name' => 'A', 'class_id' => $class->id, 'school_id' => $this->school->id, 'session_id' => $this->session->id, 'room_no' => '101']);
        
        $semester = Semester::create([
            'semester_name' => 'Terminal 1',
            'school_id'     => $this->school->id,
            'session_id'    => $this->session->id,
            'start_date'    => '2026-04-15',
            'end_date'      => '2026-07-15',
        ]);

        $subject = Course::create([
            'course_name' => 'Mathematics',
            'course_type' => 'Core',
            'class_id'    => $class->id,
            'semester_id' => $semester->id,
            'session_id'  => $this->session->id,
            'school_id'   => $this->school->id,
        ]);

        $student = User::factory()->create([
            'first_name' => 'Rohan',
            'last_name'  => 'Adhikari',
            'school_id'  => $this->school->id,
            'role'       => 'student',
        ]);

        $teacher = User::factory()->create([
            'first_name' => 'Bikram',
            'last_name'  => 'Thapa',
            'school_id'  => $this->school->id,
            'role'       => 'teacher',
        ]);

        // Setup Promotion with id_card_number
        Promotion::create([
            'student_id'     => $student->id,
            'class_id'       => $class->id,
            'section_id'     => $section->id,
            'session_id'     => $this->session->id,
            'roll_no'        => 1,
            'id_card_number' => 'STU-1001',
            'school_id'      => $this->school->id,
        ]);

        // 3. Trigger Full Migration via Route
        $response = $this->actingAs($this->superAdmin)->post(route('super.schools.google.migrate', $this->school));

        $response->assertRedirect();
        $response->assertSessionHas('status');

        // 4. Verify Google Workspace Accounts provisioned locally
        $this->assertTrue(GoogleWorkspaceAccount::where('school_id', $this->school->id)->where('user_id', $student->id)->exists());
        $this->assertTrue(GoogleWorkspaceAccount::where('school_id', $this->school->id)->where('user_id', $teacher->id)->exists());
        $this->assertNotEmpty($student->fresh()->google_workspace_email);
        $this->assertNotEmpty($teacher->fresh()->google_workspace_email);
        $this->assertEquals('rohan.adhikari@mteverest.edu.np', $student->fresh()->google_workspace_email);
        $this->assertEquals('bikram.thapa@mteverest.edu.np', $teacher->fresh()->google_workspace_email);

        // 5. Verify Google Classroom Courses created
        $this->assertTrue(GoogleClassroomCourse::where('school_id', $this->school->id)->where('class_id', $class->id)->where('course_id', $subject->id)->exists());

        // 6. Verify Rosters enrolled
        $this->assertTrue(GoogleClassroomRoster::where('school_id', $this->school->id)->where('user_id', $student->id)->where('role', 'STUDENT')->exists());

        // 7. Verify Audit Log recorded
        $this->assertTrue(GoogleSyncLog::where('school_id', $this->school->id)->where('sync_type', 'full_migration')->exists());
    }

    /**
     * Test individual School Admin can access their Google Education Workspace plugin page
     */
    public function test_school_admin_can_access_google_plugin_box()
    {
        SchoolGoogleConfig::create([
            'school_id'            => $this->school->id,
            'workspace_domain'     => 'mteverest.edu.np',
            'admin_email'          => 'principal@mteverest.edu.np',
            'service_account_json' => $this->getSampleServiceAccountJson(),
            'is_active'            => true,
            'connection_status'    => 'connected',
        ]);

        $response = $this->actingAs($this->schoolAdmin)->get(route('school.google.plugin'));

        $response->assertStatus(200);
        $response->assertSee('Google Education Plugin Hub');
        $response->assertSee('mteverest.edu.np');
    }

    /**
     * Test ERP loads data (coursework & announcements) live from Google Workspace
     */
    public function test_school_admin_can_load_classroom_stream_from_google_workspace()
    {
        SchoolGoogleConfig::create([
            'school_id'            => $this->school->id,
            'workspace_domain'     => 'mteverest.edu.np',
            'admin_email'          => 'principal@mteverest.edu.np',
            'service_account_json' => $this->getSampleServiceAccountJson(),
            'is_active'            => true,
            'connection_status'    => 'connected',
        ]);

        $response = $this->actingAs($this->schoolAdmin)->get(route('school.google.classroom.stream', [
            'googleCourseId' => 'gc_demo_101',
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'course_id',
            'coursework',
            'announcements',
            'source',
        ]);
    }
}
