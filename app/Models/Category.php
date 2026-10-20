<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Category extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = [
        'uuid',
        'parent_id',
        'slug',
        'sort_order',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            $category->uuid ??= Str::uuid()->toString();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['slug', 'parent_id', 'is_visible', 'sort_order'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('تصنيف')
            ->setDescriptionForEvent(fn (string $event) => match ($event) {
                'created' => 'أنشأ',
                'updated' => 'عدّل',
                'deleted' => 'حذف',
                default => $event,
            });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function translation(string $locale = 'ar'): ?CategoryTranslation
    {
        return $this->translations->firstWhere('locale', $locale);
    }

    public function getNameAttribute(): string
    {
        return $this->translation(app()->getLocale())?->name
            ?? $this->translation('ar')?->name
            ?? '';
    }
}
