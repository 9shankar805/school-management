<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    
    protected $fillable = ['student_id', 'title', 'amount', 'status', 'due_date', 'description'];

    public function student() {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function payments() {
        return $this->hasMany(Payment::class);
    }
}
