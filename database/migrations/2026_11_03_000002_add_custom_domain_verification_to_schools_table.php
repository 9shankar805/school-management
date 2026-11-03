<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds the fields powering the guided "Custom Domain" onboarding flow:
     *  - custom_domain_token:      random string the principal places in a
     *                              TXT record (_nshl-verify.<domain>) to prove
     *                              they control the domain (ownership check).
     *  - custom_domain_verified_at: timestamp set automatically the first time
     *                              DNS verification passes. The domain only
     *                              starts serving the school portal after this.
     */
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (! Schema::hasColumn('schools', 'custom_domain_token')) {
                $table->string('custom_domain_token')->nullable()->after('custom_domain');
            }
            if (! Schema::hasColumn('schools', 'custom_domain_verified_at')) {
                $table->timestamp('custom_domain_verified_at')->nullable()->after('custom_domain_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (Schema::hasColumn('schools', 'custom_domain_verified_at')) {
                $table->dropColumn('custom_domain_verified_at');
            }
            if (Schema::hasColumn('schools', 'custom_domain_token')) {
                $table->dropColumn('custom_domain_token');
            }
        });
    }
};
