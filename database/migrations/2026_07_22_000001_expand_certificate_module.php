<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        // ── 1. Expand the certificate_templates table ───────────────────────
        // Convert enum → varchar with raw SQL (avoids doctrine/dbal requirement)
        DB::statement('ALTER TABLE certificate_templates MODIFY COLUMN `type` VARCHAR(80) NOT NULL DEFAULT \'custom\'');

        Schema::table('certificate_templates', function (Blueprint $table) {
            // Bilingual body text
            $table->text('body_text_ne')->nullable()->after('body_text');

            // Branding & layout
            $table->string('logo_path')->nullable()->after('is_active');
            $table->string('signature_image_path')->nullable()->after('logo_path');
            $table->string('paper_size', 20)->default('A4')->after('signature_image_path');
            $table->string('orientation', 15)->default('landscape')->after('paper_size');
            $table->string('language', 10)->default('en')->after('orientation');
            $table->boolean('show_student_photo')->default(false)->after('language');
            $table->boolean('show_qr_code')->default(true)->after('show_student_photo');
            $table->boolean('show_serial_number')->default(true)->after('show_qr_code');
            $table->string('border_style', 30)->default('classic')->after('show_serial_number');
            $table->string('primary_color', 10)->default('#4f46e5')->after('border_style');
            $table->string('secondary_color', 10)->default('#7c3aed')->after('primary_color');
            $table->string('verification_url')->nullable()->after('secondary_color');
        });

        // ── 2. Create issued_certificates table ─────────────────────────────
        Schema::create('issued_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('template_id')->constrained('certificate_templates')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('issued_by')->constrained('users')->cascadeOnDelete();
            $table->string('certificate_number', 60)->unique();
            $table->string('type', 80);
            $table->date('issued_date');
            $table->date('valid_until')->nullable();
            $table->text('extra_notes')->nullable();
            $table->string('purpose')->nullable();
            $table->string('status', 20)->default('issued');
            $table->string('language', 10)->default('en');
            $table->string('paper_size', 20)->default('A4');
            $table->string('verification_token', 64)->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'student_id']);
            $table->index(['school_id', 'type']);
            $table->index('certificate_number');
            $table->index('verification_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issued_certificates');

        Schema::table('certificate_templates', function (Blueprint $table) {
            $table->dropColumn([
                'body_text_ne',
                'logo_path', 'signature_image_path', 'paper_size', 'orientation',
                'language', 'show_student_photo', 'show_qr_code', 'show_serial_number',
                'border_style', 'primary_color', 'secondary_color', 'verification_url',
            ]);
        });

        // Restore original enum (approximate rollback)
        DB::statement("ALTER TABLE certificate_templates MODIFY COLUMN `type` ENUM('completion','merit','participation','graduation','transfer','custom') NOT NULL DEFAULT 'completion'");
    }
};
