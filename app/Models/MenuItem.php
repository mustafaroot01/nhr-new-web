<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_id',
        'parent_id',
        'label',
        'type',
        'reference_id',
        'url',
        'is_visible',
        'sort_order',
        'open_in_new_tab',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'open_in_new_tab' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }

    public function getResolvedUrlAttribute(): string
    {
        return match ($this->type) {
            'page' => $this->reference_id ? route('page.show', Post::find($this->reference_id)?->slug ?? '#') : '#',
            'category' => $this->reference_id ? route('category.show', Category::find($this->reference_id)?->slug ?? '#') : '#',
            default => $this->url ?? '#',
        };
    }
}
