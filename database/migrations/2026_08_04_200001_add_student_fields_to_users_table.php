<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1A — Add missing student identity fields to users table.
 *
 * admission_no    — unique admission number generated on enroll
 * roll_no         — roll number within a class/section
 * guardian_id     — FK to users (a parent/guardian user) — quick single-guardian link
 *                   (full multi-guardian lives in parent_student pivot)
 * admission_date  — date student was admitted
 * student_status  — active | inactive | graduated | transferred | dropped_out
 * pan_no          — PAN for staff (tax purposes)
 * bank_name       — bank name for salary transfer
 * bank_account_no — bank account number (encrypted at rest)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Student identity
            $table->string('admission_no', 30)->nullable()->unique()->after('school_id');
            $table->string('roll_no', 10)->nullable()->after('admission_no');
            $table->date('admission_date')->nullable()->after('roll_no');
            $table->unsignedBigInteger('guardian_id')->nullable()->after('admission_date');
            $table->string('student_status', 30)->nullable()->default('active')->after('guardian_id')
                  ->comment('active|inactive|graduated|transferred|dropped_out');

            // Staff payroll fields (PAN, bank)
            $table->string('pan_no', 20)->nullable()->after('national_id');
            $table->string('bank_name', 100)->nullable()->after('pan_no');
            $table->string('bank_account_no', 30)->nullable()->after('bank_name');

            // Basic salary on user profile (used as default for payroll component calculation)
            $table->decimal('basic_salary', 12, 2)->nullable()->after('bank_account_no');

            $table->foreign('guardian_id')->references('id')->on('users')->nullOnDelete();
            $table->index('admission_no');
            $table->index('roll_no');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['guardian_id']);
            $table->dropColumn([
                'admission_no', 'roll_no', 'admission_date', 'guardian_id',
                'student_status', 'pan_no', 'bank_name', 'bank_account_no', 'basic_salary',
            ]);
        });
    }
};
