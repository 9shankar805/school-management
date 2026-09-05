<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Module 22 — Security: encrypted sensitive fields
 *
 * Changes:
 *   users            + national_id (longText), must_change_password (bool)
 *   medical_records  PHI columns widened to longText for encrypted payloads
 *
 * Note: Uses raw ALTER TABLE instead of ->change() to avoid requiring
 * the doctrine/dbal package on Laravel 8.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── users ──────────────────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'national_id')) {
                $table->longText('national_id')->nullable()->after('role');
            }
            if (! Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')->default(false)->after('national_id');
            }
        });

        // ── medical_records — widen to longText via raw SQL (no doctrine/dbal needed) ──
        $mrCols = [
            'allergies', 'chronic_conditions', 'medications',
            'vaccination_history', 'special_needs', 'emergency_medical_notes',
            'doctor_name', 'doctor_phone',
        ];

        $existing = Schema::getColumnListing('medical_records');

        if (DB::getDriverName() === 'mysql') {
            foreach ($mrCols as $col) {
                if (in_array($col, $existing)) {
                    DB::statement("ALTER TABLE `medical_records` MODIFY `{$col}` LONGTEXT NULL");
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['national_id', 'must_change_password'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        if (DB::getDriverName() === 'mysql') {
            // Revert to text/string — data may be truncated if encrypted payloads stored
            $existing = Schema::getColumnListing('medical_records');
            $textCols = ['allergies', 'chronic_conditions', 'medications',
                         'vaccination_history', 'special_needs', 'emergency_medical_notes'];
            foreach ($textCols as $col) {
                if (in_array($col, $existing)) {
                    DB::statement("ALTER TABLE `medical_records` MODIFY `{$col}` TEXT NULL");
                }
            }
            foreach (['doctor_name', 'doctor_phone'] as $col) {
                if (in_array($col, $existing)) {
                    DB::statement("ALTER TABLE `medical_records` MODIFY `{$col}` VARCHAR(191) NULL");
                }
            }
        }
    }
};
