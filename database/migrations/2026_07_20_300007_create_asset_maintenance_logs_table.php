<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetMaintenanceLogsTable extends Migration
{
    public function up(): void
    {
        Schema::create('asset_maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->enum('type', ['preventive', 'corrective', 'inspection', 'upgrade', 'disposal'])->default('preventive');
            $table->date('maintenance_date');
            $table->date('next_due_date')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('vendor')->nullable();           // external repair vendor
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('completed');
            $table->text('description');
            $table->text('findings')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['asset_id', 'maintenance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_maintenance_logs');
    }
}
