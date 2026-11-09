<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\Section;
use App\Models\Semester;
use App\Models\TerminalRollNumber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolLandingTopRankersTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'name'   => 'Rato Bangala Academy',
            'slug'   => 'rato-bangala',
            'email'  => 'info@ratobangala.edu.np',
            'status' => 'active',
        ]);

        config(['tenant.school_id' => $this->school->id]);
    }

    /**
     * Test top rankers defaults when no exam marks exist yet.
     */
    public function test_default_rankers_when_no_marks_exist()
    {
        $data = $this->school->getTopRankersData();

        $this->assertTrue($data['is_active']);
        $this->assertNotEmpty($data['overall']);
        $this->assertCount(3, $data['overall']);

        // Check 1st, 2nd, 3rd ranks
        $this->assertEquals(1, $data['overall'][0]['rank']);
        $this->assertEquals(2, $data['overall'][1]['rank']);
        $this->assertEquals(3, $data['overall'][2]['rank']);

        // Check class and section are populated
        $this->assertNotEmpty($data['overall'][0]['class_name']);
        $this->assertNotEmpty($data['overall'][0]['section_name']);
    }

    /**
     * Test auto-update from TerminalRollNumber records with class and section.
     */
    public function test_auto_update_top_rankers_from_terminal_roll_numbers()
    {
        $session = SchoolSession::create([
            'school_id'    => $this->school->id,
            'session_name' => '2081-2082',
        ]);

        $semester = Semester::create([
            'school_id'     => $this->school->id,
            'session_id'    => $session->id,
            'semester_name' => 'Final Terminal Examination 2081',
            'start_date'    => '2026-01-01',
            'end_date'      => '2026-03-30',
        ]);

        $class10 = SchoolClass::create([
            'school_id'   => $this->school->id,
            'session_id'  => $session->id,
            'class_name'  => 'Grade 10',
        ]);

        $sectionA = Section::create([
            'school_id'    => $this->school->id,
            'session_id'   => $session->id,
            'class_id'     => $class10->id,
            'section_name' => 'Section A',
            'room_no'      => '101',
        ]);

        $sectionB = Section::create([
            'school_id'    => $this->school->id,
            'session_id'   => $session->id,
            'class_id'     => $class10->id,
            'section_name' => 'Section B',
            'room_no'      => '102',
        ]);

        // Student 1: Aarav Sharma - Rank 1, 3.98 GPA, Section A
        $student1 = User::factory()->create([
            'school_id'  => $this->school->id,
            'first_name' => 'Aarav',
            'last_name'  => 'Sharma',
        ]);
        TerminalRollNumber::create([
            'school_id'   => $this->school->id,
            'session_id'  => $session->id,
            'semester_id' => $semester->id,
            'class_id'    => $class10->id,
            'section_id'  => $sectionA->id,
            'student_id'  => $student1->id,
            'roll_no'     => '1',
            'rank'        => 1,
            'gpa'         => 3.98,
            'percentage'  => 96.5,
            'total_marks' => 772,
        ]);

        // Student 2: Binod Thapa - Rank 2, 3.92 GPA, Section B
        $student2 = User::factory()->create([
            'school_id'  => $this->school->id,
            'first_name' => 'Binod',
            'last_name'  => 'Thapa',
        ]);
        TerminalRollNumber::create([
            'school_id'   => $this->school->id,
            'session_id'  => $session->id,
            'semester_id' => $semester->id,
            'class_id'    => $class10->id,
            'section_id'  => $sectionB->id,
            'student_id'  => $student2->id,
            'roll_no'     => '2',
            'rank'        => 2,
            'gpa'         => 3.92,
            'percentage'  => 94.0,
            'total_marks' => 752,
        ]);

        // Student 3: Chirag KC - Rank 3, 3.88 GPA, Section A
        $student3 = User::factory()->create([
            'school_id'  => $this->school->id,
            'first_name' => 'Chirag',
            'last_name'  => 'KC',
        ]);
        TerminalRollNumber::create([
            'school_id'   => $this->school->id,
            'session_id'  => $session->id,
            'semester_id' => $semester->id,
            'class_id'    => $class10->id,
            'section_id'  => $sectionA->id,
            'student_id'  => $student3->id,
            'roll_no'     => '3',
            'rank'        => 3,
            'gpa'         => 3.88,
            'percentage'  => 91.8,
            'total_marks' => 734,
        ]);

        $data = $this->school->getTopRankersData();

        $this->assertTrue($data['is_live_data']);
        $this->assertEquals('Final Terminal Examination 2081', $data['exam_term']);

        // Verify Rank 1
        $rank1 = $data['overall'][0];
        $this->assertEquals(1, $rank1['rank']);
        $this->assertEquals('Aarav Sharma', $rank1['student_name']);
        $this->assertEquals('Grade 10', $rank1['class_name']);
        $this->assertEquals('Section A', $rank1['section_name']);
        $this->assertEquals('3.98', $rank1['gpa']);

        // Verify Rank 2
        $rank2 = $data['overall'][1];
        $this->assertEquals(2, $rank2['rank']);
        $this->assertEquals('Binod Thapa', $rank2['student_name']);
        $this->assertEquals('Grade 10', $rank2['class_name']);
        $this->assertEquals('Section B', $rank2['section_name']);
        $this->assertEquals('3.92', $rank2['gpa']);

        // Verify Rank 3
        $rank3 = $data['overall'][2];
        $this->assertEquals(3, $rank3['rank']);
        $this->assertEquals('Chirag KC', $rank3['student_name']);
        $this->assertEquals('Grade 10', $rank3['class_name']);
        $this->assertEquals('Section A', $rank3['section_name']);
        $this->assertEquals('3.88', $rank3['gpa']);

        // Check website response contains the names, classes, and sections
        $response = $this->get('/school/' . $this->school->slug);
        $response->assertStatus(200);
        $response->assertSee('Aarav Sharma');
        $response->assertSee('Binod Thapa');
        $response->assertSee('Chirag KC');
        $response->assertSee('Grade 10');
        $response->assertSee('Section A');
        $response->assertSee('Section B');
        $response->assertSee('1st Rank');
        $response->assertSee('2nd Rank');
        $response->assertSee('3rd Rank');
    }

    /**
     * Test manual spotlight mode with custom toppers.
     */
    public function test_manual_mode_custom_toppers()
    {
        $this->school->update([
            'settings' => [
                'topper_ranks_active' => 1,
                'topper_ranks_mode'   => 'manual',
                'topper_ranks_title'  => 'SEE District Board Toppers',
                'topper_ranks_custom' => [
                    'topper_1_name'    => 'Kritika Shrestha',
                    'topper_1_class'   => 'Grade 10 SEE',
                    'topper_1_section' => 'Sagarmatha Section',
                    'topper_1_gpa'     => '4.00',
                    'topper_1_honors'  => 'National Board Gold Medalist',
                    'topper_2_name'    => 'Sujan Pokhrel',
                    'topper_2_class'   => 'Grade 10 SEE',
                    'topper_2_section' => 'Annapurna Section',
                    'topper_2_gpa'     => '3.96',
                    'topper_2_honors'  => 'District 2nd Achiever',
                    'topper_3_name'    => 'Deepa Sharma',
                    'topper_3_class'   => 'Grade 10 SEE',
                    'topper_3_section' => 'Sagarmatha Section',
                    'topper_3_gpa'     => '3.92',
                    'topper_3_honors'  => 'High Honors Scholar',
                ],
            ],
        ]);

        $data = $this->school->fresh()->getTopRankersData();

        $this->assertEquals('manual', $data['mode']);
        $this->assertEquals('SEE District Board Toppers', $data['title']);
        $this->assertEquals('Kritika Shrestha', $data['overall'][0]['student_name']);
        $this->assertEquals('Grade 10 SEE', $data['overall'][0]['class_name']);
        $this->assertEquals('Sagarmatha Section', $data['overall'][0]['section_name']);
        $this->assertEquals('4.00', $data['overall'][0]['gpa']);
    }
}
