<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('homework_submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('homework_submissions', 'annotated_file_path')) {
                $table->string('annotated_file_path')->nullable()->after('file_path');
            }
            if (!Schema::hasColumn('homework_submissions', 'annotations_json')) {
                $table->longText('annotations_json')->nullable()->after('annotated_file_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('homework_submissions', function (Blueprint $table) {
            if (Schema::hasColumn('homework_submissions', 'annotated_file_path')) {
                $table->dropColumn('annotated_file_path');
            }
            if (Schema::hasColumn('homework_submissions', 'annotations_json')) {
                $table->dropColumn('annotations_json');
            }
        });
    }
};
