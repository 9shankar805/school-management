<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\StudentParentInfo;

class StudentParentInfoSeeder extends Seeder
{
    public function run()
    {
        $students = User::where('role', 'student')->get();
        foreach ($students as $student) {
            StudentParentInfo::firstOrCreate(
                ['student_id' => $student->id],
                [
                    'father_name' => 'Mr. ' . $student->last_name,
                    'father_phone' => '555-01' . rand(10, 99),
                    'mother_name' => 'Mrs. ' . $student->last_name,
                    'mother_phone' => '555-02' . rand(10, 99),
                    'parent_address' => $student->address,
                ]
            );
        }
    }
}
