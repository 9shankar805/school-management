<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Null = super-admin (platform-level, no school)
            $table->unsignedBigInteger('school_id')
                  ->nullable()
                  ->after('id')
                  ->comment('Tenant FK — null for super-admins');

            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['school_id']);
            $table->dropColumn('school_id');
        });
    }
};
