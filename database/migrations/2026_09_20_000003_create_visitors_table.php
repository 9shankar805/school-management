<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->default(1);
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('id_type')->nullable(); // citizenship, national_id, license, passport, other
            $table->string('id_number')->nullable();
            $table->string('organization')->nullable();
            $table->string('purpose'); // admission_inquiry, fee_payment, meeting_principal, meeting_teacher, vendor, maintenance, other
            $table->string('person_to_meet')->nullable();
            $table->string('badge_number')->nullable();
            $table->timestamp('check_in_at');
            $table->timestamp('check_out_at')->nullable();
            $table->string('status')->default('active'); // active, checked_out
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'check_in_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
