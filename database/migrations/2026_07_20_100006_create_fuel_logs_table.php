<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFuelLogsTable extends Migration
{
    public function up()
    {
        Schema::create('fuel_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->date('date');
            $table->decimal('litres', 8, 2);
            $table->decimal('cost_per_litre', 8, 2);
            $table->decimal('total_cost', 10, 2);
            $table->integer('odometer_reading')->nullable(); // km at time of fill
            $table->string('fuel_station')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['vehicle_id', 'date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('fuel_logs');
    }
}
