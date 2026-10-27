<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'title',
        'category',       // holiday, exam, academic, sports, celebration, meeting, vacation, other
        'is_holiday',     // true if school is closed
        'start',
        'end',
        'start_bs',       // YYYY-MM-DD (Bikram Sambat)
        'end_bs',         // YYYY-MM-DD
        'audience',       // all, students, teachers, staff, parents
        'academic_term',  // First Term, Mid Term, Second Term, Final Term, Annual
        'venue',
        'color',
        'description',
        'session_id',
    ];

    protected $casts = [
        'is_holiday' => 'boolean',
        'start'      => 'datetime',
        'end'        => 'datetime',
    ];

    public static function categoryColors(): array
    {
        return [
            'holiday'     => '#dc2626', // Red
            'vacation'    => '#ea580c', // Orange
            'exam'        => '#9333ea', // Purple
            'academic'    => '#4f46e5', // Indigo
            'sports'      => '#16a34a', // Emerald Green
            'celebration' => '#db2777', // Pink
            'meeting'     => '#0891b2', // Cyan
            'other'       => '#475569', // Slate
        ];
    }

    public static function categoryLabels(): array
    {
        return [
            'holiday'     => 'सार्वजनिक / विद्यालय बिदा (Holiday)',
            'vacation'    => 'लामो बिदा (Vacation Break)',
            'exam'        => 'परीक्षा तालिका (Examinations)',
            'academic'    => 'शैक्षिक कार्यक्रम (Academic Milestones)',
            'sports'      => 'खेलकुद तथा अतिरिक्त (Sports & ECA)',
            'celebration' => 'उत्सव तथा जयन्ती (Celebrations & Festivals)',
            'meeting'     => 'बैठक / PTM (Meetings & Consultations)',
            'other'       => 'अन्य कार्यक्रम (General Activities)',
        ];
    }
}
