<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class StudentFeeDiscount extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $table = 'student_fee_discounts';

    protected $fillable = [
        'school_id',
        'student_id', 'discount_id', 'session_id', 'valid_from', 'valid_to', 'notes',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to'   => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function discount()
    {
        return $this->belongsTo(FeeDiscount::class, 'discount_id');
    }

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }
}
