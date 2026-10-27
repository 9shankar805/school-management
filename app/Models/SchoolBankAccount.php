<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolBankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'bank_name',
        'bank_code',
        'account_name',
        'account_number',
        'branch_name',
        'account_type',
        'fonepay_merchant_id',
        'fonepay_pan',
        'qr_payload',
        'qr_image',
        'is_default',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'bank_account_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefaultFirst($query)
    {
        return $query->orderByDesc('is_default')->orderBy('bank_name');
    }

    public function getQrImageUrlAttribute(): ?string
    {
        if ($this->qr_image) {
            return asset('storage/' . $this->qr_image);
        }
        return null;
    }
}
