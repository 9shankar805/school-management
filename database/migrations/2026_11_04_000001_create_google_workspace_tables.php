<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. School-specific Google Workspace Credentials & Preferences (Multi-Tenant)
        if (!Schema::hasTable('school_google_configs')) {
            Schema::create('school_google_configs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->unique()->constrained('schools')->cascadeOnDelete();
                $table->string('workspace_domain')->nullable();          // e.g. prithvi.edu.np
                $table->string('admin_email')->nullable();               // e.g. principal@prithvi.edu.np
                $table->longText('service_account_json')->nullable();    // Encrypted JSON key
                $table->string('client_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('sync_classroom')->default(true);
                $table->boolean('sync_directory_users')->default(true);
                $table->boolean('sync_meet_attendance')->default(true);
                $table->boolean('auto_create_student_emails')->default(false);
                $table->string('connection_status')->default('untested'); // connected, disconnected, error, untested
                $table->timestamp('last_synced_at')->nullable();
                $table->text('last_error_message')->nullable();
                $table->json('stats')->nullable();                       // Cached sync counts
                $table->timestamps();
            });
        }

        // 2. Google Classroom Courses (Classes / Sections / Subjects)
        if (!Schema::hasTable('google_classroom_courses')) {
            Schema::create('google_classroom_courses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->unsignedBigInteger('section_id')->nullable();
                $table->unsignedBigInteger('course_id')->nullable(); // Subject
                $table->unsignedBigInteger('session_id')->nullable();
                $table->string('google_course_id')->index();
                $table->string('name');
                $table->string('section_name')->nullable();
                $table->string('description_heading')->nullable();
                $table->string('owner_google_id')->nullable();
                $table->string('enrollment_code')->nullable();
                $table->string('alternate_link')->nullable(); // Classroom Web URL
                $table->string('teacher_folder_id')->nullable(); // Google Drive Folder
                $table->string('calendar_id')->nullable(); // Google Calendar ID
                $table->string('course_state')->default('ACTIVE'); // ACTIVE, ARCHIVED
                $table->timestamps();

                $table->index(['school_id', 'class_id', 'section_id', 'course_id'], 'g_course_lookup_idx');
            });
        }

        // 3. Google Classroom Rosters (Enrolled Teachers & Students)
        if (!Schema::hasTable('google_classroom_rosters')) {
            Schema::create('google_classroom_rosters', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
                $table->foreignId('google_classroom_course_id')->constrained('google_classroom_courses')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('google_user_id')->index();
                $table->string('user_email')->nullable();
                $table->enum('role', ['STUDENT', 'TEACHER'])->default('STUDENT');
                $table->string('enrollment_state')->default('ACTIVE');
                $table->timestamps();

                $table->unique(['google_classroom_course_id', 'google_user_id', 'role'], 'g_roster_unique');
            });
        }

        // 4. Google Workspace Accounts (Directory API Mirror)
        if (!Schema::hasTable('google_workspace_accounts')) {
            Schema::create('google_workspace_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('google_user_id')->nullable()->index();
                $table->string('primary_email')->index();
                $table->string('recovery_email')->nullable();
                $table->string('org_unit_path')->default('/Students');
                $table->boolean('is_suspended')->default(false);
                $table->boolean('is_2sv_enrolled')->default(false);
                $table->timestamp('last_login_at')->nullable();
                $table->timestamps();

                $table->index(['school_id', 'user_id']);
            });
        }

        // 5. Google Sync Logs & Audit History
        if (!Schema::hasTable('google_sync_logs')) {
            Schema::create('google_sync_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
                $table->string('sync_type'); // 'full_migration', 'roster_sync', 'grade_pull', 'account_provision'
                $table->string('status');    // 'success', 'warning', 'failed'
                $table->string('summary');
                $table->json('details')->nullable();
                $table->unsignedBigInteger('triggered_by')->nullable();
                $table->timestamps();
            });
        }

        // 6. Direct Google Workspace credentials & identity pointers on Users
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'google_id')) {
                    $table->string('google_id')->nullable()->index();
                }
                if (!Schema::hasColumn('users', 'google_workspace_email')) {
                    $table->string('google_workspace_email')->nullable()->index();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'google_id')) {
                    $table->dropColumn('google_id');
                }
                if (Schema::hasColumn('users', 'google_workspace_email')) {
                    $table->dropColumn('google_workspace_email');
                }
            });
        }
        Schema::dropIfExists('google_sync_logs');
        Schema::dropIfExists('google_workspace_accounts');
        Schema::dropIfExists('google_classroom_rosters');
        Schema::dropIfExists('google_classroom_courses');
        Schema::dropIfExists('school_google_configs');
    }
};

