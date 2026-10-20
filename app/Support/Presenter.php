<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Post;
use App\Models\Report;

/**
 * Formats models into clean arrays for the Inertia frontend, in the current locale.
 */
class Presenter
{
    public static function locale(): string
    {
        return app()->getLocale();
    }

    public static function postCard(Post $post): array
    {
        $t = $post->translation(self::locale()) ?? $post->translation('ar');

        return [
            'id' => $post->id,
            'slug' => $post->slug,
            'title' => $t?->title ?? '',
            'excerpt' => $t?->excerpt ?? self::excerptFrom($t?->body),
            'image' => $post->featuredImage ? asset('storage/' . $post->featuredImage->path) : null,
            'category' => $post->category ? self::categoryBrief($post->category) : null,
            'views' => $post->view_count,
            'date' => $post->published_at?->translatedFormat('d F Y'),
            'is_featured' => $post->is_featured,
        ];
    }

    public static function postFull(Post $post): array
    {
        $t = $post->translation(self::locale()) ?? $post->translation('ar');

        return [
            'id' => $post->id,
            'slug' => $post->slug,
            'type' => $post->type,
            'title' => $t?->title ?? '',
            'subtitle' => $t?->subtitle,
            'body' => $t?->body ?? '',
            'excerpt' => $t?->excerpt,
            'meta_title' => $t?->meta_title,
            'meta_description' => $t?->meta_description ?? self::excerptFrom($t?->body),
            'image' => $post->featuredImage ? asset('storage/' . $post->featuredImage->path) : null,
            'category' => $post->category ? self::categoryBrief($post->category) : null,
            'author' => $post->author?->name,
            'views' => $post->view_count,
            'date' => $post->type === 'page' ? null : $post->published_at?->translatedFormat('d F Y'),
            'gallery' => $post->galleryMedia->map(fn ($m) => [
                'url' => asset('storage/' . $m->path),
                'alt' => $m->alt_text,
            ])->values(),
            'attachments' => $post->attachments->map(fn ($m) => [
                'url' => asset('storage/' . $m->path),
                'name' => $m->original_name,
                'type' => strtoupper(pathinfo($m->path, PATHINFO_EXTENSION)),
                'size' => self::humanSize($m->size),
            ])->values(),
            'has_translation' => [
                'ar' => $post->translation('ar') !== null,
                'en' => $post->translation('en') !== null,
            ],
        ];
    }

    public static function categoryBrief(Category $category): array
    {
        $t = $category->translation(self::locale()) ?? $category->translation('ar');

        return [
            'id' => $category->id,
            'slug' => $category->slug,
            'name' => $t?->name ?? '',
        ];
    }

    public static function categoryTree(Category $category): array
    {
        return [
            ...self::categoryBrief($category),
            'posts_count' => $category->posts_count ?? $category->posts()->published()->count(),
            'children' => $category->children
                ->where('is_visible', true)
                ->map(fn ($c) => self::categoryBrief($c))
                ->values(),
        ];
    }

    public static function report(Report $report): array
    {
        $locale = self::locale();

        return [
            'id' => $report->id,
            'title' => $report->{"title_{$locale}"} ?: $report->title_ar,
            'description' => $report->{"description_{$locale}"} ?: $report->description_ar,
            'cover' => $report->cover_image_path ? asset('storage/' . $report->cover_image_path) : null,
            'pdf' => $report->{"pdf_{$locale}_path"}
                ? asset('storage/' . $report->{"pdf_{$locale}_path"})
                : ($report->pdf_ar_path ? asset('storage/' . $report->pdf_ar_path) : null),
            'year' => $report->year,
            'category' => $report->category,
        ];
    }

    private static function excerptFrom(?string $html, int $length = 160): string
    {
        if (! $html) {
            return '';
        }
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($html)));

        return mb_strlen($text) > $length ? mb_substr($text, 0, $length) . '…' : $text;
    }

    private static function humanSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }

        return max(1, (int) round($bytes / 1024)) . ' KB';
    }
}
