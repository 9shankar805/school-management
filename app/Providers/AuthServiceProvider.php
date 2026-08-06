<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        App\Models\User::class => App\Policies\UserPolicy::class,
        App\Models\Attendance::class => App\Policies\AttendancePolicy::class,
        App\Models\Mark::class => App\Policies\MarkPolicy::class,
        App\Models\FinalMark::class => App\Policies\FinalMarkPolicy::class,
        App\Models\Invoice::class => App\Policies\InvoicePolicy::class,
        App\Models\Payment::class => App\Policies\PaymentPolicy::class,
        App\Models\Expense::class => App\Policies\ExpensePolicy::class,
        App\Models\LedgerEntry::class => App\Policies\LedgerEntryPolicy::class,
        App\Models\FeeStructure::class => App\Policies\FeeStructurePolicy::class,
        App\Models\FeeCategory::class => App\Policies\FeeCategoryPolicy::class,
        App\Models\Scholarship::class => App\Policies\ScholarshipPolicy::class,
        App\Models\AdmissionApplication::class => App\Policies\AdmissionApplicationPolicy::class,
        App\Models\Promotion::class => App\Policies\PromotionPolicy::class,
        App\Models\Exam::class => App\Policies\ExamPolicy::class,
        App\Models\HostelAllocation::class => App\Policies\HostelAllocationPolicy::class,
        App\Models\Vehicle::class => App\Policies\VehiclePolicy::class,
        App\Models\MedicalRecord::class => App\Policies\MedicalRecordPolicy::class,
        App\Models\DisciplinaryRecord::class => App\Policies\DisciplinaryRecordPolicy::class,
        App\Models\ResultSheet::class => App\Policies\ResultSheetPolicy::class,
        App\Models\Hostel::class => App\Policies\HostelPolicy::class,
        App\Models\QuestionPaper::class => App\Policies\QuestionPaperPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Super-admin and school admins/teachers bypass permission gates so buttons are always visible
        Gate::before(function ($user) {
            $role = strtolower($user->role ?? '');
            if (in_array($role, ['super-admin', 'admin', 'principal', 'vice-principal', 'accountant', 'teacher', 'class-teacher'])) {
                return true;
            }
            try {
                if ($user->hasAnyRole(['super-admin', 'admin', 'principal', 'vice-principal', 'accountant', 'teacher', 'class-teacher'])) {
                    return true;
                }
            } catch (\Throwable $e) {
                // Ignore Spatie role lookup failures
            }
        });
    }
}
