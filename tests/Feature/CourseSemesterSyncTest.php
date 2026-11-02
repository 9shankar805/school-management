<?php

namespace Tests\Feature;

use App\Models\AssignedTeacher;
use App\Models\Course;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\Section;
use App\Models\Semester;
use App\Models\User;
use App\Repositories\CourseRepository;
use App\Repositories\SemesterRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CourseSemesterSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected School $school;
    protected SchoolSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'name'  => 'Saraswati Academy',
            'slug'  => 'saraswati-academy',
            'email' => 'admin@saraswati.edu.np',
        ]);

        config(['tenant.school_id' => $this->school->id]);

        $permission = Permission::firstOrCreate(['name' => 'create courses']);
        $permClasses = Permission::firstOrCreate(['name' => 'create classes']);
        $permAssign = Permission::firstOrCreate(['name' => 'assign teachers']);
        $adminRole  = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo([$permission, $permClasses, $permAssign]);

        $this->admin = User::factory()->create([
            'school_id' => $this->school->id,
            'role'      => 'admin',
        ]);
        $this->admin->assignRole('admin');

        $this->session = SchoolSession::create([
            'session_name' => '2083-2084',
            'school_id'    => $this->school->id,
        ]);
        session(['current_school_session_id' => $this->session->id]);
    }

    protected function createSemester(string $name, string $startDate = '2026-04-15', string $endDate = '2026-07-15'): Semester
    {
        return Semester::create([
            'school_id'     => $this->school->id,
            'session_id'    => $this->session->id,
            'semester_name' => $name,
            'start_date'    => $startDate,
            'end_date'      => $endDate,
        ]);
    }

    /**
     * Test creating a course with all_terms=1 automatically creates records for all semesters in the session.
     */
    public function test_creating_course_with_all_terms_creates_for_all_existing_semesters()
    {
        $sem1 = $this->createSemester('First Terminal', '2026-04-15', '2026-07-15');
        $sem2 = $this->createSemester('Second Terminal', '2026-07-16', '2026-11-15');
        $sem3 = $this->createSemester('Final Terminal', '2026-11-16', '2027-03-15');

        $class = SchoolClass::create(['class_name' => 'Class 10', 'session_id' => $this->session->id, 'school_id' => $this->school->id]);

        $response = $this->actingAs($this->admin)->post(route('school.course.create'), [
            'course_name' => 'Compulsory Mathematics',
            'course_type' => 'Core',
            'class_ids'   => [$class->id],
            'all_terms'   => '1',
            'session_id'  => $this->session->id,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // Should have created 3 course rows, one per semester
        $this->assertEquals(3, Course::where('course_name', 'Compulsory Mathematics')->where('class_id', $class->id)->count());
        $this->assertTrue(Course::where('course_name', 'Compulsory Mathematics')->where('semester_id', $sem1->id)->exists());
        $this->assertTrue(Course::where('course_name', 'Compulsory Mathematics')->where('semester_id', $sem2->id)->exists());
        $this->assertTrue(Course::where('course_name', 'Compulsory Mathematics')->where('semester_id', $sem3->id)->exists());
    }

    /**
     * Test creating a new semester auto-replicates existing session subjects to the new semester.
     */
    public function test_creating_new_semester_auto_replicates_existing_session_courses()
    {
        $sem1 = $this->createSemester('First Terminal');
        $class = SchoolClass::create(['class_name' => 'Class 9', 'session_id' => $this->session->id, 'school_id' => $this->school->id]);

        Course::create([
            'course_name' => 'English',
            'course_type' => 'Core',
            'class_id'    => $class->id,
            'semester_id' => $sem1->id,
            'session_id'  => $this->session->id,
            'school_id'   => $this->school->id,
        ]);

        Course::create([
            'course_name' => 'Science',
            'course_type' => 'Core',
            'class_id'    => $class->id,
            'semester_id' => $sem1->id,
            'session_id'  => $this->session->id,
            'school_id'   => $this->school->id,
        ]);

        // Now create a new semester via SemesterRepository
        $repo = new SemesterRepository();
        $sem2 = $repo->create([
            'semester_name' => 'Second Terminal',
            'session_id'    => $this->session->id,
            'school_id'     => $this->school->id,
            'start_date'    => '2026-07-16',
            'end_date'      => '2026-11-15',
        ]);

        // The new semester must now automatically possess both English and Science!
        $this->assertTrue(Course::where('course_name', 'English')->where('class_id', $class->id)->where('semester_id', $sem2->id)->exists());
        $this->assertTrue(Course::where('course_name', 'Science')->where('class_id', $class->id)->where('semester_id', $sem2->id)->exists());
    }

    /**
     * Test getMatrixData auto-reconciles missing courses when switching to another semester.
     */
    public function test_get_matrix_data_auto_reconciles_missing_courses_for_semester()
    {
        $sem1 = $this->createSemester('Term 1');
        $sem2 = $this->createSemester('Term 2', '2026-07-16', '2026-11-15');

        $class = SchoolClass::create(['class_name' => 'Class 8', 'session_id' => $this->session->id, 'school_id' => $this->school->id]);
        Section::create(['section_name' => 'A', 'class_id' => $class->id, 'session_id' => $this->session->id, 'school_id' => $this->school->id, 'room_no' => '101']);

        // Course defined only in Term 1
        Course::create([
            'course_name' => 'Social Studies',
            'course_type' => 'General',
            'class_id'    => $class->id,
            'semester_id' => $sem1->id,
            'session_id'  => $this->session->id,
            'school_id'   => $this->school->id,
        ]);

        $this->assertFalse(Course::where('course_name', 'Social Studies')->where('semester_id', $sem2->id)->exists());

        // Admin opens Matrix for Term 2
        $response = $this->actingAs($this->admin)->getJson(route('school.teacher.matrix.data', [
            'class_id'    => $class->id,
            'semester_id' => $sem2->id,
        ]));

        $response->assertStatus(200);
        $data = $response->json();

        // Must contain Social Studies
        $this->assertCount(1, $data['courses']);
        $this->assertEquals('Social Studies', $data['courses'][0]['course_name']);

        // And it should have created the course row for Term 2 in the database
        $this->assertTrue(Course::where('course_name', 'Social Studies')->where('semester_id', $sem2->id)->exists());
    }

    /**
     * Test SectionController getByClassId auto-reconciles courses for semester dropdowns.
     */
    public function test_section_get_by_class_id_auto_reconciles_and_returns_courses()
    {
        $sem1 = $this->createSemester('Term 1');
        $sem2 = $this->createSemester('Term 2', '2026-07-16', '2026-11-15');

        $class = SchoolClass::create(['class_name' => 'Class 7', 'session_id' => $this->session->id, 'school_id' => $this->school->id]);
        Section::create(['section_name' => 'A', 'class_id' => $class->id, 'session_id' => $this->session->id, 'school_id' => $this->school->id, 'room_no' => '102']);

        Course::create([
            'course_name' => 'Nepali',
            'course_type' => 'Core',
            'class_id'    => $class->id,
            'semester_id' => $sem1->id,
            'session_id'  => $this->session->id,
            'school_id'   => $this->school->id,
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('get.sections.courses.by.classId', [
            'class_id'    => $class->id,
            'semester_id' => $sem2->id,
        ]));

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertNotEmpty($data['courses']);
        $this->assertEquals('Nepali', $data['courses'][0]['course_name']);
        $this->assertTrue(Course::where('course_name', 'Nepali')->where('semester_id', $sem2->id)->exists());
    }

    /**
     * Test synchronizing all courses across all semesters via the dedicated POST route.
     */
    public function test_sync_all_courses_to_all_semesters_route()
    {
        $sem1 = $this->createSemester('First Term');
        $sem2 = $this->createSemester('Second Term', '2026-07-16', '2026-11-15');
        $sem3 = $this->createSemester('Third Term', '2026-11-16', '2027-03-15');

        $class = SchoolClass::create(['class_name' => 'Class 6', 'session_id' => $this->session->id, 'school_id' => $this->school->id]);

        // Created only in Sem 1
        Course::create([
            'course_name' => 'Health & Physical Education',
            'course_type' => 'General',
            'class_id'    => $class->id,
            'semester_id' => $sem1->id,
            'session_id'  => $this->session->id,
            'school_id'   => $this->school->id,
        ]);

        $this->assertEquals(1, Course::where('course_name', 'Health & Physical Education')->count());

        $response = $this->actingAs($this->admin)->post(route('school.course.sync.semesters'), [
            'session_id' => $this->session->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        // Should now exist across all 3 terms!
        $this->assertEquals(3, Course::where('course_name', 'Health & Physical Education')->count());
        $this->assertTrue(Course::where('course_name', 'Health & Physical Education')->where('semester_id', $sem2->id)->exists());
        $this->assertTrue(Course::where('course_name', 'Health & Physical Education')->where('semester_id', $sem3->id)->exists());
    }

    /**
     * Test updating a course name/type updates across all semester records for that subject.
     */
    public function test_updating_course_updates_across_all_semesters()
    {
        $sem1 = $this->createSemester('Term 1');
        $sem2 = $this->createSemester('Term 2', '2026-07-16', '2026-11-15');

        $class = SchoolClass::create(['class_name' => 'Class 5', 'session_id' => $this->session->id, 'school_id' => $this->school->id]);

        $c1 = Course::create([
            'course_name' => 'Computer',
            'course_type' => 'General',
            'class_id'    => $class->id,
            'semester_id' => $sem1->id,
            'session_id'  => $this->session->id,
            'school_id'   => $this->school->id,
        ]);

        $c2 = Course::create([
            'course_name' => 'Computer',
            'course_type' => 'General',
            'class_id'    => $class->id,
            'semester_id' => $sem2->id,
            'session_id'  => $this->session->id,
            'school_id'   => $this->school->id,
        ]);

        $repo = new CourseRepository();
        $request = new \Illuminate\Http\Request([
            'course_id'   => $c1->id,
            'course_name' => 'Computer Science',
            'course_type' => 'Elective',
        ]);

        $repo->update($request);

        $this->assertEquals('Computer Science', $c1->fresh()->course_name);
        $this->assertEquals('Elective', $c1->fresh()->course_type);

        // Sem 2 record should also be updated
        $this->assertEquals('Computer Science', $c2->fresh()->course_name);
        $this->assertEquals('Elective', $c2->fresh()->course_type);
    }

    /**
     * Test cloning teacher assignments across semesters maps to destination semester course IDs.
     */
    public function test_clone_semester_teacher_assignments_maps_to_destination_semester_course_ids()
    {
        $sem1 = $this->createSemester('Term 1');
        $sem2 = $this->createSemester('Term 2', '2026-07-16', '2026-11-15');

        $class = SchoolClass::create(['class_name' => 'Class 4', 'session_id' => $this->session->id, 'school_id' => $this->school->id]);
        $section = Section::create(['section_name' => 'A', 'class_id' => $class->id, 'session_id' => $this->session->id, 'school_id' => $this->school->id, 'room_no' => '103']);

        $c1 = Course::create([
            'course_name' => 'Art',
            'course_type' => 'General',
            'class_id'    => $class->id,
            'semester_id' => $sem1->id,
            'session_id'  => $this->session->id,
            'school_id'   => $this->school->id,
        ]);

        $teacher = User::factory()->create([
            'school_id' => $this->school->id,
            'role'      => 'teacher',
        ]);

        AssignedTeacher::create([
            'school_id'   => $this->school->id,
            'teacher_id'  => $teacher->id,
            'semester_id' => $sem1->id,
            'class_id'    => $class->id,
            'section_id'  => $section->id,
            'course_id'   => $c1->id,
            'session_id'  => $this->session->id,
        ]);

        $response = $this->actingAs($this->admin)->post(route('school.teacher.clone.assignments'), [
            'from_semester_id' => $sem1->id,
            'to_semester_id'   => $sem2->id,
        ]);

        $response->assertRedirect();

        // Assignment in Term 2 must exist and point to the Term 2 course row!
        $destAssignment = AssignedTeacher::where('semester_id', $sem2->id)->where('class_id', $class->id)->first();
        $this->assertNotNull($destAssignment);
        $this->assertEquals($teacher->id, $destAssignment->teacher_id);

        $destCourse = Course::find($destAssignment->course_id);
        $this->assertNotNull($destCourse);
        $this->assertEquals('Art', $destCourse->course_name);
        $this->assertEquals($sem2->id, $destCourse->semester_id);
    }
}
