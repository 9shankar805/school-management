<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaFilesTable extends Migration
{
    public function up()
    {
        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable()->index();
            $table->unsignedBigInteger('uploaded_by')->nullable()->index(); // user_id
            // Polymorphic: file can belong to a student, teacher, assignment, etc.
            $table->string('model_type')->nullable()->index();
            $table->unsignedBigInteger('model_id')->nullable()->index();
            $table->string('collection', 60)->default('default'); // documents|photos|assignments|library
            $table->string('file_name');
            $table->string('original_name');
            $table->string('mime_type', 100)->nullable();
            $table->string('disk', 30)->default('local');         // local|s3|public
            $table->string('path');                                // relative storage path
            $table->unsignedBigInteger('size')->default(0);       // bytes
            $table->string('extension', 20)->nullable();
            $table->boolean('is_public')->default(false);
            $table->json('custom_properties')->nullable();         // extra metadata
            $table->softDeletes();
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('media_files');
    }
}
