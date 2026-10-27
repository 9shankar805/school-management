<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('notices', function (Blueprint $table) {
            if (!Schema::hasColumn('notices', 'title')) {
                $table->string('title')->nullable()->after('school_id');
            }
            if (!Schema::hasColumn('notices', 'priority')) {
                $table->string('priority')->default('normal')->after('title'); // normal, urgent, holiday, exam
            }
            if (!Schema::hasColumn('notices', 'audience')) {
                $table->string('audience')->default('all')->after('priority'); // all, parents, students, teachers
            }
            if (!Schema::hasColumn('notices', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('audience');
            }
            if (!Schema::hasColumn('notices', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('notice');
            }
            if (!Schema::hasColumn('notices', 'published_by')) {
                $table->unsignedBigInteger('published_by')->nullable()->after('attachment_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notices', function (Blueprint $table) {
            $cols = ['title', 'priority', 'audience', 'expiry_date', 'attachment_path', 'published_by'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('notices', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
