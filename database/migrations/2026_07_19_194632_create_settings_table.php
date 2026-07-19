<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            // Scope: null = global, int = per-school (multi-tenant ready)
            $table->unsignedBigInteger('school_id')->nullable()->index();
            $table->string('group', 60)->default('general')->index();  // general|mail|sms|payment|theme|academic
            $table->string('key', 100)->index();
            $table->text('value')->nullable();
            $table->string('type', 30)->default('string'); // string|boolean|integer|json|file
            $table->string('label')->nullable();           // human-readable label for settings UI
            $table->boolean('is_public')->default(false);  // exposed to frontend JS?
            $table->boolean('is_encrypted')->default(false);
            $table->timestamps();

            $table->unique(['school_id', 'group', 'key']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
