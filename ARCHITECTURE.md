# School Management ERP — System Architecture

## Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 8 (PHP 8.3) |
| Database | MySQL 8 |
| Cache | File (Redis-ready via predis/predis) |
| Queue | Database driver → upgrade to Redis in production |
| Auth (Web) | Laravel Session + Spatie Permission |
| Auth (API) | Laravel Sanctum (token-based, stateless) |
| 2FA | Custom TOTP stub (upgrade with pragmarx/google2fa) |
| PDF | barryvdh/laravel-dompdf v2 |
| Excel/CSV | maatwebsite/excel v3 |
| Activity Log | spatie/laravel-activitylog v4 |
| Audit Trail | Custom AuditLog model + AuditObserver |
| File Manager | Custom MediaFile model + FileManagerController |
| Notifications | Laravel Notifications (database + mail channels) |
| API Prefix | /api/v1/ |
| Rate Limiting | 60 req/min (public), 600 req/min (authenticated) |

---

## Directory Structure

```
app/
├── Console/Kernel.php
├── Exceptions/Handler.php
├── Http/
│   ├── Controllers/
│   │   ├── Api/V1/                 ← All REST API controllers
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── StudentController.php
│   │   │   ├── TeacherController.php
│   │   │   ├── AttendanceController.php
│   │   │   ├── ExamController.php
│   │   │   ├── MarkController.php
│   │   │   ├── NotificationController.php
│   │   │   └── SettingController.php
│   │   ├── Auth/                   ← Web auth (login/register/password)
│   │   ├── FileManagerController.php
│   │   ├── NotificationController.php
│   │   ├── TwoFactorController.php
│   │   └── [feature controllers...]
│   ├── Middleware/
│   │   ├── EnsureTwoFactorEnabled.php
│   │   └── SetSchoolContext.php
│   └── Kernel.php
├── Interfaces/                     ← Repository pattern interfaces
├── Models/
│   ├── AuditLog.php
│   ├── MediaFile.php
│   ├── Setting.php
│   ├── TwoFactorAuth.php
│   └── [domain models...]
├── Notifications/
│   ├── GeneralNotification.php     ← Multi-channel general purpose
│   ├── FeeReminderNotification.php
│   └── AttendanceAlertNotification.php
├── Observers/
│   └── AuditObserver.php           ← Auto audit-trails on all models
├── Providers/
│   ├── AppServiceProvider.php      ← Observer registration + bindings
│   └── [feature service providers...]
└── Repositories/                   ← Repository implementations
```

---

## Database Tables (complete list)

### Core / Auth
| Table | Purpose |
|---|---|
| users | All user roles (admin, teacher, student, staff, parent) |
| personal_access_tokens | Sanctum API tokens |
| password_resets | Password reset tokens |
| two_factor_auth | 2FA secrets per user |
| roles / permissions | Spatie RBAC tables |

### Academic
| Table | Purpose |
|---|---|
| school_sessions | Academic years |
| semesters | Semester within a session |
| school_classes | Class (Grade 1, Grade 2, etc.) |
| sections | Section per class (A, B, C) |
| courses | Subject/course per section |
| assigned_teachers | Teacher → course mapping |
| academic_settings | Global academic config flags |
| promotions | Student promotion records |
| syllabi | Course syllabus content |
| routines | Section timetable |

### Student / Parent
| Table | Purpose |
|---|---|
| student_academic_infos | Class/section/roll for student |
| student_parent_infos | Guardian details |

### Attendance
| Table | Purpose |
|---|---|
| attendances | Per-student-per-course-per-day records |

### Exams / Marks
| Table | Purpose |
|---|---|
| exams | Exam definitions |
| exam_rules | Rules per exam (pass mark, weight) |
| grading_systems | GPA/grade scale |
| grade_rules | Grade bands per grading system |
| marks | Raw marks per student per course per exam |
| final_marks | Computed/finalized marks |

### Finance
| Table | Purpose |
|---|---|
| invoices | Fee invoices per student |
| payments | Payment transactions |

### Communication
| Table | Purpose |
|---|---|
| notices | Notice board entries |
| events | Calendar events |
| assignments | Assignments per course |
| notifications | Laravel database notifications |

### Library
| Table | Purpose |
|---|---|
| books | Book catalog |

### Infrastructure
| Table | Purpose |
|---|---|
| settings | Key-value school configuration store |
| audit_logs | Who changed what and when |
| media_files | Uploaded files / documents |
| activity_log | Spatie user activity stream |
| jobs | Queue jobs (database driver) |
| failed_jobs | Failed queue job log |

