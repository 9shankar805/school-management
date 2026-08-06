<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class FeeCategory extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    public const LEGAL_FEE_HEADS = [
        'Tuition',
        'Annual',
        'Admission',
        'Refundable Deposit',
        'Exam',
        'Computer',
        'Special Training',
        'Hostel',
        'Meal',
        'Transport',
        'Educational Tour',
    ];

    protected $fillable = ['school_id', 'name', 'code', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    protected static function booted()
    {
        static::saving(function ($model) {
            if (app()->environment() === 'production' && !static::isLegalHeadName($model->name)) {
                Log::warning('Fee category outside Nepal 11 legal heads: ' . $model->name);
            }
        });
    }

    private static function normalizeName(string $name): string
    {
        $normalized = preg_replace('/[^a-zA-Z0-9\s]/', '', $name);
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        return trim(strtolower($normalized));
    }

    public static function isLegalHeadName(string $name): bool
    {
        $normalized = static::normalizeName($name);
        if ($normalized === '') {
            return false;
        }
        foreach (static::LEGAL_FEE_HEADS as $head) {
            $legalNormalized = static::normalizeName($head);
            if (strcasecmp($normalized, $legalNormalized) === 0) {
                return true;
            }
            $percent = 0;
            similar_text($normalized, $legalNormalized, $percent);
            if ($percent > 85) {
                return true;
            }
        }
        return false;
    }

    public static function findClosestLegalHead(string $name): ?string
    {
        $normalized = static::normalizeName($name);
        if ($normalized === '') {
            return null;
        }
        $bestHead = null;
        $bestPercent = 0;
        foreach (static::LEGAL_FEE_HEADS as $head) {
            $legalNormalized = static::normalizeName($head);
            $percent = 0;
            similar_text($normalized, $legalNormalized, $percent);
            if ($percent > $bestPercent) {
                $bestPercent = $percent;
                $bestHead = $head;
            }
        }
        return $bestPercent > 70 ? $bestHead : null;
    }

    public function feeStructures()
    {
        return $this->hasMany(FeeStructure::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
