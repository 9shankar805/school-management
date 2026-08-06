<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentFeeDiscountsTable extends Migration
{
    public function up()
    {
        Schema::create('student_fee_discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('discount_id');
            $table->unsignedBigInteger('session_id')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('discount_id')->references('id')->on('fee_discounts')->onDelete('cascade');
            $table->foreign('session_id')->references('id')->on('school_sessions')->onDelete('set null');
            $table->unique(['student_id', 'discount_id', 'session_id'], 'student_discount_session_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_fee_discounts');
    }
}
