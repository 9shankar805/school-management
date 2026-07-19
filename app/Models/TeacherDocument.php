<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class TeacherDocument extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['teacher_id','document_type','title','file_path','file_name','mime_type','file_size','is_verified','uploaded_by'];
    protected $casts = ['is_verified'=>'boolean'];

    const TYPES = [
        'degree_certificate'  => 'Degree Certificate',
        'national_id'         => 'National ID',
        'passport'            => 'Passport',
        'experience_letter'   => 'Experience Letter',
        'appointment_letter'  => 'Appointment Letter',
        'police_clearance'    => 'Police Clearance',
        'medical_certificate' => 'Medical Certificate',
        'photo'               => 'Photo',
        'other'               => 'Other',
    ];

    public function teacher()  { return $this->belongsTo(User::class, 'teacher_id'); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }
    public function getUrlAttribute(): string { return Storage::url($this->file_path); }
    public function getHumanSizeAttribute(): string {
        $b = $this->file_size ?? 0;
        if ($b >= 1048576) return round($b/1048576,1).' MB';
        if ($b >= 1024)    return round($b/1024,0).' KB';
        return $b.' B';
    }
    public function isImage(): bool { return str_starts_with($this->mime_type ?? '', 'image/'); }
    public function isPdf():   bool { return ($this->mime_type ?? '') === 'application/pdf'; }
}
