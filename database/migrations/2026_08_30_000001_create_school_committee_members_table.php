<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * School Management Committee (SMC) members table.
 *
 * Per Nepal Education Regulations 2026 (July amendment), SMC members
 * MUST NOT be active members of any political party. The boolean flag
 * `no_political_party_member` captures the signed self-declaration,
 * and supporting ID documents (citizenship, voter ID, declaration form)
 * are uploaded as polymorphic MediaFile records (collection: smc_id_proof).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_committee_members', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('school_id')->index();
            $table->foreign('school_id')->references('id')->on('schools')
                  ->onDelete('cascade');

            // Member identity
            $table->string('full_name', 200);
            $table->enum('position', [
                'chairperson',
                'vice_chairperson',
                'secretary',
                'treasurer',
                'member',
                'teacher_representative',
                'parent_representative',
                'community_representative',
                'student_representative',
                'other',
            ])->default('member');

            // Contact
            $table->string('phone', 20)->nullable();
            $table->string('email', 200)->nullable();

            // Nepal legal: 2026 July Education Regs — no political party member
            $table->boolean('no_political_party_member')->default(false)
                  ->comment('Self-declaration: member is not active in any political party');
            $table->timestamp('declaration_signed_at')->nullable()
                  ->comment('When the no-political-party declaration was submitted');

            // Citizenship / voter ID number (for audit / MoEST IEMIS reporting)
            $table->string('citizenship_no', 50)->nullable();
            $table->string('voter_id_no', 50)->nullable();

            // Tenure
            $table->date('appointed_at')->nullable();
            $table->date('term_ends_at')->nullable();

            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'position']);
            $table->index('no_political_party_member');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_committee_members');
    }
};
