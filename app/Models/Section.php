<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\SchoolClass;
use App\Models\Promotion;

class Section extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = ['school_id', 'section_name', 'room_no', 'class_id', 'session_id'];

    /**
     * Convenience accessor so ->name works alongside ->section_name
     */
    public function getNameAttribute(): string
    {
        return $this->section_name ?? '';
    }

    public function schoolClass() {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function promotions() {
        return $this->hasMany(Promotion::class, 'section_id', 'id');
    }
}
