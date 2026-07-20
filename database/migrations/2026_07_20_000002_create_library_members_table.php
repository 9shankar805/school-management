<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLibraryMembersTable extends Migration
{
    public function up()
    {
        Schema::create('library_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('member_type'); // student | staff | teacher
            $table->string('card_number')->unique();
            $table->date('membership_start');
            $table->date('membership_end')->nullable();
            $table->enum('status', ['active', 'suspended', 'expired'])->default('active');
            $table->integer('max_books')->default(3);
            $table->integer('loan_days')->default(14);
            $table->decimal('outstanding_fine', 10, 2)->default(0.00);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
            $table->index('card_number');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('library_members');
    }
}
