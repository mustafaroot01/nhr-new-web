<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Mews\Purifier\Facades\Purifier;

class Faq extends Model
{
    protected $fillable = [
        'question_ar', 'question_en',
        'answer_ar', 'answer_en',
        'is_visible',
        'sort_order',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected function answerAr(): Attribute
    {
        return Attribute::set(fn ($v) => $v ? Purifier::clean($v) : $v);
    }

    protected function answerEn(): Attribute
    {
        return Attribute::set(fn ($v) => $v ? Purifier::clean($v) : $v);
    }
}
