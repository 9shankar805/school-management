<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Role → permission mapping.
     *
     * Each role lists ONLY the permissions it needs.
     * 'super-admin' gets everything via Gate::before() in AuthServiceProvider.
     */
    public static array $roles = [

        // ----------------------------------------------------------------
        // SUPER ADMIN — manages all schools/tenants (bypass via Gate::before)
        // ----------------------------------------------------------------
        'super-admin' => ['*'],   // wildcard handled in AuthServiceProvider

        // ----------------------------------------------------------------
        // ORGANIZATION OWNER — owns a school group / multi-campus org
        // ----------------------------------------------------------------
        'organization-owner' => [
            'manage schools',
            'view system settings', 'edit system settings',
            'view audit logs', 'view activity logs',
            'manage roles', 'manage permissions',
            'view reports', 'export reports', 'view analytics', 'view advanced analytics',
            'view financial reports', 'export financial reports',
            'view staff', 'view teachers', 'view students',
            'view school sessions', 'view departments',
            'view payroll', 'manage payroll',
            'send notifications', 'manage notification templates',
        ],

        // ----------------------------------------------------------------
        // SCHOOL ADMIN — full control within one school
        // ----------------------------------------------------------------
        'admin' => [
            'create school sessions', 'view school sessions', 'edit school sessions', 'delete school sessions',
            'update browse by session',
            'create semesters', 'view semesters', 'edit semesters', 'delete semesters',
            'create departments', 'view departments', 'edit departments', 'delete departments',
            'create classes', 'view classes', 'edit classes', 'delete classes',
            'create sections', 'view sections', 'edit sections', 'delete sections',
            'create courses', 'view courses', 'edit courses', 'delete courses',
            'assign teachers',
            'view academic settings', 'edit academic settings',
            'update attendances type', 'update marks submission window',
            'create students', 'view students', 'edit students', 'delete students',
            'promote students', 'transfer students', 'view student profile',
            'create teachers', 'view teachers', 'edit teachers', 'delete teachers', 'view teacher profile',
            'create staff', 'view staff', 'edit staff', 'delete staff',
            'manage payroll', 'view payroll',
            'manage leave', 'view leave', 'approve leave',
            'manage contracts', 'manage recruitment',
            'create users', 'view users', 'edit users', 'delete users',
            'take attendances', 'view attendances', 'edit attendances',
            'view attendance reports', 'export attendance reports',
            'create exams', 'view exams', 'edit exams', 'delete exams',
            'create exams rule', 'view exams rule', 'edit exams rule', 'delete exams rule',
            'view exams history', 'manage exam hall', 'manage online exams',
            'create grading systems', 'view grading systems', 'edit grading systems', 'delete grading systems',
            'create grading systems rule', 'view grading systems rule', 'edit grading systems rule', 'delete grading systems rule',
            'save marks', 'view marks', 'edit marks', 'view results', 'publish results',
            'generate report cards', 'generate transcripts',
            'create assignments', 'view assignments', 'edit assignments', 'delete assignments', 'grade assignments',
            'create syllabi', 'view syllabi', 'edit syllabi', 'delete syllabi',
            'create routines', 'view routines', 'edit routines', 'delete routines',
            'create notices', 'view notices', 'edit notices', 'delete notices',
            'create events', 'view events', 'edit events', 'delete events',
            'view notifications', 'send notifications', 'manage notification templates',
            'create invoices', 'view invoices', 'edit invoices', 'delete invoices',
            'create payments', 'view payments', 'edit payments', 'delete payments',
            'view financial reports', 'export financial reports',
            'manage fee structure', 'manage discounts', 'manage scholarships',
            'create books', 'view books', 'edit books', 'delete books',
            'issue books', 'return books', 'view library reports', 'manage library members',
            'manage transport', 'view transport', 'assign student transport', 'view transport routes',
            'manage hostel', 'view hostel', 'assign hostel beds',
            'manage inventory', 'view inventory', 'create purchase orders',
            'upload files', 'view files', 'delete files', 'manage media',
            'view reports', 'export reports', 'create custom reports',
            'view analytics', 'view advanced analytics',
            'send sms', 'send email', 'send bulk messages', 'manage communication templates',
            'view chat', 'send chat messages',
            'view audit logs', 'manage roles', 'manage permissions',
        ],

        // ----------------------------------------------------------------
        // PRINCIPAL
        // ----------------------------------------------------------------
        'principal' => [
            'view school sessions', 'view semesters', 'view departments',
            'view classes', 'view sections', 'view courses',
            'view academic settings',
            'view students', 'view student profile',
            'view teachers', 'view teacher profile',
            'view staff', 'view payroll',
            'view attendances', 'view attendance reports', 'export attendance reports',
            'view exams', 'view exams rule', 'view exams history',
            'view grading systems', 'view grading systems rule',
            'view marks', 'view results', 'generate report cards', 'generate transcripts',
            'publish results',
            'view assignments', 'view syllabi', 'view routines',
            'create notices', 'view notices', 'edit notices', 'delete notices',
            'create events', 'view events', 'edit events', 'delete events',
            'view notifications', 'send notifications',
            'view invoices', 'view payments', 'view financial reports',
            'view books', 'view library reports',
            'view transport', 'view hostel',
            'view reports', 'export reports',
            'view analytics', 'view advanced analytics',
            'send sms', 'send email', 'send bulk messages',
            'view chat', 'send chat messages',
            'approve leave', 'view leave',
            'view audit logs',
        ],

        // ----------------------------------------------------------------
        // VICE PRINCIPAL
        // ----------------------------------------------------------------
        'vice-principal' => [
            'view school sessions', 'view semesters', 'view departments',
            'view classes', 'view sections', 'view courses',
            'view academic settings',
            'view students', 'view student profile',
            'edit students',
            'view teachers', 'view teacher profile',
            'view staff',
            'take attendances', 'view attendances', 'edit attendances',
            'view attendance reports',
            'view exams', 'view exams rule',
            'view grading systems', 'view grading systems rule',
            'view marks', 'view results',
            'view assignments', 'view syllabi', 'view routines',
            'create notices', 'view notices', 'edit notices',
            'create events', 'view events', 'edit events',
            'view notifications', 'send notifications',
            'view invoices', 'view payments',
            'view books',
            'view reports', 'view analytics',
            'send sms', 'send email',
            'view chat', 'send chat messages',
            'view leave', 'approve leave',
        ],

        // ----------------------------------------------------------------
        // ACADEMIC COORDINATOR
        // ----------------------------------------------------------------
        'academic-coordinator' => [
            'view school sessions', 'view semesters', 'view departments',
            'view classes', 'view sections',
            'create courses', 'view courses', 'edit courses',
            'view academic settings', 'update marks submission window',
            'view students', 'view student profile',
            'view teachers', 'view teacher profile', 'assign teachers',
            'take attendances', 'view attendances', 'view attendance reports',
            'create exams', 'view exams', 'edit exams',
            'create exams rule', 'view exams rule', 'edit exams rule',
            'view grading systems', 'view grading systems rule',
            'view marks', 'view results', 'generate report cards',
            'create assignments', 'view assignments', 'edit assignments',
            'create syllabi', 'view syllabi', 'edit syllabi',
            'create routines', 'view routines', 'edit routines',
            'create notices', 'view notices',
            'create events', 'view events',
            'view notifications', 'send notifications',
            'view reports', 'view analytics',
            'send email',
            'view chat', 'send chat messages',
        ],

        // ----------------------------------------------------------------
        // TEACHER (subject teacher + class teacher combined base)
        // ----------------------------------------------------------------
        'teacher' => [
            'view school sessions', 'view semesters',
            'view classes', 'view sections',
            'view courses',
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
        ],

        // ----------------------------------------------------------------
        // CLASS TEACHER (teacher + extra student pastoral access)
        // ----------------------------------------------------------------
        'class-teacher' => [
            'view school sessions', 'view semesters',
            'view classes', 'view sections', 'view courses',
            'view students', 'view student profile', 'edit students',
            'take attendances', 'view attendances', 'view attendance reports',
            'create exams', 'view exams',
            'view exams rule', 'view grading systems', 'view grading systems rule',
            'save marks', 'view marks', 'view results', 'generate report cards',
            'create assignments', 'view assignments', 'edit assignments', 'grade assignments',
            'submit assignments',
            'create syllabi', 'view syllabi', 'edit syllabi',
            'view routines',
            'view notices', 'create notices',
            'view events', 'create events',
            'view notifications', 'send notifications',
            'message teachers',
            'upload files', 'view files',
            'view books',
            'view chat', 'send chat messages',
            'view own profile',
        ],

        // ----------------------------------------------------------------
        // STUDENT
        // ----------------------------------------------------------------
        'student' => [
            'view own profile',
            'view attendances',
            'view own marks', 'view results',
            'view assignments', 'submit assignments',
            'view syllabi', 'view routines',
            'view notices', 'view events',
            'view notifications',
            'view own invoices',
            'view books',
            'upload files', 'view files',
            'view chat',
        ],

        // ----------------------------------------------------------------
        // PARENT / GUARDIAN
        // ----------------------------------------------------------------
        'parent' => [
            'view parent portal',
            'view child attendance',
            'view child results',
            'view child fees',
            'view own invoices',
            'create payments',
            'message teachers',
            'view notices', 'view events',
            'view notifications',
            'view transport routes',
            'view chat', 'send chat messages',
        ],

        // ----------------------------------------------------------------
        // ACCOUNTANT
        // ----------------------------------------------------------------
        'accountant' => [
            'create invoices', 'view invoices', 'edit invoices',
            'create payments', 'view payments', 'edit payments',
            'view financial reports', 'export financial reports',
            'manage fee structure', 'manage discounts', 'manage scholarships',
            'view students', 'view student profile',
            'view payroll', 'manage payroll',
            'view reports', 'export reports',
            'view notifications',
            'send email',
            'view chat',
        ],

        // ----------------------------------------------------------------
        // LIBRARIAN
        // ----------------------------------------------------------------
        'librarian' => [
            'create books', 'view books', 'edit books', 'delete books',
            'issue books', 'return books',
            'view library reports',
            'manage library members',
            'view students', 'view student profile',
            'view staff',
            'view notifications',
            'view chat',
            'upload files', 'view files',
        ],

        // ----------------------------------------------------------------
        // RECEPTIONIST
        // ----------------------------------------------------------------
        'receptionist' => [
            'view students', 'view student profile',
            'create students',
            'view teachers', 'view teacher profile',
            'view staff',
            'view notices', 'create notices',
            'view events', 'create events',
            'view notifications',
            'view invoices', 'create invoices',
            'view payments',
            'view transport', 'view transport routes',
            'view chat', 'send chat messages',
            'upload files', 'view files',
        ],

        // ----------------------------------------------------------------
        // HR MANAGER
        // ----------------------------------------------------------------
        'hr-manager' => [
            'view staff', 'create staff', 'edit staff', 'delete staff',
            'view teachers', 'edit teachers',
            'manage payroll', 'view payroll',
            'manage leave', 'view leave', 'approve leave',
            'manage contracts', 'manage recruitment',
            'view reports', 'export reports',
            'view notifications',
            'send email', 'send sms',
            'upload files', 'view files', 'manage media',
            'view chat',
        ],

        // ----------------------------------------------------------------
        // TRANSPORT MANAGER
        // ----------------------------------------------------------------
        'transport-manager' => [
            'manage transport', 'view transport',
            'assign student transport', 'view transport routes',
            'view students',
            'view staff',
            'view notifications',
            'view chat',
            'upload files', 'view files',
        ],

        // ----------------------------------------------------------------
        // HOSTEL MANAGER
        // ----------------------------------------------------------------
        'hostel-manager' => [
            'manage hostel', 'view hostel', 'assign hostel beds',
            'view students',
            'view staff',
            'view notifications',
            'view chat',
            'upload files', 'view files',
        ],

        // ----------------------------------------------------------------
        // EXAM CONTROLLER
        // ----------------------------------------------------------------
        'exam-controller' => [
            'create exams', 'view exams', 'edit exams', 'delete exams',
            'create exams rule', 'view exams rule', 'edit exams rule', 'delete exams rule',
            'view exams history', 'manage exam hall', 'manage online exams',
            'create grading systems', 'view grading systems', 'edit grading systems', 'delete grading systems',
            'create grading systems rule', 'view grading systems rule', 'edit grading systems rule', 'delete grading systems rule',
            'save marks', 'view marks', 'edit marks', 'view results', 'publish results',
            'generate report cards', 'generate transcripts',
            'view students', 'view student profile',
            'view school sessions', 'view semesters',
            'view classes', 'view sections', 'view courses',
            'view notifications', 'send notifications',
            'view reports', 'export reports',
            'view chat',
        ],

        // ----------------------------------------------------------------
        // ATTENDANCE OFFICER
        // ----------------------------------------------------------------
        'attendance-officer' => [
            'take attendances', 'view attendances', 'edit attendances', 'delete attendances',
            'view attendance reports', 'export attendance reports',
            'update attendances type',
            'view students', 'view student profile',
            'view staff', 'view teachers',
            'view school sessions', 'view semesters',
            'view classes', 'view sections',
            'view notifications', 'send notifications',
            'send sms', 'send email',
            'view chat',
        ],

        // ----------------------------------------------------------------
        // ADMISSION OFFICER
        // ----------------------------------------------------------------
        'admission-officer' => [
            'create students', 'view students', 'edit students', 'view student profile',
            'transfer students',
            'view school sessions', 'view semesters',
            'view classes', 'view sections',
            'view courses',
            'create invoices', 'view invoices',
            'view notifications',
            'send sms', 'send email',
            'upload files', 'view files',
            'view chat', 'send chat messages',
            'view reports',
        ],

        // ----------------------------------------------------------------
        // GUEST (read-only, public-facing)
        // ----------------------------------------------------------------
        'guest' => [
            'view notices',
            'view events',
        ],
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $allPermissions = \Spatie\Permission\Models\Permission::pluck('name')->toArray();

        foreach (static::$roles as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            // super-admin bypasses all checks via Gate::before — no need to assign individual permissions
            if ($permissions === ['*']) {
                $this->command?->info("  ✓ Role [{$roleName}] created (wildcard via Gate::before)");
                continue;
            }

            // Filter to only permissions that actually exist in the DB
            $valid = array_intersect($permissions, $allPermissions);
            $role->syncPermissions($valid);

            $this->command?->info("  ✓ Role [{$roleName}] assigned " . count($valid) . ' permissions');
        }

        $this->command?->info('✓ All ' . count(static::$roles) . ' roles seeded.');
    }
}
