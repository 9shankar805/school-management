<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransportAttendanceTable extends Migration
{
    public function up()
    {
        Schema::create('transport_attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('route_id');
            $table->date('date');
            $table->enum('trip', ['morning', 'afternoon']);
            $table->enum('status', ['present', 'absent', 'late'])->default('present');
            $table->time('actual_time')->nullable();          // actual pickup/dropoff time
            $table->unsignedBigInteger('marked_by')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('cascade');
            $table->foreign('marked_by')->references('id')->on('users')->onDelete('set null');

            $table->unique(['student_id', 'route_id', 'date', 'trip'], 'transport_att_unique');
            $table->index(['route_id', 'date']);
            $table->index('date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transport_attendance');
    }
}
