<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\MedicalRecord;
use App\Models\EmergencyContact;
use App\Models\StudentDocument;
use App\Models\DisciplinaryRecord;
use App\Models\HouseAssignment;
use App\Models\Scholarship;
use App\Models\StudentTransfer;
use App\Models\Promotion;
use App\Models\StudentStatus;
use App\Models\Achievement;
use App\Models\TeacherQualification;
use App\Models\TeacherContract;
use App\Models\TeacherDocument;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\TeacherAttendance;
use App\Models\StaffAttendance;
use App\Models\PerformanceReview;
use App\Models\TeacherTraining;
use App\Models\TeacherPayroll;
use App\Models\Department;
use App\Models\Conversation;

class User extends Authenticatable
{
    use HasApiTokens, HasRoles, HasFactory, Notifiable;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'password',
        'gender', 'nationality', 'phone',
        'address', 'address2', 'city', 'zip',
        'photo', 'birthday', 'religion', 'blood_type',
        'role',                     // legacy column kept for compatibility
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthday'          => 'date',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function parent_info(): HasOne
    {
        return $this->hasOne(StudentParentInfo::class, 'student_id');
    }

    public function academic_info(): HasOne
    {
        return $this->hasOne(StudentAcademicInfo::class, 'student_id');
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class, 'student_id');
    }

    public function twoFactorAuth(): HasOne
    {
        return $this->hasOne(TwoFactorAuth::class);
    }

    public function mediaFiles(): HasMany
    {
        return $this->hasMany(MediaFile::class, 'uploaded_by');
    }

    /** Teacher → assigned courses */
    public function assignedCourses(): HasMany
    {
        return $this->hasMany(AssignedTeacher::class, 'teacher_id');
    }

    // ── Teacher extended relationships ────────────────────────────────────

    public function qualifications(): HasMany
    {
        return $this->hasMany(TeacherQualification::class, 'teacher_id')->latest('end_year');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(TeacherContract::class, 'teacher_id')->latest('start_date');
    }

    public function activeContract(): HasOne
    {
        return $this->hasOne(TeacherContract::class, 'teacher_id')->where('status', 'active')->latestOfMany('start_date');
    }

    public function teacherDocuments(): HasMany
    {
        return $this->hasMany(TeacherDocument::class, 'teacher_id');
    }

    public function leaveApplications(): HasMany
    {
        return $this->hasMany(LeaveApplication::class, 'user_id')->latest();
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class, 'user_id');
    }

    public function teacherAttendance(): HasMany
    {
        return $this->hasMany(TeacherAttendance::class, 'teacher_id')->latest('date');
    }

    public function staffAttendance(): HasMany
    {
        return $this->hasMany(StaffAttendance::class, 'staff_id')->latest('date');
    }

    public function performanceReviews(): HasMany
    {
        return $this->hasMany(PerformanceReview::class, 'teacher_id')->latest('review_date');
    }

    public function trainingRecords(): HasMany
    {
        return $this->hasMany(TeacherTraining::class, 'teacher_id')->latest('from_date');
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(TeacherPayroll::class, 'teacher_id')->orderByDesc('year')->orderByDesc('month');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_user');
    }

    // ── Student extended relationships ────────────────────────────────────

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class, 'student_id');
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmergencyContact::class, 'student_id');
    }

    public function studentDocuments(): HasMany
    {
        return $this->hasMany(StudentDocument::class, 'student_id');
    }

    public function disciplinaryRecords(): HasMany
    {
        return $this->hasMany(DisciplinaryRecord::class, 'student_id')->latest();
    }

    public function houseAssignments(): HasMany
    {
        return $this->hasMany(HouseAssignment::class, 'student_id');
    }

    public function scholarships(): HasMany
    {
        return $this->hasMany(Scholarship::class, 'student_id')->latest();
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(StudentTransfer::class, 'student_id')->latest();
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class, 'student_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(StudentStatus::class, 'student_id')->latest();
    }

    public function currentStatus(): HasOne
    {
        return $this->hasOne(StudentStatus::class, 'student_id')->where('is_current', true)->latestOfMany('effective_date');
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class, 'student_id')->latest('awarded_date');
    }

    // ── Parent–Student relationships ──────────────────────────────────────

    /** Parent → linked children (via parent_student pivot) */
    public function children()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'parent_id', 'student_id')
                    ->withPivot('relationship', 'is_primary')
                    ->withTimestamps();
    }

    /** Student → linked parents (via parent_student pivot) */
    public function parents()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id')
                    ->withPivot('relationship', 'is_primary')
                    ->withTimestamps();
    }

    /** Parent → conversations */
    public function parentConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'parent_id')->latest();
    }

    // -----------------------------------------------------------------------
    // Role convenience helpers
    // (Use these instead of role === 'x' string checks throughout views)
    // -----------------------------------------------------------------------

    public function isSuperAdmin(): bool    { return $this->hasRole('super-admin'); }
    public function isOrgOwner(): bool      { return $this->hasRole('organization-owner'); }
    public function isAdmin(): bool         { return $this->hasRole('admin'); }
    public function isPrincipal(): bool     { return $this->hasRole('principal'); }
    public function isVicePrincipal(): bool { return $this->hasRole('vice-principal'); }
    public function isAcademicCoord(): bool { return $this->hasRole('academic-coordinator'); }
    public function isTeacher(): bool       { return $this->hasAnyRole(['teacher', 'class-teacher']); }
    public function isClassTeacher(): bool  { return $this->hasRole('class-teacher'); }
    public function isStudent(): bool       { return $this->hasRole('student'); }
    public function isParent(): bool        { return $this->hasRole('parent'); }
    public function isAccountant(): bool    { return $this->hasRole('accountant'); }
    public function isLibrarian(): bool     { return $this->hasRole('librarian'); }
    public function isReceptionist(): bool  { return $this->hasRole('receptionist'); }
    public function isHrManager(): bool     { return $this->hasRole('hr-manager'); }
    public function isTransportManager():bool{ return $this->hasRole('transport-manager'); }
    public function isHostelManager(): bool { return $this->hasRole('hostel-manager'); }
    public function isExamController(): bool{ return $this->hasRole('exam-controller'); }
    public function isAttendanceOfficer():bool{ return $this->hasRole('attendance-officer'); }
    public function isAdmissionOfficer():bool{ return $this->hasRole('admission-officer'); }

    /** True for all staff/management roles (not student/parent/guest) */
    public function isStaff(): bool
    {
        return $this->hasAnyRole([
            'super-admin','organization-owner','admin','principal','vice-principal',
            'academic-coordinator','teacher','class-teacher','accountant','librarian',
            'receptionist','hr-manager','transport-manager','hostel-manager',
            'exam-controller','attendance-officer','admission-officer',
        ]);
    }

    /** Returns the intended dashboard route name after login */
    public function getHomePage(): string
    {
        return match (true) {
            $this->isSuperAdmin()       => 'home',
            $this->isOrgOwner()         => 'home',
            $this->isAdmin()            => 'home',
            $this->isPrincipal()        => 'home',
            $this->isVicePrincipal()    => 'home',
            $this->isAcademicCoord()    => 'home',
            $this->isTeacher()          => 'home',
            $this->isStudent()          => 'home',
            $this->isParent()           => 'home',
            $this->isAccountant()       => 'home',
            $this->isLibrarian()        => 'home',
            $this->isExamController()   => 'home',
            $this->isAttendanceOfficer()=> 'home',
            $this->isAdmissionOfficer() => 'home',
            default                     => 'home',
        };
    }

    /** Returns the dashboard view name for this user's primary role */
    public function getDashboardView(): string
    {
        return match (true) {
            $this->isSuperAdmin()       => 'dashboards.super-admin',
            $this->isOrgOwner()         => 'dashboards.organization-owner',
            $this->isAdmin()            => 'dashboards.admin',
            $this->isPrincipal()        => 'dashboards.principal',
            $this->isVicePrincipal()    => 'dashboards.vice-principal',
            $this->isAcademicCoord()    => 'dashboards.academic-coordinator',
            $this->isClassTeacher()     => 'dashboards.teacher',
            $this->isTeacher()          => 'dashboards.teacher',
            $this->isStudent()          => 'dashboards.student',
            $this->isParent()           => 'dashboards.parent',
            $this->isAccountant()       => 'dashboards.accountant',
            $this->isLibrarian()        => 'dashboards.librarian',
            $this->isHrManager()        => 'dashboards.hr-manager',
            $this->isExamController()   => 'dashboards.exam-controller',
            $this->isAttendanceOfficer()=> 'dashboards.attendance-officer',
            $this->isAdmissionOfficer() => 'dashboards.admission-officer',
            $this->isTransportManager() => 'dashboards.transport-manager',
            $this->isHostelManager()    => 'dashboards.hostel-manager',
            $this->isReceptionist()     => 'dashboards.receptionist',
            default                     => 'dashboards.admin',
        };
    }

    /** Display name */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /** Primary role label for UI badges */
    public function getPrimaryRoleAttribute(): string
    {
        return $this->getRoleNames()->first() ?? ($this->role ?? 'user');
    }

    /** Avatar URL — photo or generated initials avatar */
    public function getAvatarAttribute(): string
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        $initials = strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
        return "https://ui-avatars.com/api/?name={$initials}&background=6366f1&color=fff&size=128";
    }
}
