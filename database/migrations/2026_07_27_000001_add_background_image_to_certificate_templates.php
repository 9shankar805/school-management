<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificate_templates', function (Blueprint $table) {
            // Guard against duplicate — column may already exist from a previous partial run
            if (! Schema::hasColumn('certificate_templates', 'background_image_path')) {
                $table->string('background_image_path')->nullable()->after('signature_image_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('certificate_templates', function (Blueprint $table) {
            if (Schema::hasColumn('certificate_templates', 'background_image_path')) {
                $table->dropColumn('background_image_path');
            }
        });
    }
};
