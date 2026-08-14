<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('id_card_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable()->index();
            $table->string('name');
            $table->boolean('is_active')->default(false);

            // ── Header ────────────────────────────────────────────────────
            $table->string('header_bg_color')->default('#1a56db');
            $table->string('header_text_color')->default('#ffffff');
            $table->boolean('show_logo')->default(true);
            $table->boolean('show_school_name')->default(true);
            $table->boolean('show_tagline')->default(true);
            $table->string('tagline_text')->default('Student Identification Card');
            $table->unsignedTinyInteger('header_height')->default(38); // pt

            // ── Accent ────────────────────────────────────────────────────
            $table->string('accent_color')->default('#f59e0b');

            // ── Photo ─────────────────────────────────────────────────────
            $table->boolean('show_photo')->default(true);
            $table->unsignedTinyInteger('photo_size')->default(52);    // pt square
            $table->string('photo_shape')->default('square');          // square | circle
            $table->boolean('show_photo_corner_marks')->default(true);

            // ── Blood badge ───────────────────────────────────────────────
            $table->boolean('show_blood_type')->default(true);

            // ── Body ──────────────────────────────────────────────────────
            $table->string('body_bg_color')->default('#ffffff');
            $table->string('name_text_color')->default('#0f172a');
            $table->unsignedTinyInteger('name_font_size')->default(10); // pt
            $table->boolean('name_uppercase')->default(true);
            $table->boolean('show_role_badge')->default(true);

            // ── Visible fields (JSON array of field keys) ─────────────────
            // Possible: class, section, gender, dob, phone, blood_type, address, roll_no
            $table->json('visible_fields')->nullable();

            // ── Footer ────────────────────────────────────────────────────
            $table->string('footer_bg_color')->default('#f8fafc');
            $table->string('footer_text_color')->default('#1e293b');
            $table->boolean('show_id_number')->default(true);
            $table->boolean('show_session')->default(true);
            $table->boolean('show_qr')->default(true);

            // ── Custom text overlays ──────────────────────────────────────
            $table->string('footer_left_text')->nullable();    // e.g. school phone
            $table->string('footer_center_text')->nullable();  // e.g. school website
            $table->boolean('show_signature_line')->default(false);
            $table->string('signature_label')->nullable();     // e.g. "Principal"

            // ── Background ───────────────────────────────────────────────
            $table->string('background_image_path')->nullable();
            $table->unsignedTinyInteger('background_opacity')->default(10); // %

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('id_card_templates');
    }
};
