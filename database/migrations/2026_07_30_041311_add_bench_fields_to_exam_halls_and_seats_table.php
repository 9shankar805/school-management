<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('exam_halls')) {
            Schema::table('exam_halls', function (Blueprint $table) {
                if (!Schema::hasColumn('exam_halls', 'columns')) {
                    $table->integer('columns')->default(4)->after('capacity');
                }
                if (!Schema::hasColumn('exam_halls', 'benches_per_column')) {
                    $table->integer('benches_per_column')->default(5)->after('columns');
                }
                if (!Schema::hasColumn('exam_halls', 'students_per_bench')) {
                    $table->integer('students_per_bench')->default(2)->after('benches_per_column'); // 1, 2, or 3
                }
            });
        }

        if (Schema::hasTable('exam_seat_allocations')) {
            Schema::table('exam_seat_allocations', function (Blueprint $table) {
                if (!Schema::hasColumn('exam_seat_allocations', 'column_no')) {
                    $table->integer('column_no')->nullable()->after('seat_number');
                }
                if (!Schema::hasColumn('exam_seat_allocations', 'bench_no')) {
                    $table->integer('bench_no')->nullable()->after('column_no');
                }
                if (!Schema::hasColumn('exam_seat_allocations', 'bench_position')) {
                    $table->integer('bench_position')->nullable()->after('bench_no'); // 1, 2, or 3
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('exam_halls')) {
            Schema::table('exam_halls', function (Blueprint $table) {
                $table->dropColumn(['columns', 'benches_per_column', 'students_per_bench']);
            });
        }

        if (Schema::hasTable('exam_seat_allocations')) {
            Schema::table('exam_seat_allocations', function (Blueprint $table) {
                $table->dropColumn(['column_no', 'bench_no', 'bench_position']);
            });
        }
    }
};
