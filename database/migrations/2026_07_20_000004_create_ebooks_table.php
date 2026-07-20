<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEbooksTable extends Migration
{
    public function up()
    {
        Schema::create('ebooks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('isbn')->nullable();
            $table->text('description')->nullable();
            $table->string('file_path');       // stored in storage/app/ebooks/
            $table->string('cover_image')->nullable();
            $table->string('file_type', 20)->default('pdf'); // pdf, epub, mobi
            $table->bigInteger('file_size')->nullable();     // bytes
            $table->integer('pages')->nullable();
            $table->year('publication_year')->nullable();
            $table->string('publisher')->nullable();
            $table->enum('access_level', ['public', 'members_only', 'restricted'])->default('members_only');
            $table->boolean('is_active')->default(true);
            $table->integer('download_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('book_categories')->onDelete('set null');
            $table->index('category_id');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ebooks');
    }
}
