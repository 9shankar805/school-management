<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class QuestionCategory extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = ['school_id', 'name', 'subject', 'description', 'parent_id', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public function parent()    { return $this->belongsTo(self::class, 'parent_id'); }
    public function children()  { return $this->hasMany(self::class, 'parent_id'); }
    public function questions() { return $this->hasMany(QuestionBank::class, 'category_id'); }
}
