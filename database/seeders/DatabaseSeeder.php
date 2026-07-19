<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
{
    public function run()
    {
        $this->call([
            AcademicSettingSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            RoleSeeder::class,              // must run AFTER PermissionSeeder
            SchoolSessionSeeder::class,
            SemesterSeeder::class,
            SchoolClassSeeder::class,
            SectionSeeder::class,
            StudentAcademicInfoSeeder::class,
            StudentParentInfoSeeder::class,
            CourseSeeder::class,
            GradingSystemSeeder::class,
            GradeRuleSeeder::class,
            ExamSeeder::class,
            ExamRuleSeeder::class,
            AssignedTeacherSeeder::class,
            RoutineSeeder::class,
            SyllabusSeeder::class,
            EventSeeder::class,
            NoticeSeeder::class,
            AssignmentSeeder::class,
            BookSeeder::class,
            InvoiceSeeder::class,
        ]);
    }
}
