<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * bus_locations — real-time GPS breadcrumb trail.
 *
 * Each row is one location ping from the driver app.
 * The parent_app BusTrackingController reads the latest row per vehicle.
 * Old rows can be pruned by a scheduled artisan command.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bus_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->index();
            $table->unsignedBigInteger('vehicle_id')->index();
            $table->unsignedBigInteger('driver_id')->nullable()->index();
            $table->unsignedBigInteger('route_id')->nullable()->index();

            // GPS position
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy_meters', 8, 2)->nullable()->comment('GPS accuracy radius in metres');
            $table->decimal('speed_kmh', 6, 2)->nullable()->comment('Speed in km/h reported by device');
            $table->decimal('heading', 6, 2)->nullable()->comment('Bearing 0-360 degrees');
            $table->decimal('altitude_m', 8, 2)->nullable();

            // Trip context
            $table->enum('trip_type', ['morning', 'afternoon', 'other'])->default('morning');
            $table->boolean('is_active')->default(true)->comment('False once the trip ends');

            // Device metadata
            $table->string('device_id', 100)->nullable();
            $table->string('battery_level', 10)->nullable();

            $table->timestamp('recorded_at')->useCurrent()->index()->comment('Device-side timestamp');
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('set null');
            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('set null');

            // Composite index for fast "latest location per vehicle" query
            $table->index(['vehicle_id', 'recorded_at']);
            $table->index(['route_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bus_locations');
    }
};
