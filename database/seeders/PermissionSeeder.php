<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * All system permissions grouped by module.
     * Format: 'action resource'
     */
    public static array $permissions = [

        // ------------------------------------------------------------------
        // SYSTEM / SETTINGS
        // ------------------------------------------------------------------
        'view system settings',
        'edit system settings',
        'view audit logs',
        'manage roles',
        'manage permissions',
        'view activity logs',
        'manage schools',           // super-admin only

        // ------------------------------------------------------------------
        // SCHOOL SESSIONS / ACADEMIC CALENDAR
        // ------------------------------------------------------------------
        'create school sessions',
        'view school sessions',
        'edit school sessions',
        'delete school sessions',
        'update browse by session',

        // ------------------------------------------------------------------
        // SEMESTERS & TERMS
        // ------------------------------------------------------------------
        'create semesters',
        'view semesters',
        'edit semesters',
        'delete semesters',

        // ------------------------------------------------------------------
        // CLASSES & SECTIONS
        // ------------------------------------------------------------------
        'create classes',
        'view classes',
        'edit classes',
        'delete classes',
        'create sections',
        'view sections',
        'edit sections',
        'delete sections',

        // ------------------------------------------------------------------
        // DEPARTMENTS
        // ------------------------------------------------------------------
        'create departments',
        'view departments',
        'edit departments',
        'delete departments',

        // ------------------------------------------------------------------
        // COURSES / SUBJECTS
        // ------------------------------------------------------------------
        'create courses',
        'view courses',
        'edit courses',
        'delete courses',
        'assign teachers',

        // ------------------------------------------------------------------
        // ACADEMIC SETTINGS
        // ------------------------------------------------------------------
        'view academic settings',
        'edit academic settings',
        'update attendances type',
        'update marks submission window',

        // ------------------------------------------------------------------
        // STUDENTS
        // ------------------------------------------------------------------
        'create students',
        'view students',
        'edit students',
        'delete students',
        'promote students',
        'transfer students',
        'view student profile',
        'view own profile',

        // ------------------------------------------------------------------
        // TEACHERS
        // ------------------------------------------------------------------
        'create teachers',
        'view teachers',
        'edit teachers',
        'delete teachers',
        'view teacher profile',

        // ------------------------------------------------------------------
        // STAFF / HR
        // ------------------------------------------------------------------
        'create staff',
        'view staff',
        'edit staff',
        'delete staff',
        'manage payroll',
        'view payroll',
        'manage leave',
        'apply leave',
        'view leave',
        'approve leave',
        'manage contracts',
        'manage recruitment',

        // ------------------------------------------------------------------
        // PARENTS
        // ------------------------------------------------------------------
        'view parent portal',
        'view child attendance',
        'view child results',
        'view child fees',
        'pay child fees',
        'view child assignments',
        'apply leave for child',
        'message teachers',
        'view child notifications',

        // ------------------------------------------------------------------
        // USERS (general)
        // ------------------------------------------------------------------
        'create users',
        'view users',
        'edit users',
        'delete users',

        // ------------------------------------------------------------------
        // ATTENDANCE
        // ------------------------------------------------------------------
        'take attendances',
        'view attendances',
        'edit attendances',
        'delete attendances',
        'view attendance reports',
        'export attendance reports',

        // ------------------------------------------------------------------
        // EXAMS
        // ------------------------------------------------------------------
        'create exams',
        'view exams',
        'edit exams',
        'delete exams',
        'create exams rule',
        'view exams rule',
        'edit exams rule',
        'delete exams rule',
        'view exams history',
        'manage exam hall',
        'manage online exams',

        // ------------------------------------------------------------------
        // GRADING
        // ------------------------------------------------------------------
        'create grading systems',
        'view grading systems',
        'edit grading systems',
        'delete grading systems',
        'create grading systems rule',
        'view grading systems rule',
        'edit grading systems rule',
        'delete grading systems rule',

        // ------------------------------------------------------------------
        // MARKS / RESULTS
        // ------------------------------------------------------------------
        'save marks',
        'view marks',
        'edit marks',
        'delete marks',
        'view results',
        'publish results',
        'view own marks',
        'generate report cards',
        'generate transcripts',

        // ------------------------------------------------------------------
        // ASSIGNMENTS & SYLLABUS
        // ------------------------------------------------------------------
        'create assignments',
        'view assignments',
        'edit assignments',
        'delete assignments',
        'submit assignments',
        'grade assignments',
        'create syllabi',
        'view syllabi',
        'edit syllabi',
        'delete syllabi',

        // ------------------------------------------------------------------
        // TIMETABLE / ROUTINES
        // ------------------------------------------------------------------
        'create routines',
        'view routines',
        'edit routines',
        'delete routines',

        // ------------------------------------------------------------------
        // NOTICES & EVENTS
        // ------------------------------------------------------------------
        'create notices',
        'view notices',
        'edit notices',
        'delete notices',
        'create events',
        'view events',
        'edit events',
        'delete events',

        // ------------------------------------------------------------------
        // NOTIFICATIONS
        // ------------------------------------------------------------------
        'view notifications',
        'send notifications',
        'manage notification templates',

        // ------------------------------------------------------------------
        // FINANCE
        // ------------------------------------------------------------------
        'create invoices',
        'view invoices',
        'edit invoices',
        'delete invoices',
        'create payments',
        'view payments',
        'edit payments',
        'delete payments',
        'view own invoices',
        'view financial reports',
        'export financial reports',
        'manage fee structure',
        'manage discounts',
        'manage scholarships',

        // ------------------------------------------------------------------
        // LIBRARY
        // ------------------------------------------------------------------
        'create books',
        'view books',
        'edit books',
        'delete books',
        'issue books',
        'return books',
        'view library reports',
        'manage library members',

        // ------------------------------------------------------------------
        // TRANSPORT
        // ------------------------------------------------------------------
        'manage transport',
        'view transport',
        'assign student transport',
        'view transport routes',

        // ------------------------------------------------------------------
        // HOSTEL
        // ------------------------------------------------------------------
        'manage hostel',
        'view hostel',
        'assign hostel beds',

        // ------------------------------------------------------------------
        // INVENTORY
        // ------------------------------------------------------------------
        'manage inventory',
        'view inventory',
        'create purchase orders',

        // ------------------------------------------------------------------
        // FILES / MEDIA
        // ------------------------------------------------------------------
        'upload files',
        'view files',
        'delete files',
        'manage media',

        // ------------------------------------------------------------------
        // REPORTS
        // ------------------------------------------------------------------
        'view reports',
        'export reports',
        'create custom reports',

        // ------------------------------------------------------------------
        // ANALYTICS
        // ------------------------------------------------------------------
        'view analytics',
        'view advanced analytics',

        // ------------------------------------------------------------------
        // COMMUNICATION
        // ------------------------------------------------------------------
        'send sms',
        'send email',
        'send bulk messages',
        'manage communication templates',
        'view chat',
        'send chat messages',

        // ------------------------------------------------------------------
        // PROMOTIONS
        // ------------------------------------------------------------------
        'promote students',
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (static::$permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $this->command?->info('✓ ' . count(static::$permissions) . ' permissions created/verified.');
    }
}
