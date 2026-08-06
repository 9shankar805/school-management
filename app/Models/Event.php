<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'title',
        'start',
        'end',
        'color',
        'description',
        'session_id',
    ];
}
