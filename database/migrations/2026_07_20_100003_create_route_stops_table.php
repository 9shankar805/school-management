<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRouteStopsTable extends Migration
{
    public function up()
    {
        Schema::create('route_stops', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('route_id');
            $table->string('name');                           // "Main Gate", "Market Square"
            $table->integer('stop_order');                    // sequence on route
            $table->time('morning_pickup')->nullable();
            $table->time('afternoon_dropoff')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('landmark')->nullable();
            $table->decimal('stop_fee', 10, 2)->default(0.00); // fee override per stop
            $table->timestamps();

            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('cascade');
            $table->index(['route_id', 'stop_order']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('route_stops');
    }
}
