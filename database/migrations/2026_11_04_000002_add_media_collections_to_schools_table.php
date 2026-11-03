<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds JSON array columns for the new school media collections:
     *  - achievement_gallery : award & achievement photos (array of paths)
     *  - batch_gallery       : class/batch group photo (array of paths)
     *  - program_gallery     : academic program / department photos (array of paths)
     *
     * Vice-principal, coordinator, and team-photo headshots are stored in the
     * existing `settings` JSON column (no migration needed for those).
     */
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (! Schema::hasColumn('schools', 'achievement_gallery')) {
                $table->json('achievement_gallery')->nullable()->after('gallery_images');
            }
            if (! Schema::hasColumn('schools', 'batch_gallery')) {
                $table->json('batch_gallery')->nullable()->after('achievement_gallery');
            }
            if (! Schema::hasColumn('schools', 'program_gallery')) {
                $table->json('program_gallery')->nullable()->after('batch_gallery');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            foreach (['achievement_gallery', 'batch_gallery', 'program_gallery'] as $col) {
                if (Schema::hasColumn('schools', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
