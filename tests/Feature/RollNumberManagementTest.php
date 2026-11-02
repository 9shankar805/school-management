<?php

namespace Tests\Feature;

use App\Models\AcademicSetting;
use App\Models\Course;
use App\Models\FinalMark;
use App\Models\Promotion;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Setting;
use App\Models\TerminalRollNumber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RollNumberManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected School $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'name'  => 'Prithvi Secondary School',
            'slug'  => 'prithvi-school',
            'email' => 'admin@prithvi.edu.np',
        ]);

        config(['tenant.school_id' => $this->school->id]);

        $permission = Permission::firstOrCreate(['name' => 'edit users']);
        $adminRole  = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo($permission);

        $this->admin = User::factory()->create([
            'school_id' => $this->school->id,
            'role'      => 'admin',
        ]);
        $this->admin->assignRole('admin');
    }

    /**
     * Test updating school setting roll number assignment mode.
     */
    public function test_admin_can_update_roll_number_mode()
    {
        // 1. Update to Terminal / Semester-wise
        $response = $this->actingAs($this->admin)->post(route('school.roll.number.mode.update'), [
            'roll_number_mode' => 'terminal',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertEquals('terminal', Setting::get('roll_number_mode'));
        $academicSetting = AcademicSetting::withoutGlobalScopes()->first();
        if ($academicSetting) {
            $this->assertEquals('terminal', $academicSetting->roll_number_mode);
        }

        // 2. Update to Yearly
        $response = $this->actingAs($this->admin)->post(route('school.roll.number.mode.update'), [
            'roll_number_mode' => 'yearly',
        ]);

        $response->assertRedirect();
        $this->assertEquals('yearly', Setting::get('roll_number_mode'));
    }

    /**
     * Test Roll Number Reassignment for Terminal 3 based on Terminal 2 results.
     */
    public function test_reassign_roll_numbers_for_terminal_3_based_on_terminal_2_results()
    {
        // 1. Create Session e.g. 2083-2084
        $session = SchoolSession::create([
            'school_id'    => $this->school->id,
            'session_name' => '2083-2084',
        ]);

        // 2. Create Semesters: Terminal 1, Terminal 2 (Source), Terminal 3 (Target)
        $terminal1 = Semester::create([
            'school_id'     => $this->school->id,
            'session_id'    => $session->id,
            'semester_name' => 'Terminal 1',
            'start_date'    => '2026-04-15',
            'end_date'      => '2026-07-15',
        ]);

        $terminal2 = Semester::create([
            'school_id'     => $this->school->id,
            'session_id'    => $session->id,
            'semester_name' => 'Terminal 2',
            'start_date'    => '2026-07-16',
            'end_date'      => '2026-11-15',
        ]);

        $terminal3 = Semester::create([
            'school_id'     => $this->school->id,
            'session_id'    => $session->id,
            'semester_name' => 'Terminal 3',
            'start_date'    => '2026-11-16',
            'end_date'      => '2027-03-15',
        ]);

        // 3. Create Class and Section
        $class = SchoolClass::create([
            'school_id'   => $this->school->id,
            'session_id'  => $session->id,
            'class_name'  => 'Grade 10',
        ]);

        $section = Section::create([
            'school_id'    => $this->school->id,
            'session_id'   => $session->id,
            'class_id'     => $class->id,
            'section_name' => 'A',
            'room_no'      => '101',
        ]);

        $course = Course::create([
            'school_id'   => $this->school->id,
            'session_id'  => $session->id,
            'semester_id' => $terminal2->id,
            'class_id'    => $class->id,
            'course_name' => 'Mathematics',
            'course_type' => 'Core',
        ]);

        // 4. Create Students with initial roll numbers
        // Student A: initially Roll 15, but scores 95.0 in Terminal 2 (Highest -> Rank 1 -> Should become Roll 1)
        $studentA = User::factory()->create([
            'school_id'  => $this->school->id,
            'first_name' => 'Aarav',
            'last_name'  => 'Sharma',
            'roll_no'    => '15',
        ]);
        $promoA = Promotion::create([
            'school_id'      => $this->school->id,
            'session_id'     => $session->id,
            'class_id'       => $class->id,
            'section_id'     => $section->id,
            'student_id'     => $studentA->id,
            'roll_no'        => '15',
            'id_card_number' => 'ID-001',
        ]);

        // Student B: initially Roll 1, but scores 85.0 in Terminal 2 (Rank 2 -> Should become Roll 2)
        $studentB = User::factory()->create([
            'school_id'  => $this->school->id,
            'first_name' => 'Binod',
            'last_name'  => 'Thapa',
            'roll_no'    => '1',
        ]);
        $promoB = Promotion::create([
            'school_id'      => $this->school->id,
            'session_id'     => $session->id,
            'class_id'       => $class->id,
            'section_id'     => $section->id,
            'student_id'     => $studentB->id,
            'roll_no'        => '1',
            'id_card_number' => 'ID-002',
        ]);

        // Student C: initially Roll 8, scores 75.0 in Terminal 2 (Rank 3 -> Should become Roll 3)
        $studentC = User::factory()->create([
            'school_id'  => $this->school->id,
            'first_name' => 'Chirag',
            'last_name'  => 'KC',
            'roll_no'    => '8',
        ]);
        $promoC = Promotion::create([
            'school_id'      => $this->school->id,
            'session_id'     => $session->id,
            'class_id'       => $class->id,
            'section_id'     => $section->id,
            'student_id'     => $studentC->id,
            'roll_no'        => '8',
            'id_card_number' => 'ID-003',
        ]);

        // Student D: No exam in Terminal 2 (Unranked -> Should be placed at end as Roll 4)
        $studentD = User::factory()->create([
            'school_id'  => $this->school->id,
            'first_name' => 'Dipendra',
            'last_name'  => 'Adhikari',
            'roll_no'    => '20',
        ]);
        $promoD = Promotion::create([
            'school_id'      => $this->school->id,
            'session_id'     => $session->id,
            'class_id'       => $class->id,
            'section_id'     => $section->id,
            'student_id'     => $studentD->id,
            'roll_no'        => '20',
            'id_card_number' => 'ID-004',
        ]);

        // Add FinalMark records for Terminal 2
        FinalMark::create([
            'school_id'        => $this->school->id,
            'session_id'       => $session->id,
            'semester_id'      => $terminal2->id,
            'class_id'         => $class->id,
            'section_id'       => $section->id,
            'course_id'        => $course->id,
            'student_id'       => $studentA->id,
            'final_marks'      => 95.0,
            'calculated_marks' => 95.0,
            'full_marks'       => 100.0,
        ]);

        FinalMark::create([
            'school_id'        => $this->school->id,
            'session_id'       => $session->id,
            'semester_id'      => $terminal2->id,
            'class_id'         => $class->id,
            'section_id'       => $section->id,
            'course_id'        => $course->id,
            'student_id'       => $studentB->id,
            'final_marks'      => 85.0,
            'calculated_marks' => 85.0,
            'full_marks'       => 100.0,
        ]);

        FinalMark::create([
            'school_id'        => $this->school->id,
            'session_id'       => $session->id,
            'semester_id'      => $terminal2->id,
            'class_id'         => $class->id,
            'section_id'       => $section->id,
            'course_id'        => $course->id,
            'student_id'       => $studentC->id,
            'final_marks'      => 75.0,
            'calculated_marks' => 75.0,
            'full_marks'       => 100.0,
        ]);

        // 5. Test Preview page loads correctly
        $previewResponse = $this->actingAs($this->admin)->get(route('roll-numbers.reassign', [
            'session_id'         => $session->id,
            'class_id'           => $class->id,
            'section_id'         => $section->id,
            'source_semester_id' => $terminal2->id,
            'target_semester_id' => $terminal3->id,
        ]));

        $previewResponse->assertSuccessful();
        $previewResponse->assertViewIs('roll-numbers.reassign');
        $previewResponse->assertSee('Roll Number Reassignment by Exam Rank');
        $previewResponse->assertSee('Aarav Sharma');
        $previewResponse->assertSee('Binod Thapa');

        // 6. Apply Reassignment for Terminal 3 based on Terminal 2 results
        $applyResponse = $this->actingAs($this->admin)->post(route('roll-numbers.reassign.apply'), [
            'session_id'           => $session->id,
            'class_id'             => $class->id,
            'section_id'           => $section->id,
            'source_semester_id'   => $terminal2->id,
            'target_semester_id'   => $terminal3->id,
            'update_active_roster' => 1,
            'roll_no'              => [
                $studentA->id => '1',
                $studentB->id => '2',
                $studentC->id => '3',
                $studentD->id => '4',
            ],
            'meta'                 => [
                $studentA->id => ['rank' => 1, 'total_marks' => 95, 'gpa' => 4.0, 'section_id' => $section->id],
                $studentB->id => ['rank' => 2, 'total_marks' => 85, 'gpa' => 3.6, 'section_id' => $section->id],
                $studentC->id => ['rank' => 3, 'total_marks' => 75, 'gpa' => 3.2, 'section_id' => $section->id],
                $studentD->id => ['rank' => null, 'total_marks' => null, 'gpa' => null, 'section_id' => $section->id],
            ],
        ]);

        $applyResponse->assertRedirect();
        $applyResponse->assertSessionHas('status');

        // 7. Verify Terminal Roll Numbers table
        $this->assertDatabaseHas('terminal_roll_numbers', [
            'session_id'         => $session->id,
            'semester_id'        => $terminal3->id,
            'student_id'         => $studentA->id,
            'roll_no'            => '1',
            'rank'               => 1,
            'source_semester_id' => $terminal2->id,
        ]);

        $this->assertDatabaseHas('terminal_roll_numbers', [
            'session_id'         => $session->id,
            'semester_id'        => $terminal3->id,
            'student_id'         => $studentB->id,
            'roll_no'            => '2',
            'rank'               => 2,
            'source_semester_id' => $terminal2->id,
        ]);

        $this->assertDatabaseHas('terminal_roll_numbers', [
            'session_id'         => $session->id,
            'semester_id'        => $terminal3->id,
            'student_id'         => $studentC->id,
            'roll_no'            => '3',
            'rank'               => 3,
            'source_semester_id' => $terminal2->id,
        ]);

        $this->assertDatabaseHas('terminal_roll_numbers', [
            'session_id'         => $session->id,
            'semester_id'        => $terminal3->id,
            'student_id'         => $studentD->id,
            'roll_no'            => '4',
        ]);

        // 8. Verify Promotions table updated active roll numbers
        $this->assertEquals('1', $promoA->fresh()->roll_no);
        $this->assertEquals('2', $promoB->fresh()->roll_no);
        $this->assertEquals('3', $promoC->fresh()->roll_no);
        $this->assertEquals('4', $promoD->fresh()->roll_no);

        // 9. Verify User table updated active roll numbers
        $this->assertEquals('1', $studentA->fresh()->roll_no);
        $this->assertEquals('2', $studentB->fresh()->roll_no);
        $this->assertEquals('3', $studentC->fresh()->roll_no);
        $this->assertEquals('4', $studentD->fresh()->roll_no);

        // 10. Verify helper method on Promotion
        $this->assertEquals('1', $promoA->fresh()->getRollNoForSemester($terminal3->id));
    }

    /**
     * Test that reassigning with same source and target semester fails validation.
     */
    public function test_reassign_validation_source_and_target_must_be_different()
    {
        $session = SchoolSession::create([
            'school_id'    => $this->school->id,
            'session_name' => '2083-2084',
        ]);

        $terminal = Semester::create([
            'school_id'     => $this->school->id,
            'session_id'    => $session->id,
            'semester_name' => 'Terminal 1',
            'start_date'    => '2026-04-15',
            'end_date'      => '2026-07-15',
        ]);

        $class = SchoolClass::create([
            'school_id'   => $this->school->id,
            'session_id'  => $session->id,
            'class_name'  => 'Grade 10',
        ]);

        $response = $this->actingAs($this->admin)->post(route('roll-numbers.reassign.apply'), [
            'session_id'           => $session->id,
            'class_id'             => $class->id,
            'source_semester_id'   => $terminal->id,
            'target_semester_id'   => $terminal->id, // Identical
            'roll_no'              => [1 => '1'],
        ]);

        $response->assertSessionHasErrors(['source_semester_id']);
    }

    /**
     * Test Roll Numbers index in terminal-wise mode.
     */
    public function test_roll_numbers_index_in_terminal_mode()
    {
        Setting::set('roll_number_mode', 'terminal', 'academic', $this->school->id);

        $session = SchoolSession::create([
            'school_id'    => $this->school->id,
            'session_name' => '2083-2084',
        ]);

        $terminal = Semester::create([
            'school_id'     => $this->school->id,
            'session_id'    => $session->id,
            'semester_name' => 'Terminal 1',
            'start_date'    => '2026-04-15',
            'end_date'      => '2026-07-15',
        ]);

        $class = SchoolClass::create([
            'school_id'   => $this->school->id,
            'session_id'  => $session->id,
            'class_name'  => 'Grade 9',
        ]);

        $section = Section::create([
            'school_id'    => $this->school->id,
            'session_id'   => $session->id,
            'class_id'     => $class->id,
            'section_name' => 'B',
            'room_no'      => '102',
        ]);

        $student = User::factory()->create([
            'school_id'  => $this->school->id,
            'first_name' => 'Kiran',
            'last_name'  => 'Shrestha',
            'roll_no'    => '10',
        ]);

        Promotion::create([
            'school_id'      => $this->school->id,
            'session_id'     => $session->id,
            'class_id'       => $class->id,
            'section_id'     => $section->id,
            'student_id'     => $student->id,
            'roll_no'        => '10',
            'id_card_number' => 'ID-999',
        ]);

        $response = $this->actingAs($this->admin)->get(route('roll-numbers.index', [
            'class_id'    => $class->id,
            'section_id'  => $section->id,
            'semester_id' => $terminal->id,
        ]));

        $response->assertSuccessful();
        $response->assertSee('Terminal-wise');
        $response->assertSee('Kiran Shrestha');
        $response->assertSee('Reassign by Exam Rank');
    }
}
