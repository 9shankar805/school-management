<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobPostingsTable extends Migration
{
    public function up()
    {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('department')->nullable();
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->integer('vacancies')->default(1);
            $table->decimal('salary_min', 10, 2)->nullable();
            $table->decimal('salary_max', 10, 2)->nullable();
            $table->date('application_deadline')->nullable();
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->unsignedBigInteger('posted_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('posted_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['status', 'application_deadline']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('job_postings');
    }
}
