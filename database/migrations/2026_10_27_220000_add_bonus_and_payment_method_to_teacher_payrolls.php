<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('teacher_payrolls', 'bonus')) {
                $table->decimal('bonus', 12, 2)->default(0)->after('overtime');
            }
            if (!Schema::hasColumn('teacher_payrolls', 'payment_method')) {
                $table->string('payment_method', 30)->nullable()->after('payment_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('teacher_payrolls', function (Blueprint $table) {
            if (Schema::hasColumn('teacher_payrolls', 'bonus')) {
                $table->dropColumn('bonus');
            }
            if (Schema::hasColumn('teacher_payrolls', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
        });
    }
};
