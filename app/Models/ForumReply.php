<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumReply extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'thread_id',
        'user_id',
        'content',
        'is_solution',
        'upvotes_count',
    ];

    protected $casts = [
        'is_solution' => 'boolean',
        'upvotes_count' => 'integer',
    ];

    public function thread()
    {
        return $this->belongsTo(ForumThread::class, 'thread_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function votes()
    {
        return $this->hasMany(ForumVote::class, 'reply_id');
    }

    public function isVotedBy(?int $userId): bool
    {
        if (!$userId) return false;
        return $this->votes()->where('user_id', $userId)->exists();
    }
}
