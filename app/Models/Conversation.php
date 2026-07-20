<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['parent_id', 'teacher_id', 'student_id', 'subject'];

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function messages()
    {
        return $this->hasMany(ParentTeacherMessage::class)->orderBy('created_at');
    }

    public function latestMessage()
    {
        return $this->hasOne(ParentTeacherMessage::class)->latestOfMany('created_at');
    }

    public function getUnreadCountForUser(int $userId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();
    }
}
