<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('head_id')->nullable(); // User (teacher) as dept head
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('head_id')->references('id')->on('users')->nullOnDelete();
        });
        Schema::create('department_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->unique(['department_id', 'user_id']);
            $table->foreign('department_id')->references('id')->on('departments')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('department_user');
        Schema::dropIfExists('departments');
    }
};
