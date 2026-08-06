<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module 21 — Add deleted_at (soft-deletes) to every table that does not
 * already have it.  Tables that already have deleted_at are skipped safely.
 *
 * Tables intentionally excluded (system / pivot / log tables where hard
 * delete is the correct behaviour):
 *   - migrations, failed_jobs, jobs
 *   - password_resets
 *   - personal_access_tokens
 *   - permissions, roles, model_has_permissions, model_has_roles,
 *     role_has_permissions
 *   - activity_log  (Spatie — append-only)
 *   - notifications (Laravel — read/delete via notification API)
 *   - sessions
 *   - question_bank_tag (pivot)
 *   - department_user   (pivot)
 *   - program_class     (pivot)
 *   - parent_student    (pivot)
 */
return new class extends Migration
{
    private array $tables = [
        // ── Core academic ────────────────────────────────────────────────
        'school_sessions',
        'semesters',
        'terms',
        'school_classes',
        'sections',
        'courses',
        'academic_settings',
        'syllabi',
        'routines',
        'assigned_teachers',

        // ── Curriculum / LMS ─────────────────────────────────────────────
        'programs',
        'curriculums',
        'curriculum_topics',
        'lesson_plans',
        'homeworks',
        'homework_submissions',
        'projects',
        'project_submissions',
        'study_notes',
        'online_classes',

        // ── Students ─────────────────────────────────────────────────────
        'admission_applications',
        'student_academic_infos',
        'student_parent_infos',
        'student_statuses',
        'student_transfers',
        'medical_records',
        'emergency_contacts',
        'disciplinary_records',
        'house_assignments',
        'scholarships',
        'achievements',
        'certificate_templates',
        'promotions',

        // ── Teachers / Staff ─────────────────────────────────────────────
        'departments',
        'teacher_qualifications',
        'teacher_contracts',
        'teacher_documents',
        'teacher_training',
        'teacher_attendance',
        'teacher_payrolls',
        'performance_reviews',
        'leave_types',
        'leave_applications',
        'leave_balances',
        'staff_attendance',
        'staff_contracts',
        'staff_documents',
        'staff_training',
        'staff_exit_records',
        'job_postings',
        'job_applications',
        'salary_slips',
        'tax_rates',
        'ledger_entries',

        // ── Attendance ───────────────────────────────────────────────────
        'attendances',
        'qr_attendance_tokens',

        // ── Exams ────────────────────────────────────────────────────────
        'exams',
        'exam_rules',
        'grade_rules',
        'grading_systems',
        'marks',
        'final_marks',
        'exam_halls',
        'exam_schedules',
        'exam_seat_allocations',
        're_exam_applications',
        'practical_exams',
        'practical_exam_marks',
        'online_exams',
        'online_exam_questions',
        'online_exam_attempts',
        'online_exam_answers',

        // ── Question Papers ──────────────────────────────────────────────
        'question_paper_templates',
        'question_papers',
        'question_sections',
        'question_questions',
        'question_bank',
        'question_categories',
        'question_images',
        'question_versions',
        'question_approvals',
        'question_print_logs',
        'question_download_logs',
        'question_tags',

        // ── Finance ──────────────────────────────────────────────────────
        'invoices',
        'payments',
        'fee_categories',
        'fee_structures',
        'fee_discounts',
        'student_fee_discounts',
        'fee_installments',
        'expenses',
        'income_records',

        // ── Library ──────────────────────────────────────────────────────
        'book_categories',
        'books',
        'book_issues',
        'library_members',
        'ebooks',

        // ── Transport ────────────────────────────────────────────────────
        'vehicles',
        'drivers',
        'transport_routes',
        'route_stops',
        'student_transport',
        'transport_attendance',
        'fuel_logs',
        'maintenance_logs',

        // ── Hostel ───────────────────────────────────────────────────────
        'hostels',
        'hostel_rooms',
        'hostel_beds',
        'hostel_allocations',
        'hostel_attendances',
        'hostel_visitors',
        'hostel_maintenance_requests',

        // ── Inventory ────────────────────────────────────────────────────
        'suppliers',
        'asset_categories',
        'assets',
        'inventory_items',
        'purchase_orders',
        'purchase_order_items',
        'stock_transactions',

        // ── Communication ────────────────────────────────────────────────
        'notices',
        'events',
        'conversations',
        'parent_teacher_messages',
        'assignments',

        // ── Auth / Security ──────────────────────────────────────────────
        'users',
        'two_factor_auth',
        'audit_logs',
        'media_files',
        'settings',

        // ── RFID / Biometric ─────────────────────────────────────────────
        'rfid_cards',
        'rfid_scans',
        'biometric_devices',

        // ── Communication Logs ───────────────────────────────────────────
        'sms_logs',
        'email_logs',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            if (Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) {
                $t->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            if (!Schema::hasColumn($table, 'deleted_at')) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) {
                $t->dropSoftDeletes();
            });
        }
    }
};
