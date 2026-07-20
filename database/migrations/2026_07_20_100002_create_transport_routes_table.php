<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransportRoutesTable extends Migration
{
    public function up()
    {
        Schema::create('transport_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // "Route A – North Zone"
            $table->string('code', 20)->unique()->nullable();// "RT-001"
            $table->text('description')->nullable();
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->unsignedBigInteger('conductor_id')->nullable(); // optional second person
            $table->time('morning_departure')->nullable();
            $table->time('morning_arrival')->nullable();
            $table->time('afternoon_departure')->nullable();
            $table->time('afternoon_arrival')->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->decimal('monthly_fee', 10, 2)->default(0.00);
            $table->enum('status', ['active', 'suspended', 'discontinued'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('set null');
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('set null');
            $table->index('status');
            $table->index('vehicle_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transport_routes');
    }
}
