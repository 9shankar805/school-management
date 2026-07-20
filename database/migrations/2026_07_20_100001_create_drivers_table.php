<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriversTable extends Migration
{
    public function up()
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('employee_id')->nullable()->unique();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('license_number')->unique();
            $table->string('license_type', 50)->nullable();  // HMV, LMV etc.
            $table->date('license_expiry')->nullable();
            $table->string('national_id')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedBigInteger('current_vehicle_id')->nullable();
            $table->enum('status', ['active', 'on_leave', 'terminated'])->default('active');
            $table->decimal('salary', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('current_vehicle_id')->references('id')->on('vehicles')->onDelete('set null');
            $table->index('status');
            $table->index('license_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('drivers');
    }
}
