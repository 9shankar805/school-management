<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PerformanceReview extends Model
{
    use HasFactory;
    protected $fillable = [
        'teacher_id','reviewer_id','review_period','review_date',
        'teaching_quality','punctuality','student_engagement','communication','professionalism',
        'overall_rating','strengths','areas_for_improvement','goals','reviewer_comments','status',
    ];
    protected $casts = ['review_date'=>'date','overall_rating'=>'decimal:1'];

    const RATING_LABELS = [1=>'Poor',2=>'Below Average',3=>'Average',4=>'Good',5=>'Excellent'];

    public function teacher()  { return $this->belongsTo(User::class, 'teacher_id'); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewer_id'); }

    public function computeOverallRating(): float {
        $fields = ['teaching_quality','punctuality','student_engagement','communication','professionalism'];
        $values = array_filter(array_map(fn($f) => $this->$f, $fields));
        return count($values) ? round(array_sum($values) / count($values), 1) : 0;
    }

    public function getRatingColorAttribute(): string {
        $r = (float)$this->overall_rating;
        if ($r >= 4.5) return 'text-emerald-600';
        if ($r >= 3.5) return 'text-blue-600';
        if ($r >= 2.5) return 'text-amber-600';
        return 'text-rose-600';
    }
}
