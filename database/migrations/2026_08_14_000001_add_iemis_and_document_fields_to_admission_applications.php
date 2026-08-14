<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admission_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('admission_applications', 'iemis_id')) {
                $table->string('iemis_id', 50)->nullable()->after('application_number');
            }
            if (!Schema::hasColumn('admission_applications', 'national_id')) {
                $table->string('national_id', 100)->nullable()->after('iemis_id');
            }
            if (!Schema::hasColumn('admission_applications', 'birth_certificate_doc')) {
                $table->string('birth_certificate_doc', 255)->nullable()->after('previous_class');
            }
            if (!Schema::hasColumn('admission_applications', 'ethnicity')) {
                $table->string('ethnicity', 50)->nullable()->after('religion');
            }
            if (!Schema::hasColumn('admission_applications', 'mother_tongue')) {
                $table->string('mother_tongue', 50)->nullable()->after('ethnicity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('admission_applications', function (Blueprint $table) {
            $table->dropColumn(['iemis_id', 'national_id', 'birth_certificate_doc', 'ethnicity', 'mother_tongue']);
        });
    }
};
