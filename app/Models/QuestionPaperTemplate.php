<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionPaperTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
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

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function papers()  { return $this->hasMany(QuestionPaper::class, 'template_id'); }

    /**
     * Resolve all {{placeholders}} in HTML fields using given values.
     */
    public function renderHeader(array $vars): string
    {
        $html = $this->header_html ?? '';
        foreach ($vars as $key => $value) {
            $html = str_replace($key, $value, $html);
        }
        return $html;
    }
}
