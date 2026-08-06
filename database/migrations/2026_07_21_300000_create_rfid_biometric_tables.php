<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * RFID / Biometric attendance integration tables:
 *
 *  - rfid_cards          — maps RFID card UIDs to users (students + staff)
 *  - biometric_devices   — registered scanners / readers
 *  - rfid_scans          — raw scan events received from devices
 */
class CreateRfidBiometricTables extends Migration
{
    public function up(): void
    {
        // ── RFID Cards ────────────────────────────────────────────────────────
        Schema::create('rfid_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('card_uid', 64)->unique();   // RFID UID from reader
            $table->string('card_type', 20)->default('rfid'); // rfid | nfc | barcode
            $table->boolean('is_active')->default(true);
            $table->dateTime('issued_at')->useCurrent();
            $table->date('expires_at')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->index('card_uid');
        });

        // ── Biometric Devices ─────────────────────────────────────────────────
        Schema::create('biometric_devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('serial_number')->unique();
            $table->string('location')->nullable();   // e.g. "Main Gate", "Library"
            $table->enum('type', ['rfid', 'fingerprint', 'face', 'iris', 'barcode'])->default('rfid');
            $table->string('ip_address', 45)->nullable();
            $table->string('api_key', 64)->unique();  // device authenticates with this key
            $table->boolean('is_active')->default(true);
            $table->dateTime('last_seen_at')->nullable();
            $table->timestamps();
        });

        // ── RFID / Biometric Scan Log ──────────────────────────────────────────
        Schema::create('rfid_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('biometric_devices')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // resolved from card_uid
            $table->string('card_uid', 64)->nullable();        // raw UID from device
            $table->string('biometric_template_id')->nullable(); // for fingerprint/face
            $table->dateTime('scanned_at');
            $table->enum('direction', ['in', 'out', 'unknown'])->default('unknown');
            $table->boolean('attendance_marked')->default(false); // whether this scan created an Attendance record
            $table->unsignedBigInteger('attendance_id')->nullable(); // FK to attendances table
            $table->string('raw_payload')->nullable(); // raw JSON from device
            $table->timestamps();
            $table->index(['device_id', 'scanned_at']);
            $table->index(['user_id', 'scanned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfid_scans');
        Schema::dropIfExists('biometric_devices');
        Schema::dropIfExists('rfid_cards');
    }
}
