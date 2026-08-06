<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobApplicationsTable extends Migration
{
    public function up()
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_posting_id');
            $table->string('applicant_name');
            $table->string('email');
            $table->string('phone', 30)->nullable();
            $table->string('resume')->nullable();          // file path
            $table->text('cover_letter')->nullable();
            $table->date('interview_date')->nullable();
            $table->time('interview_time')->nullable();
            $table->string('interview_location')->nullable();
            $table->enum('status', ['applied', 'shortlisted', 'interviewed', 'selected', 'rejected'])->default('applied');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('job_posting_id')->references('id')->on('job_postings')->onDelete('cascade');
            $table->index(['job_posting_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('job_applications');
    }
}
