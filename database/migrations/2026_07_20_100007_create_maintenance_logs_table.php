<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaintenanceLogsTable extends Migration
{
    public function up()
    {
        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->string('type', 100);                     // oil_change|tyre|brake|engine|body|other
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('service_date');
            $table->date('next_service_date')->nullable();
            $table->integer('odometer_reading')->nullable();
            $table->string('service_provider')->nullable();  // garage/mechanic name
            $table->decimal('cost', 10, 2)->default(0.00);
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('completed');
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['vehicle_id', 'service_date']);
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('maintenance_logs');
    }
}
