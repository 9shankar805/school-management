<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHostelVisitorsTable extends Migration
{
    public function up()
    {
        Schema::create('hostel_visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('visitor_name');
            $table->string('relation');
            $table->date('date');
            $table->time('in_time');
            $table->time('out_time')->nullable();
            $table->text('purpose')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hostel_visitors');
    }
}
