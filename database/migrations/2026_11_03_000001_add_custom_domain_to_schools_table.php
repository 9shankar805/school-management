<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds a dedicated `custom_domain` column so a principal can publish their
     * school portal under their own domain. The field is unique (two schools
     * cannot claim the same domain) and nullable (most schools use the default
     * platform subdomain). Tenant resolution + routing honour it automatically.
     */
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (!Schema::hasColumn('schools', 'custom_domain')) {
                $table->string('custom_domain')->nullable()->unique()->after('website');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (Schema::hasColumn('schools', 'custom_domain')) {
                $table->dropUnique(['custom_domain']);
                $table->dropColumn('custom_domain');
            }
        });
    }
};
