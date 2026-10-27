<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SchoolSession extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = ['school_id', 'session_name'];

    /**
     * Get the active or browsed school session.
     */
    public static function current(): ?self
    {
        if (session()->has('browse_session_id')) {
            $browse = self::find(session('browse_session_id'));
            if ($browse) {
                return $browse;
            }
        }

        return self::latest('id')->first();
    }
}
