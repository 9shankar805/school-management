<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();

            // Identity
            $table->string('name');
            $table->string('slug')->unique();           // used in subdomain: slug.nepalschoolhub.com
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->default('Nepal');
            $table->string('logo')->nullable();         // storage path
            $table->string('website')->nullable();
            $table->text('description')->nullable();

            // Plan & billing
            $table->enum('plan', ['free', 'starter', 'growth', 'enterprise'])->default('free');
            $table->integer('max_students')->default(100);
            $table->integer('max_teachers')->default(20);
            $table->integer('max_staff')->default(10);
            $table->boolean('ai_features_enabled')->default(false);
            $table->date('trial_ends_at')->nullable();
            $table->date('plan_expires_at')->nullable();

            // Status & lifecycle
            $table->enum('status', ['pending', 'active', 'suspended', 'cancelled'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable(); // super-admin user id

            // Owner / primary contact
            $table->unsignedBigInteger('owner_id')->nullable();    // FK to users.id (set after user created)
            $table->string('contact_person')->nullable();
            $table->string('contact_email')->nullable();

            // Settings (JSON bag for per-school preferences)
            $table->json('settings')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('status');
            $table->index('plan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
