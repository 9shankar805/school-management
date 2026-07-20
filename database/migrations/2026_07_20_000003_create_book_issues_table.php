<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookIssuesTable extends Migration
{
    public function up()
    {
        Schema::create('book_issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('member_id');   // library_members.id
            $table->unsignedBigInteger('issued_by');   // users.id (librarian)
            $table->unsignedBigInteger('returned_to')->nullable(); // users.id
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->enum('status', ['issued', 'returned', 'overdue', 'lost'])->default('issued');
            $table->integer('overdue_days')->default(0);
            $table->decimal('fine_per_day', 8, 2)->default(1.00);
            $table->decimal('fine_amount', 10, 2)->default(0.00);
            $table->enum('fine_status', ['none', 'pending', 'waived', 'paid'])->default('none');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('library_members')->onDelete('cascade');
            $table->foreign('issued_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('returned_to')->references('id')->on('users')->onDelete('set null');

            $table->index('book_id');
            $table->index('member_id');
            $table->index('status');
            $table->index('due_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('book_issues');
    }
}
