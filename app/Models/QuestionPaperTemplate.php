<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class QuestionPaperTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name', 'description',
        'school_name', 'school_logo_path', 'school_address',
        'exam_name_placeholder', 'subject_placeholder', 'class_placeholder',
        'time_placeholder', 'full_marks_placeholder', 'pass_marks_placeholder', 'date_placeholder',
        'header_html', 'instructions_html', 'footer_html',
        'signature_name', 'signature_title',
        'paper_size', 'orientation', 'show_watermark', 'watermark_text',
        'is_active', 'created_by',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'show_watermark' => 'boolean',
    ];

    /**
     * Templates are either:
     *   - school-specific  (school_id = X)  — created by that school
     *   - global/built-in  (school_id IS NULL) — seeded platform-wide
     *
     * We apply a custom scope that returns BOTH so every school can see
     * the platform templates alongside their own.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('template_tenant', function ($builder) {
            $schoolId = config('tenant.school_id');

            if (empty($schoolId)) {
                // Super-admin / no tenant context — show everything
                return;
            }

            $table = (new static())->getTable();
            $builder->where(function ($q) use ($table, $schoolId) {
                $q->where($table . '.school_id', $schoolId)
                  ->orWhereNull($table . '.school_id');
            });
        });

        // Auto-fill school_id on creation (mirrors HasTenant behaviour)
        static::creating(function ($model) {
            if (empty($model->school_id)) {
                $schoolId = config('tenant.school_id');
                if ($schoolId) {
                    $model->school_id = $schoolId;
                }
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function school()   { return $this->belongsTo(School::class, 'school_id'); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
    public function papers()   { return $this->hasMany(QuestionPaper::class, 'template_id'); }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Resolve all {{placeholders}} in HTML fields using given values.
     * Accepts both {{lower_case}} and {{UPPER_CASE}} variants.
     */
    public function renderHeader(array $vars): string
    {
        $html = $this->header_html ?? '';
        foreach ($vars as $key => $value) {
            // Exact match first
            $html = str_replace($key, (string) $value, $html);
        }
        return $html;
    }
}
