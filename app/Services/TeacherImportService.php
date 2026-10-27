<?php

namespace App\Services;

use App\Models\AssignedTeacher;
use App\Models\Course;
use App\Models\Routine;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TeacherImportService
{
    /**
     * Generate a standard CSV sample string for download.
     * Columns mirror the student import pattern but contain teacher-specific fields.
     * The timetable (routine) section uses period_N_weekday / period_N_class / etc.
     * columns so one CSV row can set up a teacher's entire weekly schedule.
     */
    public function generateSampleCsv(): string
    {
        $headers = [
            // Core personal info
            'first_name',
            'last_name',
            'gender',
            'email',
            'phone',
            'birthday',
            'blood_type',
            'religion',
            'nationality',
            'address',
            'city',
            'zip',

            // Staff / payroll info
            'pan_no',
            'bank_name',
            'bank_account_no',
            'basic_salary',
            'joining_date',
            'department',

            // Class-teacher assignment  (optional)
            'class_teacher_class',    // exact class_name in DB (e.g. "Class 8")
            'class_teacher_section',  // exact section_name (e.g. "A")

            // Subject assignments  (optional, comma-separated tuples class|section|course)
            // e.g. "Class 8|A|Mathematics,Class 9|B|Science"
            'subject_assignments',

            // Timetable / routine slots  (optional)
            // Each period is encoded as:  period_N_weekday, period_N_class, period_N_section,
            //                             period_N_course, period_N_start, period_N_end, period_N_room
            // N = 1..10 (up to 10 periods per teacher in the CSV)
            'period_1_weekday',   // 1=Mon … 7=Sun
            'period_1_class',     // class_name
            'period_1_section',   // section_name
            'period_1_course',    // course_name
            'period_1_start',     // HH:MM  (24-h)
            'period_1_end',
            'period_1_room',

            'period_2_weekday',
            'period_2_class',
            'period_2_section',
            'period_2_course',
            'period_2_start',
            'period_2_end',
            'period_2_room',

            'period_3_weekday',
            'period_3_class',
            'period_3_section',
            'period_3_course',
            'period_3_start',
            'period_3_end',
            'period_3_room',
        ];

        $sampleRows = [
            [
                // Personal
                'Ramesh', 'Poudel', 'Male', 'ramesh.poudel@school.edu', '9841100001',
                '1985-07-12', 'O+', 'Hindu', 'Nepali', 'Baneshwor-10', 'Kathmandu', '44600',
                // Payroll
                'PAN123456', 'Nepal Bank', '0201234567890', '45000.00', '2018-06-01', 'Science',
                // Class teacher
                'Class 8', 'A',
                // Subjects: class|section|course (comma-separated)
                'Class 8|A|Mathematics,Class 8|B|Science',
                // Period 1
                '1', 'Class 8', 'A', 'Mathematics', '08:00', '08:45', 'Room 101',
                // Period 2
                '2', 'Class 8', 'B', 'Science',     '09:00', '09:45', 'Room 102',
                // Period 3 (empty)
                '', '', '', '', '', '', '',
            ],
            [
                'Sunita', 'Karki', 'Female', 'sunita.karki@school.edu', '9841100002',
                '1990-03-22', 'A+', 'Hindu', 'Nepali', 'Lakeside-6', 'Pokhara', '33700',
                'PAN789012', 'Himalayan Bank', '0209876543210', '42000.00', '2020-08-15', 'English',
                'Class 9', 'B',
                'Class 9|B|English',
                '1', 'Class 9', 'B', 'English', '10:00', '10:45', '',
                '3', 'Class 9', 'B', 'English', '11:00', '11:45', '',
                '', '', '', '', '', '', '',
            ],
        ];

        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers);
        foreach ($sampleRows as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Parse an uploaded CSV file into an associative-array row set.
     * Identical logic to StudentImportService::parseCsvFile().
     */
    public function parseCsvFile(string $filePath): array
    {
        $rows = [];
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return $rows;
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return $rows;
        }

        // Handle optional UTF-8 BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $headers   = null;
        $rowNumber = 0;

        while (($data = fgetcsv($handle, 8192, ',')) !== false) {
            $rowNumber++;

            // Skip entirely empty lines
            if (empty(array_filter($data, fn($v) => trim((string) $v) !== ''))) {
                continue;
            }

            if ($headers === null) {
                $headers = array_map(function ($col) {
                    $c = strtolower(trim((string) $col));
                    $c = str_replace([' ', '-', '.'], '_', $c);
                    $c = preg_replace('/[^a-z0-9_]/', '', $c);
                    return $c;
                }, $data);
                continue;
            }

            $row = [];
            foreach ($headers as $index => $header) {
                if (empty($header)) {
                    continue;
                }
                $row[$header] = isset($data[$index]) ? trim((string) $data[$index]) : '';
            }

            $row['_row_number'] = $rowNumber;
            $rows[] = $row;
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Bulk-process teacher rows and insert into the database.
     * For each row the service:
     *  1. Creates the User record with role = 'teacher'.
     *  2. Assigns Spatie role 'teacher' + standard teacher permissions.
     *  3. Optionally marks the teacher as Class Teacher for a given class/section.
     *  4. Optionally assigns the teacher to subject courses (AssignedTeacher).
     *  5. Optionally creates Routine (timetable/period) slots for the teacher.
     *
     * @param array  $rows
     * @param int    $sessionId
     * @param array  $options   ['default_password' => '...']
     * @return array{imported: int, skipped: int, errors: string[], imported_ids: int[]}
     */
    public function importTeachers(
        array $rows,
        int $sessionId,
        array $options = []
    ): array {
        $imported    = 0;
        $skipped     = 0;
        $errors      = [];
        $importedIds = [];

        $defaultPassword = !empty($options['default_password']) ? $options['default_password'] : 'Teacher@123';
        $schoolId        = auth()->user()->school_id ?? config('tenant.school_id') ?? 1;

        // Pre-load classes & sections for name→id resolution (avoids N+1 queries)
        $allClasses  = SchoolClass::where('session_id', $sessionId)->get()->keyBy(fn($c) => strtolower(trim($c->class_name)));
        $allSections = Section::all()->groupBy(fn($s) => strtolower(trim($s->section_name)));
        $allCourses  = Course::where('session_id', $sessionId)->get();

        // Get a usable semester for AssignedTeacher records
        $semesterId = Semester::where('session_id', $sessionId)->value('id') ?? 1;

        foreach ($rows as $row) {
            $rowNum    = $row['_row_number'] ?? ($imported + $skipped + 1);
            $firstName = trim($row['first_name'] ?? '');
            $lastName  = trim($row['last_name'] ?? '');

            if ($firstName === '' || $lastName === '') {
                $errors[] = "Row {$rowNum}: First Name and Last Name are required.";
                $skipped++;
                continue;
            }

            // ── Gender normalization ────────────────────────────────────────
            $rawGender = strtolower(trim($row['gender'] ?? ''));
            $gender    = match ($rawGender) {
                'm', 'male'   => 'Male',
                'f', 'female' => 'Female',
                default       => 'Other',
            };

            // ── Email handling ──────────────────────────────────────────────
            $rawEmail = trim($row['email'] ?? '');
            if ($rawEmail !== '') {
                if (!filter_var($rawEmail, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Row {$rowNum} ({$firstName} {$lastName}): Invalid email '{$rawEmail}'.";
                    $skipped++;
                    continue;
                }
                if (User::where('email', $rawEmail)->exists()) {
                    $errors[] = "Row {$rowNum} ({$firstName} {$lastName}): Email '{$rawEmail}' is already registered.";
                    $skipped++;
                    continue;
                }
                $email = $rawEmail;
            } else {
                $cleanFirst = strtolower(preg_replace('/[^a-z0-9]/i', '', $firstName));
                $cleanLast  = strtolower(preg_replace('/[^a-z0-9]/i', '', $lastName));
                do {
                    $suffix = rand(100, 999);
                    $email  = "{$cleanFirst}.{$cleanLast}.{$suffix}@staff.local";
                } while (User::where('email', $email)->exists());
            }

            // ── Optional scalar fields ──────────────────────────────────────
            $phone       = trim($row['phone']       ?? '') ?: 'N/A';
            $address     = trim($row['address']     ?? '') ?: 'N/A';
            $city        = trim($row['city']        ?? '') ?: 'N/A';
            $zip         = trim($row['zip']         ?? '') ?: '00000';
            $birthday    = !empty($row['birthday'])  ? date('Y-m-d', strtotime($row['birthday'])) : null;
            $bloodType   = trim($row['blood_type']  ?? '') ?: null;
            $religion    = trim($row['religion']    ?? '') ?: null;
            $nationality = trim($row['nationality'] ?? '') ?: 'Nepali';
            $panNo       = trim($row['pan_no']       ?? '') ?: null;
            $bankName    = trim($row['bank_name']    ?? '') ?: null;
            $bankAcc     = trim($row['bank_account_no'] ?? '') ?: null;
            $basicSalary = is_numeric(trim($row['basic_salary'] ?? '')) ? (float) $row['basic_salary'] : null;
            $joiningDate = !empty($row['joining_date']) ? date('Y-m-d', strtotime($row['joining_date'])) : null;

            // ── Class-teacher assignment metadata ───────────────────────────
            $ctClassName   = strtolower(trim($row['class_teacher_class']   ?? ''));
            $ctSectionName = strtolower(trim($row['class_teacher_section'] ?? ''));

            // ── Subject assignments ─────────────────────────────────────────
            $subjectAssignmentsRaw = trim($row['subject_assignments'] ?? '');

            // ── Period / routine slots ──────────────────────────────────────
            $periodSlots = $this->extractPeriodSlots($row);

            try {
                DB::transaction(function () use (
                    $schoolId, $sessionId, $semesterId,
                    $firstName, $lastName, $email, $gender, $nationality,
                    $phone, $address, $city, $zip, $birthday, $religion, $bloodType,
                    $panNo, $bankName, $bankAcc, $basicSalary, $joiningDate,
                    $defaultPassword,
                    $ctClassName, $ctSectionName,
                    $subjectAssignmentsRaw,
                    $periodSlots,
                    $allClasses, $allSections, $allCourses,
                    &$importedIds, &$imported
                ) {
                    // 1. Create User
                    $teacher = User::create([
                        'school_id'            => $schoolId,
                        'first_name'           => $firstName,
                        'last_name'            => $lastName,
                        'email'                => $email,
                        'gender'               => $gender,
                        'nationality'          => $nationality,
                        'phone'                => $phone,
                        'address'              => $address,
                        'city'                 => $city,
                        'zip'                  => $zip,
                        'birthday'             => $birthday,
                        'religion'             => $religion,
                        'blood_type'           => $bloodType,
                        'pan_no'               => $panNo,
                        'bank_name'            => $bankName,
                        'bank_account_no'      => $bankAcc,
                        'basic_salary'         => $basicSalary,
                        'admission_date'       => $joiningDate,   // joining_date stored here
                        'role'                 => 'teacher',
                        'password'             => Hash::make($defaultPassword),
                        'must_change_password' => true,
                    ]);

                    // 2. Assign Spatie role + permissions (names must match PermissionSeeder exactly)
                    $teacher->assignRole('teacher');
                    $teacher->givePermissionTo([
                        'view school sessions', 'view semesters',
                        'view classes', 'view sections', 'view courses',
                        'view students', 'view student profile',
                        'take attendances', 'view attendances', 'view attendance reports',
                        'create exams', 'view exams',
                        'view exams rule', 'view grading systems', 'view grading systems rule',
                        'save marks', 'view marks', 'view results',
                        'create assignments', 'view assignments', 'edit assignments', 'delete assignments', 'grade assignments',
                        'submit assignments',
                        'create syllabi', 'view syllabi', 'edit syllabi',
                        'view routines',
                        'view notices', 'create notices',
                        'view events', 'create events',
                        'view notifications',
                        'upload files', 'view files',
                        'view books',
                        'view chat', 'send chat messages',
                        'view own profile',
                        'apply leave',
                    ]);

                    // 3. Class-teacher assignment
                    if ($ctClassName !== '' && $ctSectionName !== '') {
                        $ctClass   = $allClasses->get($ctClassName);
                        $ctSections = $allSections->get($ctSectionName);
                        if ($ctClass && $ctSections) {
                            // Match section to the correct class
                            $ctSection = $ctSections->firstWhere('class_id', $ctClass->id);
                            if ($ctSection) {
                                // Clear previous class teacher for this section
                                AssignedTeacher::where('session_id', $sessionId)
                                    ->where('class_id', $ctClass->id)
                                    ->where('section_id', $ctSection->id)
                                    ->update(['is_class_teacher' => false]);

                                $defaultCourseId = $allCourses->where('class_id', $ctClass->id)->value('id') ?? 1;

                                AssignedTeacher::updateOrCreate(
                                    [
                                        'session_id'  => $sessionId,
                                        'class_id'    => $ctClass->id,
                                        'section_id'  => $ctSection->id,
                                        'teacher_id'  => $teacher->id,
                                    ],
                                    [
                                        'school_id'        => $schoolId,
                                        'semester_id'      => $semesterId,
                                        'course_id'        => $defaultCourseId,
                                        'is_class_teacher' => true,
                                    ]
                                );

                                // Promote Spatie role to class-teacher
                                if (!$teacher->hasRole('class-teacher')) {
                                    $teacher->assignRole('class-teacher');
                                }
                            }
                        }
                    }

                    // 4. Subject assignments  (format: "Class 8|A|Mathematics,Class 9|B|Science")
                    if ($subjectAssignmentsRaw !== '') {
                        foreach (explode(',', $subjectAssignmentsRaw) as $triplet) {
                            $parts = array_map('trim', explode('|', $triplet));
                            if (count($parts) < 3) {
                                continue;
                            }
                            [$saClassName, $saSectionName, $saCourseName] = $parts;

                            $saClass    = $allClasses->get(strtolower($saClassName));
                            $saSections = $allSections->get(strtolower($saSectionName));
                            $saCourse   = $allCourses->where('class_id', $saClass?->id ?? 0)
                                                     ->first(fn($c) => strtolower($c->course_name) === strtolower($saCourseName));

                            if (!$saClass || !$saSections || !$saCourse) {
                                continue;
                            }

                            $saSection = $saSections->firstWhere('class_id', $saClass->id);
                            if (!$saSection) {
                                continue;
                            }

                            AssignedTeacher::firstOrCreate(
                                [
                                    'session_id'  => $sessionId,
                                    'class_id'    => $saClass->id,
                                    'section_id'  => $saSection->id,
                                    'teacher_id'  => $teacher->id,
                                    'course_id'   => $saCourse->id,
                                ],
                                [
                                    'school_id'        => $schoolId,
                                    'semester_id'      => $semesterId,
                                    'is_class_teacher' => false,
                                ]
                            );
                        }
                    }

                    // 5. Routine / timetable period slots
                    foreach ($periodSlots as $slot) {
                        $slotClass    = $allClasses->get(strtolower($slot['class']));
                        $slotSections = $allSections->get(strtolower($slot['section']));
                        if (!$slotClass || !$slotSections) {
                            continue;
                        }
                        $slotSection = $slotSections->firstWhere('class_id', $slotClass->id);
                        if (!$slotSection) {
                            continue;
                        }

                        $slotCourse = $allCourses
                            ->where('class_id', $slotClass->id)
                            ->first(fn($c) => strtolower($c->course_name) === strtolower($slot['course']));

                        $weekday = (int) $slot['weekday'];
                        $start   = $slot['start'];
                        $end     = $slot['end'];

                        // Skip if weekday/time missing or invalid
                        if ($weekday < 1 || $weekday > 7 || !$start || !$end) {
                            continue;
                        }

                        // Conflict checks (same as RoutineController)
                        $sectionConflict = Routine::where('session_id', $sessionId)
                            ->where('section_id', $slotSection->id)
                            ->where('weekday', $weekday)
                            ->where(fn($q) => $q->where('start', '<', $end)->where('end', '>', $start))
                            ->exists();

                        $teacherConflict = Routine::where('session_id', $sessionId)
                            ->where('teacher_id', $teacher->id)
                            ->where('weekday', $weekday)
                            ->where(fn($q) => $q->where('start', '<', $end)->where('end', '>', $start))
                            ->exists();

                        if ($sectionConflict || $teacherConflict) {
                            // Log but do not fail the whole row
                            Log::warning("Teacher import: skipping conflicting slot for {$firstName} {$lastName} — period on weekday {$weekday} {$start}-{$end}");
                            continue;
                        }

                        Routine::create([
                            'school_id'  => $schoolId,
                            'session_id' => $sessionId,
                            'teacher_id' => $teacher->id,
                            'class_id'   => $slotClass->id,
                            'section_id' => $slotSection->id,
                            'course_id'  => $slotCourse?->id,
                            'weekday'    => $weekday,
                            'start'      => $start,
                            'end'        => $end,
                            'room'       => $slot['room'] ?: null,
                        ]);
                    }

                    $importedIds[] = $teacher->id;
                    $imported++;
                });
            } catch (\Throwable $e) {
                Log::error("Teacher bulk import error on row {$rowNum}: " . $e->getMessage());
                $errors[] = "Row {$rowNum} ({$firstName} {$lastName}): " . $e->getMessage();
                $skipped++;
            }
        }

        return [
            'imported'     => $imported,
            'skipped'      => $skipped,
            'errors'       => $errors,
            'imported_ids' => $importedIds,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Extract up to 10 period slot definitions from a CSV row.
     * Columns follow the pattern: period_N_weekday, period_N_class, etc.
     */
    private function extractPeriodSlots(array $row): array
    {
        $slots = [];
        for ($n = 1; $n <= 10; $n++) {
            $weekday = trim($row["period_{$n}_weekday"] ?? '');
            $class   = trim($row["period_{$n}_class"]   ?? '');
            $section = trim($row["period_{$n}_section"] ?? '');
            $course  = trim($row["period_{$n}_course"]  ?? '');
            $start   = trim($row["period_{$n}_start"]   ?? '');
            $end     = trim($row["period_{$n}_end"]     ?? '');
            $room    = trim($row["period_{$n}_room"]    ?? '');

            // If the minimum required fields are missing, assume no more periods
            if ($weekday === '' && $class === '') {
                continue;
            }
            if ($weekday === '' || $start === '' || $end === '') {
                continue;
            }

            $slots[] = compact('weekday', 'class', 'section', 'course', 'start', 'end', 'room');
        }
        return $slots;
    }
}
