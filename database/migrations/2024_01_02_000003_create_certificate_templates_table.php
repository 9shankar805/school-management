<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['completion', 'merit', 'participation', 'graduation', 'transfer', 'custom'])->default('completion');
            $table->text('body_text');         // Supports {{student_name}}, {{class}}, {{date}} tokens
            $table->string('header_text')->nullable();
            $table->string('footer_text')->nullable();
            $table->string('signature_name')->nullable();
            $table->string('signature_title')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('certificate_templates'); }
};
