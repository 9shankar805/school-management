<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentTransportTable extends Migration
{
    public function up()
    {
        Schema::create('student_transport', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('route_id');
            $table->unsignedBigInteger('stop_id')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('direction', ['both', 'pickup_only', 'dropoff_only'])->default('both');
            $table->decimal('monthly_fee', 10, 2)->default(0.00);
            $table->enum('status', ['active', 'suspended', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('cascade');
            $table->foreign('stop_id')->references('id')->on('route_stops')->onDelete('set null');

            $table->index('student_id');
            $table->index('route_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_transport');
    }
}
