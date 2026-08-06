<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeeStructuresTable extends Migration
{
    public function up()
    {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->decimal('amount', 12, 2);
            $table->enum('frequency', ['once', 'monthly', 'quarterly', 'half_yearly', 'yearly'])->default('monthly');
            $table->date('due_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('fee_categories')->onDelete('cascade');
            $table->foreign('session_id')->references('id')->on('school_sessions')->onDelete('set null');
            $table->foreign('class_id')->references('id')->on('school_classes')->onDelete('set null');
            $table->index(['session_id', 'class_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('fee_structures');
    }
}
