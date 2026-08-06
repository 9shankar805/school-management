<?php

namespace Database\Seeders;

use App\Models\AssignedTeacher;
use App\Models\Attendance;
use App\Models\Book;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamHall;
use App\Models\ExamSchedule;
use App\Models\ExamSeatAllocation;
use App\Models\Invoice;
use App\Models\Mark;
use App\Models\Promotion;
use App\Models\Routine;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\Section;
use App\Models\Semester;
use App\Models\StudentAcademicInfo;
use App\Models\User;
use App\Repositories\ExamHallRepository;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GenesisGlobalSchoolSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('Seeding Genesis Global School...');

            // 1. Create or retrieve Genesis Global School tenant
            $school = School::updateOrCreate(
                ['id' => 3],
                [
                    'name'    => 'Genesis Global School',
                    'slug'    => 'genesis-global-school',
                    'website' => 'https://genesisglobalschool.edu.in',
                    'address' => 'Sector 132, Expressway, Noida / Kathmandu Campus',
                    'phone'   => '+91 97110 00560',
                    'email'   => 'info@genesisgs.edu.in',
                ]
            );

            // 2. Active Session & Semesters
            $session = SchoolSession::firstOrCreate(
                ['session_name' => '2026-2027', 'school_id' => $school->id]
            );

            $sem1 = Semester::firstOrCreate(
                ['semester_name' => 'First Term 2026', 'session_id' => $session->id, 'school_id' => $school->id],
                ['start_date' => '2026-04-01', 'end_date' => '2026-09-30']
            );

            $sem2 = Semester::firstOrCreate(
                ['semester_name' => 'Second Term 2026', 'session_id' => $session->id, 'school_id' => $school->id],
                ['start_date' => '2026-10-01', 'end_date' => '2027-03-31']
            );

            // 3. Classes and Sections
            $classNames = [
                'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5',
                'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10',
                'Grade 11 (Science)', 'Grade 11 (Management)',
                'Grade 12 (Science)', 'Grade 12 (Management)',
            ];

            $classes = [];
            $sections = [];
            $allSectionList = [];

            foreach ($classNames as $idx => $cName) {
                $cls = SchoolClass::firstOrCreate(
                    ['class_name' => $cName, 'session_id' => $session->id, 'school_id' => $school->id]
                );
                $classes[$cName] = $cls;

                foreach (['A', 'B'] as $secLetter) {
                    $sec = Section::firstOrCreate(
                        ['section_name' => "Section {$secLetter}", 'class_id' => $cls->id, 'session_id' => $session->id, 'school_id' => $school->id],
                        ['room_no' => 'Room ' . (100 + $cls->id)]
                    );
                    $sections["{$cls->id}_{$secLetter}"] = $sec;
                    $allSectionList[] = $sec;
                }
            }

            // 4. Create 22+ Qualified Teachers
            $teacherData = [
                ['first_name' => 'Rajesh',    'last_name' => 'Sharma',    'dept' => 'Mathematics',      'role' => 'class-teacher', 'qual' => 'Ph.D. in Applied Mathematics'],
                ['first_name' => 'Ananya',    'last_name' => 'Verma',     'dept' => 'English Literature','role' => 'teacher',       'qual' => 'M.A. English, B.Ed.'],
                ['first_name' => 'Bikram',    'last_name' => 'Thapa',     'dept' => 'Physics',          'role' => 'class-teacher', 'qual' => 'M.Sc. Physics'],
                ['first_name' => 'Sunita',    'last_name' => 'Adhikari',  'dept' => 'Chemistry',        'role' => 'teacher',       'qual' => 'M.Sc. Organic Chemistry'],
                ['first_name' => 'David',     'last_name' => 'Miller',    'dept' => 'Biology',          'role' => 'class-teacher', 'qual' => 'M.Sc. Biotechnology'],
                ['first_name' => 'Priyanka',  'last_name' => 'Gupta',     'dept' => 'Computer Science', 'role' => 'teacher',       'qual' => 'M.Tech Computer Science'],
                ['first_name' => 'Sanjay',    'last_name' => 'Karki',     'dept' => 'Social Studies',   'role' => 'class-teacher', 'qual' => 'M.A. History, B.Ed.'],
                ['first_name' => 'Meera',     'last_name' => 'Joshi',     'dept' => 'Nepali',           'role' => 'teacher',       'qual' => 'M.A. Nepali Literature'],
                ['first_name' => 'Vivek',     'last_name' => 'Malhotra',  'dept' => 'Economics',        'role' => 'class-teacher', 'qual' => 'M.A. Economics'],
                ['first_name' => 'Archana',   'last_name' => 'Shrestha',  'dept' => 'Accountancy',      'role' => 'teacher',       'qual' => 'M.Com, FCA'],
                ['first_name' => 'Ramesh',    'last_name' => 'Bhattarai', 'dept' => 'Business Studies', 'role' => 'teacher',       'qual' => 'MBA, B.Ed.'],
                ['first_name' => 'Neha',      'last_name' => 'Agarwal',   'dept' => 'Fine Arts',        'role' => 'teacher',       'qual' => 'Master of Fine Arts'],
                ['first_name' => 'Alok',      'last_name' => 'Nandi',     'dept' => 'Physical Education','role' => 'teacher',      'qual' => 'M.P.Ed.'],
                ['first_name' => 'Sarita',    'last_name' => 'Dahal',     'dept' => 'Music & Performing Arts','role' => 'teacher', 'qual' => 'M.Mus.'],
                ['first_name' => 'Deepak',    'last_name' => 'Pokhrel',   'dept' => 'Robotics & STEM',  'role' => 'teacher',       'qual' => 'B.E. Robotics & AI'],
                ['first_name' => 'Sabina',    'last_name' => 'Giri',      'dept' => 'Environmental Science','role' => 'teacher',   'qual' => 'M.Sc. Environmental Science'],
                ['first_name' => 'Nitesh',    'last_name' => 'Pandey',    'dept' => 'Mathematics',      'role' => 'class-teacher', 'qual' => 'M.Sc. Mathematics'],
                ['first_name' => 'Kavita',    'last_name' => 'Roy',       'dept' => 'French & Foreign Languages','role' => 'teacher','qual' => 'M.A. French Studies'],
                ['first_name' => 'Mohan',     'last_name' => 'Khadka',    'dept' => 'Physics',          'role' => 'teacher',       'qual' => 'M.Sc. Astrophysics'],
                ['first_name' => 'Ritu',      'last_name' => 'Saxena',    'dept' => 'Psychology',       'role' => 'teacher',       'qual' => 'M.Sc. Counseling Psychology'],
                ['first_name' => 'Tenzing',   'last_name' => 'Sherpa',    'dept' => 'Geography',        'role' => 'teacher',       'qual' => 'M.A. Geography'],
                ['first_name' => 'Pooja',     'last_name' => 'Sen',       'dept' => 'Chemistry',        'role' => 'teacher',       'qual' => 'M.Sc. Industrial Chemistry'],
            ];

            $teachers = [];
            $classTeacherPool = [];

            foreach ($teacherData as $idx => $t) {
                $email = strtolower("{$t['first_name']}.{$t['last_name']}@genesisgs.edu.in");
                $teacherUser = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'first_name'        => $t['first_name'],
                        'last_name'         => $t['last_name'],
                        'gender'            => ($idx % 2 === 0) ? 'Male' : 'Female',
                        'nationality'       => 'Indian',
                        'phone'             => '+91 98' . rand(10000000, 99999999),
                        'address'           => 'Faculty Quarters, Genesis Global School Campus',
                        'city'              => 'Noida',
                        'zip'               => '201304',
                        'role'              => $t['role'],
                        'school_id'         => $school->id,
                        'password'          => Hash::make('password'),
                        'email_verified_at' => now(),
                    ]
                );
                $teacherUser->syncRoles([$t['role']]);
                $teachers[] = $teacherUser;

                if ($t['role'] === 'class-teacher') {
                    $classTeacherPool[] = $teacherUser;
                }
            }

            if (empty($classTeacherPool)) {
                $classTeacherPool = $teachers;
            }

            // 4B. Create School Management & Administrative Staff Accounts
            $staffMembers = [
                ['first_name' => 'Madhav',  'last_name' => 'Deo',      'email' => 'principal@genesisgs.edu.in',   'role' => 'principal',            'title' => 'School Principal'],
                ['first_name' => 'Sunita',  'last_name' => 'Rao',      'email' => 'viceprincipal@genesisgs.edu.in','role' => 'vice-principal',       'title' => 'Vice Principal'],
                ['first_name' => 'Anil',    'last_name' => 'Kapoor',   'email' => 'coordinator@genesisgs.edu.in', 'role' => 'academic-coordinator', 'title' => 'Academic Coordinator'],
                ['first_name' => 'Priya',   'last_name' => 'Sharma',   'email' => 'hr@genesisgs.edu.in',          'role' => 'hr-manager',           'title' => 'Human Resources Manager'],
                ['first_name' => 'Rakesh',  'last_name' => 'Verma',    'email' => 'accountant@genesisgs.edu.in',  'role' => 'accountant',           'title' => 'Chief Accountant'],
                ['first_name' => 'Sujata',  'last_name' => 'Bhatia',   'email' => 'librarian@genesisgs.edu.in',   'role' => 'librarian',            'title' => 'Head Librarian'],
                ['first_name' => 'Sanjay',  'last_name' => 'Mishra',   'email' => 'examcontroller@genesisgs.edu.in','role' => 'exam-controller',     'title' => 'Controller of Examinations'],
                ['first_name' => 'Nidhi',   'last_name' => 'Chopra',   'email' => 'receptionist@genesisgs.edu.in','role' => 'receptionist',         'title' => 'Front Desk Receptionist'],
                ['first_name' => 'Deepak',  'last_name' => 'Mehta',    'email' => 'admissions@genesisgs.edu.in',  'role' => 'admission-officer',    'title' => 'Chief Admission Officer'],
                ['first_name' => 'Vikram',  'last_name' => 'Singh',    'email' => 'transport@genesisgs.edu.in',   'role' => 'transport-manager',    'title' => 'Transport Manager'],
                ['first_name' => 'Karan',   'last_name' => 'Thapa',    'email' => 'hostel@genesisgs.edu.in',      'role' => 'hostel-manager',       'title' => 'Hostel Warden & Manager'],
                ['first_name' => 'Anita',   'last_name' => 'Pradhan',  'email' => 'attendance@genesisgs.edu.in',  'role' => 'attendance-officer',   'title' => 'Attendance Officer'],
            ];

            $staffUsers = [];
            foreach ($staffMembers as $stfIdx => $stf) {
                $staffUser = User::updateOrCreate(
                    ['email' => $stf['email']],
                    [
                        'first_name'        => $stf['first_name'],
                        'last_name'         => $stf['last_name'],
                        'gender'            => ($stfIdx % 2 === 0) ? 'Male' : 'Female',
                        'nationality'       => 'Indian',
                        'phone'             => '+91 98' . rand(10000000, 99999999),
                        'address'           => 'Administration Wing, Genesis Global School',
                        'city'              => 'Noida',
                        'zip'               => '201304',
                        'role'              => $stf['role'],
                        'school_id'         => $school->id,
                        'password'          => Hash::make('password'),
                        'email_verified_at' => now(),
                    ]
                );
                $staffUser->syncRoles([$stf['role']]);
                $staffUsers[$stf['role']] = $staffUser;
            }

            // 5. Courses & Assigned Teachers per Class and Section
            $createdCourses = [];
            $classListList = array_values($classes);

            foreach ($classListList as $cIdx => $cls) {
                $subjectList = [
                    'Mathematics', 'English Literature', 'Science', 'Nepali',
                    'Social Studies', 'Computer Science', 'Physical Education', 'Fine Arts'
                ];

                if (str_contains($cls->class_name, 'Science')) {
                    $subjectList = ['Physics', 'Chemistry', 'Mathematics', 'Biology', 'English Literature', 'Computer Science'];
                } elseif (str_contains($cls->class_name, 'Management')) {
                    $subjectList = ['Accountancy', 'Business Studies', 'Economics', 'English Literature', 'Mathematics'];
                }

                $classCourses = [];
                foreach ($subjectList as $sIdx => $sName) {
                    $course = Course::firstOrCreate(
                        ['course_name' => $sName, 'class_id' => $cls->id, 'session_id' => $session->id, 'school_id' => $school->id],
                        ['course_type' => 'theory', 'semester_id' => $sem1->id]
                    );
                    $createdCourses[] = $course;
                    $classCourses[] = $course;
                }

                // Assign teachers to courses per section
                foreach (['A', 'B'] as $secLetter) {
                    $sec = $sections["{$cls->id}_{$secLetter}"];
                    foreach ($classCourses as $crsIdx => $crs) {
                        $subjTeacher = $teachers[($cIdx + $crsIdx) % count($teachers)];
                        AssignedTeacher::firstOrCreate([
                            'teacher_id'  => $subjTeacher->id,
                            'semester_id' => $sem1->id,
                            'class_id'    => $cls->id,
                            'section_id'  => $sec->id,
                            'course_id'   => $crs->id,
                            'session_id'  => $session->id,
                            'school_id'   => $school->id,
                        ]);
                    }
                }
            }

            // 6. Create 45+ Students & Guardians across Houses
            $studentNames = [
                ['Aarav', 'Sharma'], ['Riya', 'Karki'], ['Rohan', 'Adhikari'], ['Saniya', 'Gupta'],
                ['Ayush', 'Thapa'], ['Ananya', 'Joshi'], ['Kushal', 'Shrestha'], ['Diya', 'Verma'],
                ['Prashant', 'Bhattarai'], ['Kavya', 'Pandey'], ['Nabin', 'Dahal'], ['Pooja', 'Giri'],
                ['Sujan', 'Pokhrel'], ['Sreya', 'Saxena'], ['Bibek', 'Sherpa'], ['Taniya', 'Sen'],
                ['Aman', 'Malhotra'], ['Isha', 'Roy'], ['Nirajan', 'Khadka'], ['Mehak', 'Agarwal'],
                ['Subash', 'Nandi'], ['Sneha', 'Rai'], ['Deepesh', 'Chaudhary'], ['Aarti', 'Pradhan'],
                ['Sankalpa', 'Bista'], ['Simran', 'Gautam'], ['Biplov', 'Magar'], ['Pratima', 'Tamang'],
                ['Abhinav', 'Yadav'], ['Prajwal', 'Singh'], ['Sristi', 'Rana'], ['Nishan', 'Basnet'],
                ['Aakriti', 'Dhakal'], ['Bishal', 'Gurung'], ['Binita', 'Khatiwada'], ['Saroj', 'KC'],
                ['Shristi', 'Regmi'], ['Manish', 'Devkota'], ['Kritika', 'Subedi'], ['Aashish', 'Luitel'],
                ['Nisha', 'Maharjan'], ['Sunil', 'Shrestha'], ['Dikshya', 'Neupane'], ['Rajiv', 'Rizal'],
                ['Prashna', 'Giri']
            ];

            $studentUsers = [];
            foreach ($studentNames as $sIdx => $s) {
                $targetClass   = $classListList[$sIdx % count($classListList)];
                $targetSection = $sections["{$targetClass->id}_" . ($sIdx % 2 === 0 ? 'A' : 'B')];
                $email         = strtolower("student" . ($sIdx + 1) . "@genesisgs.edu.in");
                $admNo         = "GGS-2026-" . str_pad($sIdx + 1, 3, '0', STR_PAD_LEFT);

                $student = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'first_name'        => $s[0],
                        'last_name'         => $s[1],
                        'gender'            => ($sIdx % 2 === 0) ? 'Male' : 'Female',
                        'nationality'       => 'Nepali',
                        'phone'             => '+91 98' . rand(10000000, 99999999),
                        'address'           => 'Genesis Student Residences, Noida',
                        'city'              => 'Noida',
                        'zip'               => '201304',
                        'role'              => 'student',
                        'school_id'         => $school->id,
                        'password'          => Hash::make('password'),
                        'email_verified_at' => now(),
                    ]
                );
                $student->syncRoles(['student']);
                $studentUsers[] = $student;

                // Student Academic Info
                StudentAcademicInfo::updateOrCreate(
                    ['student_id' => $student->id],
                    [
                        'board_reg_no' => $admNo,
                        'school_id'    => $school->id,
                    ]
                );

                // Promotion record
                Promotion::updateOrCreate(
                    ['student_id' => $student->id, 'session_id' => $session->id],
                    [
                        'class_id'       => $targetClass->id,
                        'section_id'     => $targetSection->id,
                        'id_card_number' => "ID-{$admNo}",
                        'school_id'      => $school->id,
                    ]
                );
            }

            // 7. Structured Period Routines (Periods 1 to 7 with Class Teacher assigned to Period 1)
            $weekdays = [1, 2, 3, 4, 5];
            $periodSlots = [
                ['period' => 1, 'start' => '08:30', 'end' => '09:15'], // Period 1: Class Teacher Period / Core Subject
                ['period' => 2, 'start' => '09:20', 'end' => '10:05'],
                ['period' => 3, 'start' => '10:10', 'end' => '10:55'],
                ['period' => 4, 'start' => '11:15', 'end' => '12:00'],
                ['period' => 5, 'start' => '12:05', 'end' => '12:50'],
                ['period' => 6, 'start' => '13:30', 'end' => '14:15'],
                ['period' => 7, 'start' => '14:20', 'end' => '15:05'],
            ];

            foreach ($classListList as $cIdx => $cls) {
                foreach (['A', 'B'] as $secLetter) {
                    $sec = $sections["{$cls->id}_{$secLetter}"];
                    $secIndex = ($cIdx * 2) + ($secLetter === 'A' ? 0 : 1);
                    $assignedClassTeacher = $classTeacherPool[$secIndex % count($classTeacherPool)];
                    $cCourses = Course::where('class_id', $cls->id)->where('session_id', $session->id)->get();

                    if ($cCourses->isEmpty()) continue;

                    foreach ($weekdays as $wIdx => $day) {
                        foreach ($periodSlots as $pIdx => $pSlot) {
                            $periodNum = $pSlot['period'];
                            $targetCourse = $cCourses[($periodNum - 1) % $cCourses->count()];

                            if ($periodNum === 1) {
                                $periodTeacher = $assignedClassTeacher;
                            } else {
                                $periodTeacher = $teachers[($secIndex + $periodNum - 1) % count($teachers)];
                            }

                            Routine::firstOrCreate([
                                'weekday'    => $day,
                                'start'      => $pSlot['start'],
                                'class_id'   => $cls->id,
                                'section_id' => $sec->id,
                                'session_id' => $session->id,
                                'school_id'  => $school->id,
                            ], [
                                'end'        => $pSlot['end'],
                                'course_id'  => $targetCourse->id,
                                'teacher_id' => $periodTeacher->id,
                            ]);
                        }
                    }
                }
            }

            // 8. 30-Day Attendance Logs
            $startDate = now()->subDays(30);
            for ($d = 0; $d < 30; $d++) {
                $logDate = (clone $startDate)->addDays($d);
                if ($logDate->isWeekend()) continue;

                foreach (array_slice($studentUsers, 0, 30) as $stIdx => $st) {
                    $promo = Promotion::where('student_id', $st->id)->where('session_id', $session->id)->first();
                    $status = ($stIdx % 10 === 0) ? 'absent' : 'present';

                    Attendance::firstOrCreate([
                        'student_id' => $st->id,
                        'session_id' => $session->id,
                        'date'       => $logDate->toDateString(),
                    ], [
                        'status'     => $status,
                        'class_id'   => $promo?->class_id ?? $classListList[0]->id,
                        'section_id' => $promo?->section_id ?? 0,
                        'school_id'  => $school->id,
                    ]);
                }
            }

            // 9. Exam Halls, Schedules, Marks & Seating Allocations
            $midExam = Exam::firstOrCreate(
                ['exam_name' => 'Mid-Term Examination 2026', 'session_id' => $session->id, 'school_id' => $school->id],
                [
                    'start_date'  => '2026-09-10',
                    'end_date'    => '2026-09-20',
                    'semester_id' => $sem1->id,
                    'class_id'    => $classListList[0]->id,
                    'course_id'   => $createdCourses[0]->id,
                ]
            );

            // Create Exam Halls
            $hallMain = ExamHall::firstOrCreate(
                ['hall_name' => 'Main Auditorium Complex', 'school_id' => $school->id, 'session_id' => $session->id],
                ['building' => 'Block A', 'floor' => '1st Floor', 'capacity' => 100, 'is_active' => true]
            );

            $hallScience = ExamHall::firstOrCreate(
                ['hall_name' => 'Science Block Exam Center', 'school_id' => $school->id, 'session_id' => $session->id],
                ['building' => 'Science Wing', 'floor' => '2nd Floor', 'capacity' => 60, 'is_active' => true]
            );

            // Exam Schedule sitting
            $examSchedule = ExamSchedule::firstOrCreate(
                ['exam_id' => $midExam->id, 'session_id' => $session->id, 'school_id' => $school->id],
                [
                    'hall_id'        => $hallMain->id,
                    'exam_date'      => '2026-09-12',
                    'start_time'     => '09:00',
                    'end_time'       => '12:00',
                    'invigilator_id' => $teachers[0]->id,
                    'notes'          => 'Staggered seating plan managed by Academic Coordinator',
                ]
            );

            // Auto Allocate Seats with Anti-Cheating Staggered Strategy
            $studentsToSeat = Promotion::where('session_id', $session->id)
                ->where('school_id', $school->id)
                ->pluck('student_id')
                ->toArray();

            if (!empty($studentsToSeat)) {
                $hallRepo = new ExamHallRepository();
                $hallRepo->autoAllocateSeats($examSchedule->id, $studentsToSeat, 4, 5, 2, 'staggered');
            }

            // Student Marks
            foreach (array_slice($studentUsers, 0, 25) as $st) {
                $promo = Promotion::where('student_id', $st->id)->where('session_id', $session->id)->first();
                if (!$promo) continue;

                $stCourses = Course::where('class_id', $promo->class_id)->get();
                foreach ($stCourses as $crs) {
                    Mark::updateOrCreate(
                        ['student_id' => $st->id, 'exam_id' => $midExam->id, 'course_id' => $crs->id, 'session_id' => $session->id],
                        [
                            'marks'      => rand(65, 98),
                            'class_id'   => $promo->class_id,
                            'section_id' => $promo->section_id,
                            'school_id'  => $school->id,
                        ]
                    );
                }
            }

            // 10. Fee Invoices
            $feeTitles = ['First Term Tuition Fee', 'Laboratory & Science Fee', 'Library & E-Resource Fee', 'Annual Sports & Activity Fee'];
            foreach (array_slice($studentUsers, 0, 35) as $sIdx => $st) {
                $title  = $feeTitles[$sIdx % count($feeTitles)];
                $status = ($sIdx % 3 === 0) ? 'unpaid' : 'paid';
                $amount = rand(5000, 25000);

                Invoice::firstOrCreate([
                    'student_id' => $st->id,
                    'title'      => $title,
                ], [
                    'amount'    => $amount,
                    'status'    => $status,
                    'due_date'  => now()->addDays(15)->toDateString(),
                    'school_id' => $school->id,
                ]);
            }

            // 11. Library Catalog Books
            $books = [
                ['title' => 'Core Physics for IB Diploma & CBSE', 'author' => 'Dr. K. L. Vasudeva',  'isbn' => '978-0198390039', 'qty' => 15],
                ['title' => 'Advanced Organic Chemistry',         'author' => 'O. P. Tandon',        'isbn' => '978-9382314561', 'qty' => 20],
                ['title' => 'Higher Algebra & Calculus',           'author' => 'H. S. Hall & S. Knight','isbn' => '978-8123901459', 'qty' => 25],
                ['title' => 'Robotics & Artificial Intelligence',  'author' => 'Deepak Pokhrel',       'isbn' => '978-0262033848', 'qty' => 12],
                ['title' => 'Macroeconomics & World Trade',        'author' => 'N. Gregory Mankiw',   'isbn' => '978-1305971493', 'qty' => 18],
                ['title' => 'To Kill a Mockingbird',               'author' => 'Harper Lee',          'isbn' => '978-0060935467', 'qty' => 30],
                ['title' => 'The Great Gatsby',                    'author' => 'F. Scott Fitzgerald', 'isbn' => '978-0743273565', 'qty' => 25],
                ['title' => 'Modern Computer Science with Python', 'author' => 'Sumita Arora',        'isbn' => '978-9389682145', 'qty' => 40],
            ];

            foreach ($books as $b) {
                Book::firstOrCreate(
                    ['title' => $b['title'], 'school_id' => $school->id],
                    [
                        'author'         => $b['author'],
                        'isbn'           => $b['isbn'],
                        'qty'            => $b['qty'],
                        'available_qty'  => $b['qty'] - rand(1, 4),
                        'shelf_location' => 'Main Library - Rack ' . rand(1, 10),
                    ]
                );
            }

            $this->call(QuestionPaperSeeder::class);

            $this->command->info('Genesis Global School successfully seeded with Exam Halls, Staggered Seating Allocations, Class Teachers, 7-Period Routines, Question Papers, and Management Accounts!');
        });
    }
}
