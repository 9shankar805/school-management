<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Question Paper Maker — all 13 tables in a single migration.
 * Tables are created in dependency order so FKs resolve correctly.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. question_categories ─────────────────────────────────────────────
        Schema::create('question_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject')->nullable();   // e.g. Mathematics, Science
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable(); // subcategory support
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('question_categories')->nullOnDelete();
        });

        // ── 2. question_tags ───────────────────────────────────────────────────
        Schema::create('question_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('color', 7)->default('#6366f1'); // hex colour for UI badge
            $table->timestamps();
        });

        // ── 3. question_paper_templates ────────────────────────────────────────
        Schema::create('question_paper_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            // Header / meta fields (support {{placeholders}})
            $table->string('school_name')->nullable();
            $table->string('school_logo_path')->nullable();
            $table->text('school_address')->nullable();
            $table->string('exam_name_placeholder')->default('{{exam_name}}');
            $table->string('subject_placeholder')->default('{{subject}}');
            $table->string('class_placeholder')->default('{{class}}');
            $table->string('time_placeholder')->default('{{time}}');
            $table->string('full_marks_placeholder')->default('{{full_marks}}');
            $table->string('pass_marks_placeholder')->default('{{pass_marks}}');
            $table->string('date_placeholder')->default('{{date}}');
            // Full HTML/JSON layout of the header + instructions block
            $table->longText('header_html')->nullable();
            $table->longText('instructions_html')->nullable();
            $table->longText('footer_html')->nullable();
            // Signature area text
            $table->string('signature_name')->nullable();
            $table->string('signature_title')->nullable();
            // Paper settings
            $table->enum('paper_size', ['A4', 'Letter'])->default('A4');
            $table->enum('orientation', ['portrait', 'landscape'])->default('portrait');
            $table->boolean('show_watermark')->default(false);
            $table->string('watermark_text')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        // ── 4. question_bank ───────────────────────────────────────────────────
        Schema::create('question_bank', function (Blueprint $table) {
            $table->id();
            $table->enum('question_type', [
                'essay', 'mcq', 'fill_blank', 'true_false',
                'match', 'short_answer', 'long_answer',
                'diagram', 'case_study', 'programming',
                'practical', 'numerical',
            ])->default('short_answer');
            $table->longText('question_text');       // HTML (Tiptap output)
            $table->longText('answer_text')->nullable();
            $table->json('options')->nullable();     // MCQ choices array
            $table->string('correct_answer')->nullable();
            $table->float('allocated_marks')->default(1);
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->string('subject')->nullable();
            $table->string('chapter')->nullable();
            $table->string('bloom_taxonomy')->nullable(); // remember/understand/apply/analyse/evaluate/create
            $table->string('learning_outcome')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['subject', 'chapter']);
            $table->index('question_type');
            $table->index('difficulty');

            $table->foreign('category_id')->references('id')->on('question_categories')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        // ── 5. question_bank_tag (pivot) ───────────────────────────────────────
        Schema::create('question_bank_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('question_bank_id');
            $table->unsignedBigInteger('question_tag_id');
            $table->primary(['question_bank_id', 'question_tag_id']);
            $table->foreign('question_bank_id')->references('id')->on('question_bank')->cascadeOnDelete();
            $table->foreign('question_tag_id')->references('id')->on('question_tags')->cascadeOnDelete();
        });

        // ── 6. question_papers ─────────────────────────────────────────────────
        Schema::create('question_papers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('template_id')->nullable();
            $table->unsignedInteger('exam_id')->nullable();     // link to existing Exam
            $table->unsignedInteger('class_id')->nullable();
            $table->unsignedInteger('section_id')->nullable();
            $table->unsignedInteger('course_id')->nullable();
            $table->unsignedInteger('session_id')->nullable();
            // Resolved template values (substituted placeholders)
            $table->string('exam_name')->nullable();
            $table->string('subject')->nullable();
            $table->string('class_label')->nullable();
            $table->string('duration')->nullable();             // e.g. "3 Hours"
            $table->float('full_marks')->default(0);
            $table->float('pass_marks')->default(0);
            $table->date('exam_date')->nullable();
            // Paper settings override
            $table->enum('paper_size', ['A4', 'Letter'])->default('A4');
            $table->enum('orientation', ['portrait', 'landscape'])->default('portrait');
            // Approval workflow
            $table->enum('status', [
                'draft', 'submitted', 'reviewed', 'approved', 'locked', 'printed'
            ])->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['class_id', 'session_id']);
            $table->index('status');

            $table->foreign('template_id')->references('id')->on('question_paper_templates')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });

        // ── 7. question_sections ───────────────────────────────────────────────
        Schema::create('question_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paper_id');
            $table->string('title');                            // e.g. "Section A", "Group B"
            $table->text('instructions')->nullable();
            $table->float('total_marks')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['paper_id', 'sort_order']);
            $table->foreign('paper_id')->references('id')->on('question_papers')->cascadeOnDelete();
        });

        // ── 8. question_questions ──────────────────────────────────────────────
        Schema::create('question_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('bank_id')->nullable(); // from question_bank
            $table->enum('question_type', [
                'essay', 'mcq', 'fill_blank', 'true_false',
                'match', 'short_answer', 'long_answer',
                'diagram', 'case_study', 'programming',
                'practical', 'numerical',
            ])->default('short_answer');
            $table->longText('question_text');          // HTML (Tiptap)
            $table->longText('answer_text')->nullable();
            $table->json('options')->nullable();        // MCQ choices
            $table->string('correct_answer')->nullable();
            $table->float('allocated_marks')->default(1);
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->string('chapter')->nullable();
            $table->string('bloom_taxonomy')->nullable();
            $table->string('numbering')->nullable();    // auto-assigned: "1", "1.1", "A", "i"
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['section_id', 'sort_order']);
            $table->foreign('section_id')->references('id')->on('question_sections')->cascadeOnDelete();
            $table->foreign('bank_id')->references('id')->on('question_bank')->nullOnDelete();
        });

        // ── 9. question_images ─────────────────────────────────────────────────
        Schema::create('question_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id')->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->unsignedInteger('file_size')->default(0);  // bytes
            $table->string('mime_type', 50)->nullable();
            $table->string('caption')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            $table->foreign('question_id')->references('id')->on('question_questions')->nullOnDelete();
            $table->foreign('bank_id')->references('id')->on('question_bank')->nullOnDelete();
            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
        });

        // ── 10. question_versions ──────────────────────────────────────────────
        Schema::create('question_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paper_id');
            $table->unsignedSmallInteger('version_number')->default(1);
            $table->json('snapshot');                   // full paper JSON snapshot
            $table->text('change_summary')->nullable(); // what changed
            $table->unsignedBigInteger('saved_by')->nullable();
            $table->timestamps();

            $table->index(['paper_id', 'version_number']);
            $table->foreign('paper_id')->references('id')->on('question_papers')->cascadeOnDelete();
            $table->foreign('saved_by')->references('id')->on('users')->nullOnDelete();
        });

        // ── 11. question_approvals ─────────────────────────────────────────────
        Schema::create('question_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paper_id');
            $table->unsignedBigInteger('reviewer_id');
            $table->enum('action', ['submitted', 'reviewed', 'approved', 'rejected', 'locked'])->default('submitted');
            $table->text('comments')->nullable();
            $table->timestamp('actioned_at')->useCurrent();
            $table->timestamps();

            $table->index('paper_id');
            $table->foreign('paper_id')->references('id')->on('question_papers')->cascadeOnDelete();
            $table->foreign('reviewer_id')->references('id')->on('users')->cascadeOnDelete();
        });

        // ── 12. question_print_logs ────────────────────────────────────────────
        Schema::create('question_print_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paper_id');
            $table->unsignedBigInteger('printed_by');
            $table->unsignedSmallInteger('copies')->default(1);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->foreign('paper_id')->references('id')->on('question_papers')->cascadeOnDelete();
            $table->foreign('printed_by')->references('id')->on('users')->cascadeOnDelete();
        });

        // ── 13. question_download_logs ─────────────────────────────────────────
        Schema::create('question_download_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paper_id');
            $table->unsignedBigInteger('downloaded_by');
            $table->enum('format', ['pdf', 'docx'])->default('pdf');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->foreign('paper_id')->references('id')->on('question_papers')->cascadeOnDelete();
            $table->foreign('downloaded_by')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_download_logs');
        Schema::dropIfExists('question_print_logs');
        Schema::dropIfExists('question_approvals');
        Schema::dropIfExists('question_versions');
        Schema::dropIfExists('question_images');
        Schema::dropIfExists('question_questions');
        Schema::dropIfExists('question_sections');
        Schema::dropIfExists('question_papers');
        Schema::dropIfExists('question_bank_tag');
        Schema::dropIfExists('question_bank');
        Schema::dropIfExists('question_paper_templates');
        Schema::dropIfExists('question_tags');
        Schema::dropIfExists('question_categories');
    }
};
