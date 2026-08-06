<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Module 21 — Add indexes on FK columns that are missing one.
 *
 * Strategy: check INFORMATION_SCHEMA before adding each index so this
 * migration is idempotent and safe to re-run.
 */
return new class extends Migration
{
    /**
     * Map of table => [column, ...] pairs that need a plain index.
     * Columns already covered by a unique or composite index are excluded.
     */
    private array $indexes = [
        // ── Academic core ────────────────────────────────────────────────
        'school_sessions'      => ['school_id'],
        'semesters'            => ['school_id', 'session_id'],
        'terms'                => ['school_id', 'session_id'],
        'school_classes'       => ['school_id', 'session_id'],
        'sections'             => ['school_id', 'class_id', 'session_id'],
        'courses'              => ['school_id', 'class_id', 'semester_id', 'session_id'],
        'academic_settings'    => ['school_id'],
        'syllabi'              => ['school_id', 'class_id', 'course_id', 'session_id'],
        'routines'             => ['school_id', 'class_id', 'course_id', 'section_id', 'session_id', 'teacher_id'],
        'assigned_teachers'    => ['school_id', 'class_id', 'course_id', 'section_id', 'semester_id', 'session_id', 'teacher_id'],

        // ── Curriculum / LMS ─────────────────────────────────────────────
        'programs'             => ['school_id'],
        'curriculums'          => ['school_id', 'class_id', 'course_id', 'session_id'],
        'curriculum_topics'    => ['school_id', 'term_id'],
        'lesson_plans'         => ['school_id', 'class_id', 'section_id', 'session_id', 'term_id', 'curriculum_topic_id'],
        'homeworks'            => ['school_id', 'class_id', 'section_id', 'session_id'],
        'homework_submissions' => ['school_id'],
        'projects'             => ['school_id', 'class_id', 'section_id', 'session_id'],
        'project_submissions'  => ['school_id'],
        'study_notes'          => ['school_id', 'class_id', 'session_id', 'term_id'],
        'online_classes'       => ['school_id', 'class_id', 'section_id', 'session_id'],

        // ── Students ─────────────────────────────────────────────────────
        'admission_applications' => ['school_id', 'class_id'],
        'student_academic_infos' => ['school_id', 'student_id'],
        'student_parent_infos'   => ['school_id', 'student_id'],
        'student_statuses'       => ['school_id', 'class_id', 'session_id'],
        'student_transfers'      => ['school_id', 'from_class_id', 'to_class_id', 'from_session_id', 'to_session_id', 'from_section_id', 'to_section_id'],
        'medical_records'        => ['school_id'],
        'emergency_contacts'     => ['school_id'],
        'disciplinary_records'   => ['school_id'],
        'house_assignments'      => ['school_id'],
        'scholarships'           => ['school_id'],
        'achievements'           => ['school_id'],
        'certificate_templates'  => ['school_id'],
        'promotions'             => ['school_id', 'class_id', 'section_id', 'session_id', 'student_id'],

        // ── Teachers / Staff ─────────────────────────────────────────────
        'departments'       => ['school_id'],
        'teacher_qualifications' => ['school_id'],
        'teacher_contracts' => ['school_id'],
        'teacher_documents' => ['school_id'],
        'teacher_training'  => ['school_id'],
        'teacher_attendance'=> ['school_id'],
        'teacher_payrolls'  => ['school_id'],
        'performance_reviews' => ['school_id'],
        'leave_types'       => ['school_id'],
        'leave_applications'=> ['school_id', 'leave_type_id'],
        'leave_balances'    => ['school_id'],
        'staff_attendance'  => ['school_id'],
        'staff_contracts'   => ['school_id'],
        'staff_documents'   => ['school_id'],
        'staff_training'    => ['school_id'],
        'staff_exit_records'=> ['school_id'],
        'job_postings'      => ['school_id'],
        'job_applications'  => ['school_id'],
        'salary_slips'      => ['school_id'],
        'tax_rates'         => ['school_id'],
        'ledger_entries'    => ['school_id'],

        // ── Attendance ───────────────────────────────────────────────────
        'attendances'           => ['school_id', 'course_id', 'section_id', 'session_id'],
        'qr_attendance_tokens'  => ['school_id', 'course_id', 'section_id', 'session_id'],

        // ── Exams ────────────────────────────────────────────────────────
        'exams'              => ['school_id', 'class_id', 'course_id', 'semester_id', 'session_id'],
        'exam_rules'         => ['school_id', 'exam_id', 'session_id'],
        'grade_rules'        => ['school_id', 'grading_system_id', 'session_id'],
        'grading_systems'    => ['school_id', 'class_id', 'semester_id', 'session_id'],
        'marks'              => ['school_id', 'class_id', 'course_id', 'exam_id', 'section_id', 'session_id', 'student_id'],
        'final_marks'        => ['school_id'],
        'exam_halls'         => ['school_id'],
        'exam_schedules'     => ['school_id', 'exam_id'],
        'exam_seat_allocations' => ['school_id'],
        're_exam_applications'  => ['school_id', 'class_id', 'course_id', 'section_id', 'semester_id'],
        'practical_exams'    => ['school_id'],
        'practical_exam_marks' => ['school_id'],
        'online_exams'       => ['school_id'],
        'online_exam_questions' => ['school_id'],
        'online_exam_attempts'  => ['school_id'],
        'online_exam_answers'   => ['school_id'],

        // ── Question Papers ──────────────────────────────────────────────
        'question_paper_templates' => ['school_id'],
        'question_papers'     => ['school_id', 'course_id', 'exam_id', 'section_id'],
        'question_sections'   => ['school_id'],
        'question_questions'  => ['school_id'],
        'question_bank'       => ['school_id'],
        'question_categories' => ['school_id'],
        'question_images'     => ['school_id'],
        'question_versions'   => ['school_id'],
        'question_approvals'  => ['school_id'],
        'question_print_logs' => ['school_id'],
        'question_download_logs' => ['school_id'],
        'question_tags'       => ['school_id'],

        // ── Finance ──────────────────────────────────────────────────────
        'invoices'           => ['school_id'],
        'payments'           => ['school_id'],
        'fee_categories'     => ['school_id'],
        'fee_structures'     => ['school_id'],
        'fee_discounts'      => ['school_id'],
        'student_fee_discounts' => ['school_id'],
        'fee_installments'   => ['school_id'],
        'expenses'           => ['school_id'],
        'income_records'     => ['school_id'],

        // ── Library ──────────────────────────────────────────────────────
        'book_categories'    => ['school_id'],
        'books'              => ['school_id'],
        'book_issues'        => ['school_id'],
        'library_members'    => ['school_id'],
        'ebooks'             => ['school_id'],

        // ── Transport ────────────────────────────────────────────────────
        'vehicles'           => ['school_id'],
        'drivers'            => ['school_id'],
        'transport_routes'   => ['school_id', 'conductor_id'],
        'route_stops'        => ['school_id'],
        'student_transport'  => ['school_id'],
        'transport_attendance' => ['school_id', 'student_id'],
        'fuel_logs'          => ['school_id'],
        'maintenance_logs'   => ['school_id'],

        // ── Hostel ───────────────────────────────────────────────────────
        'hostels'                    => ['school_id'],
        'hostel_rooms'               => ['school_id'],
        'hostel_beds'                => ['school_id'],
        'hostel_allocations'         => ['school_id'],
        'hostel_attendances'         => ['school_id'],
        'hostel_visitors'            => ['school_id'],
        'hostel_maintenance_requests'=> ['school_id'],

        // ── Inventory ────────────────────────────────────────────────────
        'suppliers'           => ['school_id'],
        'asset_categories'    => ['school_id'],
        'assets'              => ['school_id'],
        'inventory_items'     => ['school_id'],
        'purchase_orders'     => ['school_id'],
        'purchase_order_items'=> ['school_id'],
        'stock_transactions'  => ['school_id'],

        // ── Communication ────────────────────────────────────────────────
        'notices'             => ['school_id', 'session_id'],
        'events'              => ['school_id', 'session_id'],
        'conversations'       => ['school_id'],
        'parent_teacher_messages' => ['school_id'],
        'assignments'         => ['school_id', 'class_id', 'course_id', 'section_id', 'semester_id', 'session_id', 'teacher_id'],

        // ── Auth / Logs ──────────────────────────────────────────────────
        'audit_logs'          => ['school_id'],
        'media_files'         => ['school_id'],
        'two_factor_auth'     => ['school_id'],
        'rfid_cards'          => ['school_id'],
        'rfid_scans'          => ['school_id', 'attendance_id'],
        'biometric_devices'   => ['school_id'],

        // ── Communication logs ───────────────────────────────────────────
        'sms_logs'            => ['school_id'],
        'email_logs'          => ['school_id'],
    ];

    public function up(): void
    {
        $db = config('database.connections.' . config('database.default') . '.database');

        foreach ($this->indexes as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) use ($table, $columns, $db) {
                foreach ($columns as $column) {
                    if (!Schema::hasColumn($table, $column)) {
                        continue;
                    }

                    $indexName = "{$table}_{$column}_m21_idx";

                    // Skip if this index (or any index on this column) already exists
                    $exists = DB::selectOne(
                        "SELECT COUNT(*) as cnt
                         FROM INFORMATION_SCHEMA.STATISTICS
                         WHERE TABLE_SCHEMA = ?
                           AND TABLE_NAME   = ?
                           AND COLUMN_NAME  = ?
                           AND SEQ_IN_INDEX = 1",
                        [$db, $table, $column]
                    );

                    if ($exists && $exists->cnt > 0) {
                        continue;
                    }

                    $t->index($column, $indexName);
                }
            });
        }
    }

    public function down(): void
    {
        $db = config('database.connections.' . config('database.default') . '.database');

        foreach ($this->indexes as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) use ($table, $columns, $db) {
                foreach ($columns as $column) {
                    $indexName = "{$table}_{$column}_m21_idx";

                    $exists = DB::selectOne(
                        "SELECT COUNT(*) as cnt
                         FROM INFORMATION_SCHEMA.STATISTICS
                         WHERE TABLE_SCHEMA = ?
                           AND TABLE_NAME   = ?
                           AND INDEX_NAME   = ?",
                        [$db, $table, $indexName]
                    );

                    if ($exists && $exists->cnt > 0) {
                        $t->dropIndex($indexName);
                    }
                }
            });
        }
    }
};
