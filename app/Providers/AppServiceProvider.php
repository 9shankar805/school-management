<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\Invoice;
use App\Models\Mark;
use App\Models\Payment;
use App\Models\SalarySlip;
use App\Models\StudentTransport;
use App\Models\TeacherPayroll;
use App\Models\User;
use App\Observers\AuditObserver;
use App\Observers\ExamObserver;
use App\Observers\InvoiceObserver;
use App\Observers\PaymentObserver;
use App\Observers\SalaryObserver;
use App\Observers\StudentTransportObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ── Load our custom global helpers (formatNPR, formatRs) ────────
        require_once app_path('Helpers/helpers.php');

        $this->app->bind(\App\Interfaces\LibraryInterface::class, \App\Repositories\LibraryRepository::class);
        $this->app->bind(\App\Interfaces\StaffInterface::class, \App\Repositories\StaffRepository::class);
        $this->app->bind(\App\Interfaces\PaymentInterface::class, \App\Repositories\PaymentRepository::class);

        // Question Paper module
        $this->app->bind(\App\Interfaces\QuestionPaperInterface::class, \App\Repositories\QuestionPaperRepository::class);
        $this->app->bind(\App\Interfaces\QuestionBankInterface::class, \App\Repositories\QuestionBankRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Use our modern Tailwind pagination view ─────────────────────
        Paginator::defaultView('vendor.pagination.tailwind');
        Paginator::defaultSimpleView('vendor.pagination.tailwind');

        // ── Register UploadedFile macros for automatic .webp conversion ──
        \Illuminate\Http\UploadedFile::macro('storeWebp', function (string $directory = 'uploads', string $disk = 'public', ?string $filename = null, int $quality = 82) {
            return \App\Services\ImageWebpService::storeAsWebp($this, $directory, $disk, $filename, $quality);
        });

        // ── Set the application-wide password policy default ─────────────
        Password::defaults(function () {
            return Password::min(8)->mixedCase()->numbers()->symbols();
        });

        // ── Always define $currentSchool so layouts never throw Undefined variable ──
        \Illuminate\Support\Facades\View::share('currentSchool', null);

        // ----------------------------------------------------------------
        // Seed-on-demand guard: make sure the Spatie roles & permissions
        // tables have the default 20 roles + 189 permissions.
        //
        // Roles & permissions are system-wide (not per-school), so if the
        // platform was installed without running RoleSeeder / PermissionSeeder
        // (or a backup DB was restored without them), every role dropdown in
        // the UI would be empty and `assignRole('teacher')` would fail.
        // We fix that up lazily here on the first request.
        // ----------------------------------------------------------------
        try {
            $roleTableExists = \Illuminate\Support\Facades\Schema::hasTable('roles');
        } catch (\Throwable $e) {
            $roleTableExists = false;
        }

        if ($roleTableExists) {
            try {
                $roleCount = \Spatie\Permission\Models\Role::count();
                if ($roleCount === 0) {
                    $ensureRoles = new \Illuminate\Container\Container();
                    $seeder = new class extends \Illuminate\Database\Seeder {
                        public function run(): void
                        {
                            $system = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
                            $core   = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'organization-owner', 'guard_name' => 'web']);
                            foreach (['admin','principal','vice-principal','academic-coordinator','teacher','class-teacher','student','parent','accountant','librarian','receptionist','hr-manager','transport-manager','hostel-manager','exam-controller','attendance-officer','admission-officer','guest'] as $r) {
                                \Spatie\Permission\Models\Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
                            }
                            // At minimum ensure the permission table has a default permission
                            // so Role->syncPermissions([]) etc. never error on empty tables.
                            if (\Spatie\Permission\Models\Permission::count() === 0 && \Illuminate\Support\Facades\Schema::hasTable('permissions')) {
                                foreach (['manage roles','manage permissions','view users','create users','edit users','delete users','view notices','create notices','edit notices','delete notices','view events','create events','edit events','delete events','view syllabi','create syllabi','save marks','view marks','take attendances','view attendances','create assignments','view assignments','submit assignments','create exams','view exams','create exams rule','view exams rule','edit exams rule','delete exams rule','view routines'] as $pName) {
                                    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $pName, 'guard_name' => 'web']);
                                }
                            }
                        }
                    };
                    $seeder->setContainer($this->app)->run();
                }
            } catch (\Throwable $e) {
                // If the DB connection is not yet healthy (e.g. install-time),
                // silently swallow — next request will retry.
            }
        }

        // ----------------------------------------------------------------
        // $assignableRoles — shared with every blade view so role dropdowns
        // on the staff create/edit, role assignment, etc. always list all
        // valid school-level roles. The mapping translates the Spatie slug
        // (e.g. "vice-principal") into a human-friendly label.
        // Super-admin is excluded because it is a SaaS-only role that
        // school admins must never be able to assign.
        // ----------------------------------------------------------------
        $roleLabelMap = [
            'admin'                => 'School Admin',
            'organization-owner'   => 'Organization Owner',
            'principal'            => 'Principal',
            'vice-principal'       => 'Vice Principal',
            'academic-coordinator' => 'Academic Coordinator',
            'teacher'              => 'Teacher',
            'class-teacher'        => 'Class Teacher',
            'student'              => 'Student',
            'parent'               => 'Parent / Guardian',
            'accountant'           => 'Accountant',
            'librarian'            => 'Librarian',
            'receptionist'         => 'Receptionist',
            'hr-manager'           => 'HR Manager',
            'transport-manager'    => 'Transport Manager',
            'hostel-manager'       => 'Hostel Manager',
            'exam-controller'      => 'Exam Controller',
            'attendance-officer'   => 'Attendance Officer',
            'admission-officer'    => 'Admission Officer',
            'guest'                => 'Guest',
        ];
        try {
            if (!isset($roleTableExists)) $roleTableExists = \Illuminate\Support\Facades\Schema::hasTable('roles');
            if ($roleTableExists) {
                $roles = \Spatie\Permission\Models\Role::where('name', '!=', 'super-admin')
                    ->orderBy('name')
                    ->pluck('name')
                    ->all();
                $assignableRoles = collect($roles)->mapWithKeys(fn($slug) => [$slug => $roleLabelMap[$slug] ?? ucwords(str_replace('-', ' ', $slug))]);
            } else {
                $assignableRoles = collect();
            }
        } catch (\Throwable $e) {
            $assignableRoles = collect();
        }
        \Illuminate\Support\Facades\View::share('assignableRoles', $assignableRoles);

        // ----------------------------------------------------------------
        // Register AuditObserver on all key models
        // Add more models here as they are built out.
        // ----------------------------------------------------------------
        $modelsToAudit = [User::class, Attendance::class, Exam::class, Mark::class, Invoice::class, Payment::class];

        foreach ($modelsToAudit as $model) {
            $model::observe(AuditObserver::class);
        }

        // ----------------------------------------------------------------
        // Finance observers — auto-post to student ledger + school ledger
        // on every invoice, payment, and salary payment event.
        // ----------------------------------------------------------------
        Invoice::observe(InvoiceObserver::class);
        Payment::observe(PaymentObserver::class);
        SalarySlip::observe(SalaryObserver::class);
        TeacherPayroll::observe(SalaryObserver::class);

        // ----------------------------------------------------------------
        // Exam observer — auto-generate exam fee invoices on publish
        // ----------------------------------------------------------------
        Exam::observe(ExamObserver::class);

        // ----------------------------------------------------------------
        // Transport observer — auto-generate transport fee invoice on allocation
        // ----------------------------------------------------------------
        StudentTransport::observe(StudentTransportObserver::class);

        // ----------------------------------------------------------------
        // Share pending question-paper count with every view that
        // includes the left-menu (avoids raw @php DB calls in blade).
        // ----------------------------------------------------------------
        \Illuminate\Support\Facades\View::composer('layouts.left-menu', function ($view) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                try {
                    $pending = \App\Models\QuestionPaper::whereIn('status', ['submitted', 'reviewed'])->count();
                } catch (\Throwable $e) {
                    $pending = 0; // table may not exist yet before migrations run
                }
                $view->with('pendingPapers', $pending);
            }
        });
    }
}
