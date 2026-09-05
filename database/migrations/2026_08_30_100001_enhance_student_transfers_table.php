<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enhances student_transfers to support all 3 inter-school scenarios:
 *
 *  Scenario 1 — Same-platform transfer (both schools on this SaaS)
 *               to_school_id FK populated; on approve: student's school_id
 *               is reassigned, new Promotion created at destination school.
 *
 *  Scenario 2 — Outgoing to external school
 *               to_school is a free-text name; tc_token generated on approve;
 *               TC PDF can be printed; student status set to 'transferred'.
 *
 *  Scenario 3 — Incoming from another school
 *               transfer_type = 'incoming'; from_school free-text;
 *               Previous school's tc_token stored for cross-reference.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_transfers', function (Blueprint $table) {

            // ── Scenario 1: destination school FK (nullable — null = external) ──
            $table->unsignedBigInteger('to_school_id')->nullable()->after('from_school');
            $table->unsignedBigInteger('from_school_id')->nullable()->after('from_school');

            // ── Transfer type extended ────────────────────────────────────────
            // We change the enum to also include 'incoming'
            // MySQL enum alter: drop & recreate column
            // We add a new varchar column then drop it after altering enum
            // Because Laravel's change() on enums can be finicky we use raw SQL.

            // ── TC fields ────────────────────────────────────────────────────
            $table->string('tc_token', 64)->unique()->nullable()->after('notes');
            $table->timestamp('tc_issued_at')->nullable()->after('tc_token');
            $table->timestamp('accepted_at')->nullable()->after('tc_issued_at');
            $table->unsignedBigInteger('accepted_by')->nullable()->after('accepted_at');
            $table->string('tc_number', 50)->nullable()->after('accepted_by'); // e.g. TC-2081-001

            // ── Document carry flags (which docs were handed over with TC) ───
            $table->boolean('carry_marksheet')->default(false)->after('tc_number');
            $table->boolean('carry_character')->default(false)->after('carry_marksheet');
            $table->boolean('carry_birth_cert')->default(false)->after('carry_character');
            $table->boolean('carry_medical')->default(false)->after('carry_birth_cert');

            // ── Incoming-specific fields ──────────────────────────────────────
            $table->string('previous_tc_number')->nullable()->after('carry_medical');
            $table->string('previous_school_name')->nullable()->after('previous_tc_number');
            $table->date('joining_date')->nullable()->after('previous_school_name');

            // ── indexes ───────────────────────────────────────────────────────
            $table->index('to_school_id');
            $table->index('from_school_id');
            $table->index('tc_token');
        });

        // Extend transfer_type enum to include 'incoming'
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE student_transfers MODIFY COLUMN transfer_type ENUM('inter_class','inter_section','inter_school','incoming') NOT NULL DEFAULT 'inter_class'");
        }
    }

    public function down(): void
    {
        Schema::table('student_transfers', function (Blueprint $table) {
            $table->dropIndex(['to_school_id']);
            $table->dropIndex(['from_school_id']);
            $table->dropIndex(['tc_token']);
            $table->dropColumn([
                'to_school_id', 'from_school_id',
                'tc_token', 'tc_issued_at', 'accepted_at', 'accepted_by', 'tc_number',
                'carry_marksheet', 'carry_character', 'carry_birth_cert', 'carry_medical',
                'previous_tc_number', 'previous_school_name', 'joining_date',
            ]);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE student_transfers MODIFY COLUMN transfer_type ENUM('inter_class','inter_section','inter_school') NOT NULL DEFAULT 'inter_class'");
        }
    }
};
