<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mews\Purifier\Facades\Purifier;

class PostTranslation extends Model
{
    protected $fillable = [
        'post_id',
        'locale',
        'title',
        'subtitle',
        'excerpt',
        'body',
        'meta_title',
        'meta_description',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Sanitize rich-text HTML on write to prevent stored XSS
     * (strips <script>, event handlers, javascript: URIs, etc.).
     */
    protected function body(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value === null ? null : Purifier::clean($value),
        );
    }
}
