<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Post extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = [
        'uuid',
        'type',
        'slug',
        'featured_image_id',
        'author_id',
        'category_id',
        'status',
        'is_featured',
        'view_count',
        'published_at',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'view_count' => 'integer',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Post $post) {
            $post->uuid ??= Str::uuid()->toString();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['type', 'slug', 'status', 'category_id', 'is_featured', 'published_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName($this->type === 'page' ? 'صفحة' : 'مقال')
            ->setDescriptionForEvent(fn (string $event) => match ($event) {
                'created' => 'أنشأ',
                'updated' => 'عدّل',
                'deleted' => 'حذف',
                default => $event,
            });
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PostTranslation::class);
    }

    public function translation(string $locale = 'ar'): ?PostTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    public function getTitleAttribute(): string
    {
        return $this->translation(app()->getLocale())?->title
            ?? $this->translation('ar')?->title
            ?? '';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image_id');
    }

    public function galleryMedia(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'post_media')
            ->wherePivot('type', 'gallery')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function attachments(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'post_media')
            ->wherePivot('type', 'attachment')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopePosts(Builder $query): Builder
    {
        return $query->where('type', 'post');
    }

    public function scopePages(Builder $query): Builder
    {
        return $query->where('type', 'page');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function incrementViews(): void
    {
        $this->increment('view_count');
    }
}
