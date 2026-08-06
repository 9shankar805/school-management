<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificate_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('certificate_templates', 'background_image_path')) {
                $table->string('background_image_path')->nullable()->after('signature_image_path');
            }
            if (!Schema::hasColumn('certificate_templates', 'builder_mode')) {
                $table->boolean('builder_mode')->default(false)->after('background_image_path');
            }
            if (!Schema::hasColumn('certificate_templates', 'builder_layout')) {
                $table->json('builder_layout')->nullable()->after('builder_mode');
            }
            if (!Schema::hasColumn('certificate_templates', 'editable_fields')) {
                $table->json('editable_fields')->nullable()->after('builder_layout');
            }
        });
    }

    public function down(): void
    {
        Schema::table('certificate_templates', function (Blueprint $table) {
            $table->dropColumn(['editable_fields', 'builder_layout', 'builder_mode', 'background_image_path']);
        });
    }
};