---

## API Architecture

```
POST   /api/v1/auth/login                → AuthController@login (returns Sanctum token)
POST   /api/v1/auth/logout               → AuthController@logout
GET    /api/v1/auth/me                   → AuthController@me
POST   /api/v1/auth/refresh              → AuthController@refresh
POST   /api/v1/auth/forgot-password
POST   /api/v1/auth/reset-password
POST   /api/v1/auth/2fa/enable
POST   /api/v1/auth/2fa/verify
POST   /api/v1/auth/2fa/disable

GET    /api/v1/dashboard                 → Role-aware KPIs

GET    /api/v1/students                  → paginated student list
POST   /api/v1/students
GET    /api/v1/students/{id}
PUT    /api/v1/students/{id}
DELETE /api/v1/students/{id}
GET    /api/v1/students/{id}/attendance
GET    /api/v1/students/{id}/results

GET    /api/v1/teachers
POST   /api/v1/teachers
GET    /api/v1/teachers/{id}

GET    /api/v1/attendance
POST   /api/v1/attendance               → bulk record submission
GET    /api/v1/attendance/report

GET    /api/v1/exams
POST   /api/v1/exams
GET    /api/v1/exams/{id}

GET    /api/v1/marks
POST   /api/v1/marks
GET    /api/v1/marks/results

GET    /api/v1/notifications
POST   /api/v1/notifications/{id}/read
POST   /api/v1/notifications/read-all
DELETE /api/v1/notifications/{id}

GET    /api/v1/settings                  → admin only
POST   /api/v1/settings
GET    /api/v1/settings/{group}
```

### Authentication Flow
```
Client → POST /api/v1/auth/login
       ← { token: "Bearer <sanctum-token>", user: {...}, two_factor_required: bool }

Client → GET /api/v1/dashboard
         Header: Authorization: Bearer <token>
       ← { data: { kpis: {...}, charts: {...} } }
```

---

## Security Architecture

| Control | Implementation |
|---|---|
| Authentication | Session (web) + Sanctum tokens (API) |
| Authorization | Spatie RBAC — roles + fine-grained permissions |
| 2FA | TOTP challenge middleware (EnsureTwoFactorEnabled) |
| Audit Trail | AuditObserver registered on all key models |
| Rate Limiting | 60/min unauthenticated, 600/min authenticated |
| CSRF | Laravel VerifyCsrfToken middleware (web routes) |
| XSS | stevebauman/purify on all user inputs |
| File Access | Private files served via auth-checked route |
| School Context | SetSchoolContext middleware scopes all queries |

---

## Notification Engine

All notifications extend `App\Notifications\GeneralNotification` or specific classes.

Channels available:
- **database** — stored in `notifications` table, shown in in-app bell
- **mail** — queued via `ShouldQueue`, sent async through jobs table
- **SMS / WhatsApp** — stub channels, wire up Twilio/Vonage in Phase 3

Dispatch pattern:
```php
// Single user
$user->notify(new GeneralNotification(
    title:    'Assignment Due',
    message:  'Your PHP assignment is due tomorrow.',
    type:     'assignment',
    actionUrl: '/assignments/5',
));

// Bulk (all students in a class)
Notification::send($students, new FeeReminderNotification(...));
```

---

## Settings System

```php
// Read (cached 60 min)
Setting::get('school_name', 'My School');

// Write (busts cache)
Setting::set('school_name', 'Oxford Academy', group: 'general');

// Read a whole group
Setting::group('mail');  // returns array
```

---

## Audit Log

Every create/update/delete on observed models is automatically logged:

```php
// Manual log
AuditLog::record('custom_event', $model, $oldValues, $newValues);

// Add new model to auto-audit (AppServiceProvider)
MyModel::observe(AuditObserver::class);
```

---

## Multi-tenancy Readiness

- `settings` table has `school_id` column — all settings can be scoped per school
- `media_files` table has `school_id` column
- `SetSchoolContext` middleware injects `school_id` into every request
- All future modules must include `school_id` on their tables
- Full multi-tenancy (separate DB per school) can be layered in via `spatie/laravel-multitenancy` in Phase 2

---

## Queue Setup

Development (current): `QUEUE_CONNECTION=database`  
Production (recommended): Switch to `QUEUE_CONNECTION=redis` once Redis is available

Run worker:
```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=90
```

Supervisor config is included in `docker/supervisor.conf` (Phase 3).
