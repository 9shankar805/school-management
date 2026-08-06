<?php
namespace App\Models;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeacherTraining extends Model
{
    use HasTenant;
    use HasFactory, SoftDeletes;

    protected $table = 'teacher_training';
    protected $fillable = ['school_id', 'teacher_id','title','organizer','type','from_date','to_date','hours','certificate_no','attachment_path','notes'];
    protected $casts = ['from_date'=>'date','to_date'=>'date'];

    const TYPES = ['workshop'=>'Workshop','seminar'=>'Seminar','online_course'=>'Online Course','conference'=>'Conference','certification'=>'Certification','other'=>'Other'];

    public function teacher() { return $this->belongsTo(User::class, 'teacher_id'); }
    public function getAttachmentUrlAttribute(): ?string {
        return $this->attachment_path ? \Illuminate\Support\Facades\Storage::url($this->attachment_path) : null;
    }
}
