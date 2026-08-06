<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffContractsTable extends Migration
{
    public function up()
    {
        Schema::create('staff_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('contract_type', 100);          // permanent, temporary, probation, contract
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('salary', 12, 2);
            $table->decimal('allowances', 12, 2)->default(0);
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            $table->enum('status', ['active', 'expired', 'terminated'])->default('active');
            $table->string('document_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('staff_contracts');
    }
}
