<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module 21 — Multi-tenancy: add school_id (nullable FK) to every table
 * that participates in per-school data scoping.
 *
 * users already has school_id (migration 2026_07_21_500001).
 * The schools, permissions/roles, and system-wide tables are excluded.
 *
 * All columns are nullable so existing rows are not invalidated.
 */
return new class extends Migration
{
    /** Tables that need school_id added, grouped by domain */
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
        'student_documents',
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

        // ── Notifications / Logs ─────────────────────────────────────────
        'notifications',
        'audit_logs',
        'media_files',
        'two_factor_auth',

        // ── RFID / Biometric ─────────────────────────────────────────────
        'rfid_cards',
        'rfid_scans',
        'biometric_devices',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            if (Schema::hasColumn($table, 'school_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) {
                $t->unsignedBigInteger('school_id')
                  ->nullable()
                  ->after('id')
                  ->comment('Tenant FK — null = platform-wide');
                $t->index('school_id');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            if (!Schema::hasColumn($table, 'school_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) {
                $t->dropIndex([$t->getTable() . '_school_id_index']);
                $t->dropColumn('school_id');
            });
        }
    }
};
