<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesTable extends Migration
{
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // "Bus 01", "Van A"
            $table->string('registration_number')->unique(); // plate number
            $table->string('type', 50)->default('bus');      // bus|van|minibus|car
            $table->string('make')->nullable();              // Toyota, Tata
            $table->string('model')->nullable();
            $table->year('year')->nullable();
            $table->string('color', 50)->nullable();
            $table->integer('capacity')->default(40);        // passenger seats
            $table->string('fuel_type', 30)->default('diesel'); // diesel|petrol|cng|electric
            $table->date('insurance_expiry')->nullable();
            $table->date('fitness_expiry')->nullable();      // roadworthiness cert
            $table->date('permit_expiry')->nullable();
            $table->enum('status', ['active', 'maintenance', 'retired'])->default('active');
            $table->string('gps_device_id')->nullable();     // GPS tracker ID
            $table->decimal('current_lat', 10, 7)->nullable();
            $table->decimal('current_lng', 10, 7)->nullable();
            $table->timestamp('gps_updated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('registration_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
}
