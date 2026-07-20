<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionApproval extends Model
{
    use HasFactory;

    protected $fillable = ['paper_id', 'reviewer_id', 'action', 'comments', 'actioned_at'];
    protected $casts    = ['actioned_at' => 'datetime'];

    const ACTIONS = [
        'submitted' => 'Submitted for Review',
        'reviewed'  => 'Reviewed',
        'approved'  => 'Approved',
        'rejected'  => 'Rejected',
        'locked'    => 'Locked for Print',
    ];

    public function paper()    { return $this->belongsTo(QuestionPaper::class, 'paper_id'); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewer_id'); }

    public function getActionBadgeAttribute(): string
    {
        return match ($this->action) {
            'submitted' => 'bg-amber-100 text-amber-700',
            'reviewed'  => 'bg-blue-100 text-blue-700',
            'approved'  => 'bg-emerald-100 text-emerald-700',
            'rejected'  => 'bg-rose-100 text-rose-700',
            'locked'    => 'bg-violet-100 text-violet-700',
            default     => 'bg-slate-100 text-slate-500',
        };
    }
}
