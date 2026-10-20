<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
    ];

    public static function get(string $group, string $key, mixed $default = null): mixed
    {
        return Cache::remember(
            "settings.{$group}.{$key}",
            now()->addHour(),
            fn () => static::where('group', $group)->where('key', $key)->value('value') ?? $default
        );
    }

    public static function set(string $group, string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $value]
        );

        Cache::forget("settings.{$group}.{$key}");
        Cache::forget("settings.{$group}");
    }

    public static function getGroup(string $group): array
    {
        return Cache::remember(
            "settings.{$group}",
            now()->addHour(),
            fn () => static::where('group', $group)->pluck('value', 'key')->toArray()
        );
    }
}
