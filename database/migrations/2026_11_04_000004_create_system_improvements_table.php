<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('system_improvements')) {
            Schema::create('system_improvements', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('category')->default('general'); // billing, schools, academics, attendance, exams, ui_ux, security, mobile_app, general
                $table->string('priority')->default('medium');   // low, medium, high, urgent
                $table->string('status')->default('remaining');  // remaining, in_progress, done
                $table->date('target_date')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_improvements');
    }
};

