<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Media extends Model
{
    protected $fillable = [
        'uuid',
        'path',
        'filename',
        'original_name',
        'mime_type',
        'size',
        'width',
        'height',
        'alt_text',
        'uploaded_by',
    ];

    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Media $media) {
            $media->uuid ??= Str::uuid()->toString();
        });
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }

    public function getThumbnailUrlAttribute(): string
    {
        $pathInfo = pathinfo($this->path);
        $thumbPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_thumb.' . ($pathInfo['extension'] ?? 'jpg');

        return asset('storage/' . $thumbPath);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    public function isDocument(): bool
    {
        return in_array($this->mime_type, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }
}
