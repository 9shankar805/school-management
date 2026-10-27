<?php

namespace App\Services;

use App\Models\Promotion;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\StudentAcademicInfo;
use App\Models\StudentParentInfo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StudentImportService
{
    /**
     * Generate standard CSV sample string for download.
     */
    public function generateSampleCsv(): string
    {
        $headers = [
            'first_name',
            'last_name',
            'gender',
            'email',
            'phone',
            'roll_no',
            'birthday',
            'blood_type',
            'father_name',
            'father_phone',
            'mother_name',
            'mother_phone',
            'address',
            'city',
            'zip',
            'board_reg_no',
        ];

        $sampleRows = [
            [
                'Aarav',
                'Sharma',
                'Male',
                'aarav.sharma@example.com',
                '9801122334',
                '101',
                '2012-05-15',
                'O+',
                'Rajesh Sharma',
                '9841000001',
                'Sunita Sharma',
                '9841000002',
                'Baluwatar-4',
                'Kathmandu',
                '44600',
                'REG-2026-001',
            ],
            [
                'Pooja',
                'Adhikari',
                'Female',
                '',
                '9801122335',
                '102',
                '2012-08-20',
                'A+',
                'Bikash Adhikari',
                '9841000003',
                'Gita Adhikari',
                '9841000004',
                'Lakeside-6',
                'Pokhara',
                '33700',
                'REG-2026-002',
            ],
            [
                'Rohan',
                'Thapa',
                'Male',
                'rohan.thapa@example.com',
                '9801122336',
                '103',
                '2013-01-10',
                'B+',
                'Suresh Thapa',
                '9841000005',
                'Anita Thapa',
                '9841000006',
                'Traffic Chowk',
                'Butwal',
                '32907',
                '',
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
     * Parse uploaded CSV file into associative array rows.
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

        // Handle possible UTF-8 BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $headers = null;
        $rowNumber = 0;

        while (($data = fgetcsv($handle, 4096, ',')) !== false) {
            $rowNumber++;

            // Skip entirely empty lines
            if (empty(array_filter($data, fn($v) => trim((string)$v) !== ''))) {
                continue;
            }

            if ($headers === null) {
                // Sanitize and normalize headers
                $headers = array_map(function ($col) {
                    $cleaned = strtolower(trim((string)$col));
                    $cleaned = preg_replace('/[^a-z0-9_]/', '', str_replace([' ', '-', '.'], '_', $cleaned));
                    return $cleaned;
                }, $data);
                continue;
            }

            // Map columns with headers
            $row = [];
            foreach ($headers as $index => $header) {
                if (empty($header)) {
                    continue;
                }
                $row[$header] = isset($data[$index]) ? trim((string)$data[$index]) : '';
            }

            $row['_row_number'] = $rowNumber;
            $rows[] = $row;
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Bulk process student rows and insert into database.
     *
     * @param array $rows
     * @param int $classId
     * @param int $sectionId
     * @param int $sessionId
     * @param array $options
     * @return array
     */
    public function importStudents(
        array $rows,
        int $classId,
        int $sectionId,
        int $sessionId,
        array $options = []
    ): array {
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $importedIds = [];

        $defaultPassword = !empty($options['default_password']) ? $options['default_password'] : 'Student@123';
        $schoolId = auth()->user()->school_id ?? config('tenant.school_id') ?? 1;

        // Verify class and section existence
        $classExists = SchoolClass::where('id', $classId)->exists();
        $sectionExists = Section::where('id', $sectionId)->where('class_id', $classId)->exists();

        if (!$classExists || !$sectionExists) {
            return [
                'imported'    => 0,
                'skipped'     => count($rows),
                'errors'      => ['The selected Class or Section is invalid.'],
                'imported_ids'=> [],
            ];
        }

        foreach ($rows as $row) {
            $rowNum = $row['_row_number'] ?? ($imported + $skipped + 1);
            $firstName = trim($row['first_name'] ?? '');
            $lastName = trim($row['last_name'] ?? '');

            if ($firstName === '' || $lastName === '') {
                $errors[] = "Row {$rowNum}: First Name and Last Name are required.";
                $skipped++;
                continue;
            }

            // Clean gender
            $rawGender = strtolower(trim($row['gender'] ?? ''));
            $gender = match ($rawGender) {
                'm', 'male'   => 'Male',
                'f', 'female' => 'Female',
                default       => 'Other',
            };

            // Email handling
            $rawEmail = trim($row['email'] ?? '');
            if ($rawEmail !== '') {
                if (!filter_var($rawEmail, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Row {$rowNum} ({$firstName} {$lastName}): Invalid email address '{$rawEmail}'.";
                    $skipped++;
                    continue;
                }

                if (User::where('email', $rawEmail)->exists()) {
                    $errors[] = "Row {$rowNum} ({$firstName} {$lastName}): Email '{$rawEmail}' is already registered in the system.";
                    $skipped++;
                    continue;
                }
                $email = $rawEmail;
            } else {
                // Auto generate an email if none provided
                $cleanFirst = strtolower(preg_replace('/[^a-z0-9]/i', '', $firstName));
                $cleanLast = strtolower(preg_replace('/[^a-z0-9]/i', '', $lastName));
                $rollPart = !empty($row['roll_no']) ? '.' . preg_replace('/[^a-z0-9]/i', '', $row['roll_no']) : '';
                
                do {
                    $randomSuffix = rand(100, 999);
                    $email = "{$cleanFirst}.{$cleanLast}{$rollPart}.{$randomSuffix}@student.local";
                } while (User::where('email', $email)->exists());
            }

            // Clean other fields
            $phone = trim($row['phone'] ?? '') ?: 'N/A';
            $address = trim($row['address'] ?? '') ?: 'N/A';
            $city = trim($row['city'] ?? '') ?: 'N/A';
            $zip = trim($row['zip'] ?? '') ?: '00000';
            $birthday = !empty($row['birthday']) ? date('Y-m-d', strtotime($row['birthday'])) : null;
            $bloodType = trim($row['blood_type'] ?? '') ?: 'N/A';
            $religion = trim($row['religion'] ?? '') ?: 'N/A';
            $nationality = trim($row['nationality'] ?? '') ?: 'Nepali';
            $rollNo = !empty($row['roll_no']) ? trim((string)$row['roll_no']) : null;
            $boardRegNo = !empty($row['board_reg_no']) ? trim((string)$row['board_reg_no']) : null;

            // Parents info
            $fatherName = trim($row['father_name'] ?? '') ?: 'N/A';
            $fatherPhone = trim($row['father_phone'] ?? '') ?: 'N/A';
            $motherName = trim($row['mother_name'] ?? '') ?: 'N/A';
            $motherPhone = trim($row['mother_phone'] ?? '') ?: 'N/A';
            $parentAddress = trim($row['parent_address'] ?? '') ?: $address;

            try {
                DB::transaction(function () use (
                    $firstName,
                    $lastName,
                    $email,
                    $gender,
                    $nationality,
                    $phone,
                    $address,
                    $city,
                    $zip,
                    $birthday,
                    $religion,
                    $bloodType,
                    $rollNo,
                    $defaultPassword,
                    $schoolId,
                    $fatherName,
                    $fatherPhone,
                    $motherName,
                    $motherPhone,
                    $parentAddress,
                    $boardRegNo,
                    $sessionId,
                    $classId,
                    $sectionId,
                    &$importedIds,
                    &$imported
                ) {
                    $student = User::create([
                        'school_id'            => $schoolId,
                        'first_name'           => $firstName,
                        'last_name'            => $lastName,
                        'email'                => $email,
                        'gender'               => $gender,
                        'nationality'          => $nationality,
                        'phone'                => $phone,
                        'address'              => $address,
                        'address2'             => null,
                        'city'                 => $city,
                        'zip'                  => $zip,
                        'photo'                => null,
                        'birthday'             => $birthday,
                        'religion'             => $religion,
                        'blood_type'           => $bloodType,
                        'roll_no'              => $rollNo,
                        'role'                 => 'student',
                        'password'             => Hash::make($defaultPassword),
                        'must_change_password' => true,
                    ]);

                    // Create Parent Info
                    StudentParentInfo::create([
                        'student_id'     => $student->id,
                        'school_id'      => $schoolId,
                        'father_name'    => $fatherName,
                        'father_phone'   => $fatherPhone,
                        'mother_name'    => $motherName,
                        'mother_phone'   => $motherPhone,
                        'parent_address' => $parentAddress,
                    ]);

                    // Create Academic Info
                    StudentAcademicInfo::create([
                        'student_id'   => $student->id,
                        'board_reg_no' => $boardRegNo,
                    ]);

                    // Assign Class & Section via Promotion record
                    $idCardNumber = 'STU-' . $sessionId . '-' . str_pad((string)$student->id, 4, '0', STR_PAD_LEFT);
                    Promotion::create([
                        'student_id'     => $student->id,
                        'session_id'     => $sessionId,
                        'class_id'       => $classId,
                        'section_id'     => $sectionId,
                        'id_card_number' => $idCardNumber,
                        'roll_no'        => $rollNo,
                    ]);

                    // Assign Role and Permissions
                    $student->assignRole('student');
                    $student->givePermissionTo([
                        'view attendances',
                        'view assignments',
                        'submit assignments',
                        'view exams',
                        'view marks',
                        'view routines',
                        'view syllabi',
                        'view events',
                        'view notices',
                    ]);

                    $importedIds[] = $student->id;
                    $imported++;
                });
            } catch (\Throwable $e) {
                Log::error("Student bulk import error on row {$rowNum}: " . $e->getMessage());
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
}
