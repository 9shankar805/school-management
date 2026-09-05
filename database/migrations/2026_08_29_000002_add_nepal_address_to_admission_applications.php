<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add Nepal-specific three-tier address fields to admission_applications.
 *
 * Nepal's administrative structure:
 *   Province (प्रदेश) → District (जिल्ला) → Municipality/VDC (पालिका) → Ward (वडा)
 *
 * permanent_address = free-text village/tole description
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admission_applications', function (Blueprint $table) {
            $table->string('province',          100)->nullable()->after('address');
            $table->string('district',          100)->nullable()->after('province');
            $table->string('municipality',      150)->nullable()->after('district')
                  ->comment('Municipality / Rural Municipality / VDC');
            $table->string('ward_no',           10)->nullable()->after('municipality');
            $table->string('permanent_address', 500)->nullable()->after('ward_no')
                  ->comment('Village / Tole — free-text permanent address');
        });
    }

    public function down(): void
    {
        Schema::table('admission_applications', function (Blueprint $table) {
            $table->dropColumn(['province', 'district', 'municipality', 'ward_no', 'permanent_address']);
        });
    }
};
