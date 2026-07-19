<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditLogsTable extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('user_type', 60)->nullable();          // App\Models\User
            $table->string('event', 30)->index();                  // created|updated|deleted|restored|login|logout
            $table->string('auditable_type', 100)->nullable()->index(); // model class
            $table->unsignedBigInteger('auditable_id')->nullable()->index();
            $table->json('old_values')->nullable();                // before state
            $table->json('new_values')->nullable();                // after state
            $table->string('url')->nullable();                     // request URL
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('tags')->nullable();                    // csv tags for filtering
            $table->timestamps();

            // Composite index for polymorphic lookups
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
}
