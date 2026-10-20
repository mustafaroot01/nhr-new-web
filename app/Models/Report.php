<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Report extends Model
{
    protected $fillable = [
        'uuid',
        'title_ar', 'title_en',
        'description_ar', 'description_en',
        'cover_image_path',
        'pdf_ar_path', 'pdf_en_path',
        'year',
        'category',
        'is_published',
        'sort_order',
        'download_count',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'year' => 'integer',
        'sort_order' => 'integer',
        'download_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Report $report) {
            $report->uuid ??= Str::uuid()->toString();
        });
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_image_path ? asset('storage/' . $this->cover_image_path) : null;
    }
}
