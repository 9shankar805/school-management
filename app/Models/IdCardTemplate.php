<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IdCardTemplate extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'is_active',
        // header
        'header_bg_color',
        'header_text_color',
        'show_logo',
        'show_school_name',
        'show_tagline',
        'tagline_text',
        'header_height',
        // accent
        'accent_color',
        // photo
        'show_photo',
        'photo_size',
        'photo_shape',
        'show_photo_corner_marks',
        // blood
        'show_blood_type',
        // body
        'body_bg_color',
        'name_text_color',
        'name_font_size',
        'name_uppercase',
        'show_role_badge',
        'visible_fields',
        // footer
        'footer_bg_color',
        'footer_text_color',
        'show_id_number',
        'show_session',
        'show_qr',
        'footer_left_text',
        'footer_center_text',
        'show_signature_line',
        'signature_label',
        // background
        'background_image_path',
        'background_opacity',
    ];

    protected $casts = [
        'is_active'               => 'boolean',
        'show_logo'               => 'boolean',
        'show_school_name'        => 'boolean',
        'show_tagline'            => 'boolean',
        'show_photo'              => 'boolean',
        'show_photo_corner_marks' => 'boolean',
        'show_blood_type'         => 'boolean',
        'name_uppercase'          => 'boolean',
        'show_role_badge'         => 'boolean',
        'show_id_number'          => 'boolean',
        'show_session'            => 'boolean',
        'show_qr'                 => 'boolean',
        'show_signature_line'     => 'boolean',
        'visible_fields'          => 'array',
    ];

    // ── Default field list ────────────────────────────────────────────────
    public const ALL_FIELDS = [
        'class'      => 'Class',
        'section'    => 'Section',
        'gender'     => 'Gender',
        'dob'        => 'Date of Birth',
        'phone'      => 'Phone',
        'blood_type' => 'Blood Type',
        'roll_no'    => 'Roll / ID No.',
    ];

    public const DEFAULT_FIELDS = ['class', 'gender', 'dob', 'phone'];

    /**
     * Return the default (fallback) template values as an object-like array,
     * used when no active template exists.
     */
    public static function defaults(): self
    {
        $t = new self();
        $t->forceFill([
            'header_bg_color'         => '#1a56db',
            'header_text_color'       => '#ffffff',
            'show_logo'               => true,
            'show_school_name'        => true,
            'show_tagline'            => true,
            'tagline_text'            => 'Student Identification Card',
            'header_height'           => 38,
            'accent_color'            => '#f59e0b',
            'show_photo'              => true,
            'photo_size'              => 52,
            'photo_shape'             => 'square',
            'show_photo_corner_marks' => true,
            'show_blood_type'         => true,
            'body_bg_color'           => '#ffffff',
            'name_text_color'         => '#0f172a',
            'name_font_size'          => 10,
            'name_uppercase'          => true,
            'show_role_badge'         => true,
            'visible_fields'          => self::DEFAULT_FIELDS,
            'footer_bg_color'         => '#f8fafc',
            'footer_text_color'       => '#1e293b',
            'show_id_number'          => true,
            'show_session'            => true,
            'show_qr'                 => true,
            'footer_left_text'        => null,
            'footer_center_text'      => null,
            'show_signature_line'     => false,
            'signature_label'         => 'Principal',
            'background_image_path'   => null,
            'background_opacity'      => 10,
        ]);
        return $t;
    }

    /**
     * Get the active template for the current school, falling back to defaults.
     */
    public static function active(): self
    {
        return static::where('is_active', true)->latest()->first() ?? static::defaults();
    }
}
