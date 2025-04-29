<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessibilitySettings extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'font_size',
        'high_contrast',
        'screen_reader_compatibility',
        'time_extension',
        'time_extension_reason',
        'time_extension_approval',
        'denial_reason',
        'text_to_speech',
        'keyboard_navigation',
        'color_overlay',
        'alternative_input',
        'voice_input',
        'live_assistance_allowed',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'high_contrast' => 'boolean',
        'screen_reader_compatibility' => 'boolean',
        'text_to_speech' => 'boolean',
        'keyboard_navigation' => 'boolean',
        'alternative_input' => 'boolean',
        'voice_input' => 'boolean',
        'live_assistance_allowed' => 'boolean',
    ];

    /**
     * Get the user that owns these accessibility settings.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 