<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookSetting extends Model
{
    protected $fillable = [
        'legacy_id', 'user_id', 'title', 'description', 'description_mobile',
        'experience', 'footer', 'theme', 'theme_settings', 'theme_home_image',
        'background_image', 'background_color', 'background_mode', 'is_centered',
        'thumbnail', 'bio_photo', 'custom_css', 'custom_js', 'analytics_id',
        'diffuse_ub', 'diffuse_web', 'diffuse_newsletter', 'diffuse_availability',
        'legacy_payload',
    ];

    protected function casts(): array
    {
        return [
            'theme_settings' => 'array',
            'legacy_payload' => 'array',
            'is_centered' => 'boolean',
            'diffuse_ub' => 'boolean',
            'diffuse_web' => 'boolean',
            'diffuse_newsletter' => 'boolean',
            'diffuse_availability' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
