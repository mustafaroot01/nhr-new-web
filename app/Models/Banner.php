<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'media_type',
        'image_path',
        'video_path',
        'title_ar', 'title_en',
        'subtitle_ar', 'subtitle_en',
        'link',
        'button_text_ar', 'button_text_en',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }
}
