<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionPaper extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'template_id', 'exam_id', 'class_id', 'section_id',
        'course_id', 'session_id',
        'exam_name', 'subject', 'class_label', 'duration',
        'full_marks', 'pass_marks', 'exam_date',
        'paper_size', 'orientation', 'status',
        'created_by', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'exam_date'   => 'date',
        'approved_at' => 'datetime',
    ];

    const STATUSES = [
        'draft'     => 'Draft',
        'submitted' => 'Submitted',
        'reviewed'  => 'Reviewed',
        'approved'  => 'Approved',
        'locked'    => 'Locked',
        'printed'   => 'Printed',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function template()  { return $this->belongsTo(QuestionPaperTemplate::class, 'template_id'); }
    public function exam()      { return $this->belongsTo(Exam::class, 'exam_id'); }
    public function course()    { return $this->belongsTo(Course::class, 'course_id'); }
    public function creator()   { return $this->belongsTo(User::class, 'created_by'); }
    public function approver()  { return $this->belongsTo(User::class, 'approved_by'); }
    public function sections()  { return $this->hasMany(QuestionSection::class, 'paper_id')->orderBy('sort_order'); }
    public function versions()  { return $this->hasMany(QuestionVersion::class, 'paper_id')->orderByDesc('version_number'); }
    public function approvals() { return $this->hasMany(QuestionApproval::class, 'paper_id')->latest(); }
    public function printLogs() { return $this->hasMany(QuestionPrintLog::class, 'paper_id'); }
    public function downloadLogs() { return $this->hasMany(QuestionDownloadLog::class, 'paper_id'); }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getIsEditableAttribute(): bool
    {
        return in_array($this->status, ['draft', 'submitted', 'reviewed']);
    }

    public function getIsLockedAttribute(): bool
    {
        return in_array($this->status, ['locked', 'printed']);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'bg-slate-100 text-slate-600',
            'submitted' => 'bg-amber-100 text-amber-700',
            'reviewed'  => 'bg-blue-100 text-blue-700',
            'approved'  => 'bg-emerald-100 text-emerald-700',
            'locked'    => 'bg-violet-100 text-violet-700',
            'printed'   => 'bg-slate-200 text-slate-600',
            default     => 'bg-slate-100 text-slate-500',
        };
    }

    /** Compute total marks from all sections. */
    public function getTotalMarksAttribute(): float
    {
        return $this->sections->sum('total_marks');
    }

    /** Snapshot the current paper to question_versions. */
    public function saveVersion(?string $summary = null): QuestionVersion
    {
        $latest = $this->versions()->max('version_number') ?? 0;

        $snapshot = [
            'paper'    => $this->toArray(),
            'sections' => $this->sections->map(fn($s) => array_merge(
                $s->toArray(),
                ['questions' => $s->questions->toArray()]
            ))->toArray(),
        ];

        return QuestionVersion::create([
            'paper_id'       => $this->id,
            'version_number' => $latest + 1,
            'snapshot'       => $snapshot,
            'change_summary' => $summary,
            'saved_by'       => auth()->id(),
        ]);
    }
}
