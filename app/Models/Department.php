<?php
namespace App\Models;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory, HasTenant, SoftDeletes;
    protected $fillable = ['school_id', 'name', 'code', 'description', 'head_id', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function head() { return $this->belongsTo(User::class, 'head_id'); }
    public function teachers() { return $this->belongsToMany(User::class, 'department_user'); }
}
