<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffExitRecordsTable extends Migration
{
    public function up()
    {
        Schema::create('staff_exit_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('exit_type', ['resignation', 'termination', 'retirement', 'contract_end', 'other']);
            $table->date('notice_date');
            $table->date('last_working_date');
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('clearance_done')->default(false);
            $table->boolean('dues_settled')->default(false);
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('staff_exit_records');
    }
}
