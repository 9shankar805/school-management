<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeacherQualification extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['teacher_id','type','title','institution','field_of_study','start_year','end_year','grade','attachment_path'];

    const TYPES = ['degree'=>'Degree','diploma'=>'Diploma','certification'=>'Certification','training'=>'Training','other'=>'Other'];

    public function teacher() { return $this->belongsTo(User::class, 'teacher_id'); }
    public function getAttachmentUrlAttribute(): ?string {
        return $this->attachment_path ? \Illuminate\Support\Facades\Storage::url($this->attachment_path) : null;
    }
}
