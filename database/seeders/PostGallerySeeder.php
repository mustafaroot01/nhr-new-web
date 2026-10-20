<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PostGallerySeeder extends Seeder
{
    private int $galleryCount = 0;

    private array $mediaByPath = [];

    public function run(): void
    {
        $posts = Post::where('type', 'post')->with(['translations', 'featuredImage'])->get();

        foreach ($posts as $post) {
            $this->processPost($post);
        }

        $this->command->info("صور أُضيفت لمعارض المنشورات: {$this->galleryCount}");
    }

    private function processPost(Post $post): void
    {
        $featuredPath = $post->featuredImage?->path;
        $orderedPaths = [];

        foreach ($post->translations as $translation) {
            if (! $translation->body || ! str_contains($translation->body, '/storage/media/posts')) {
                continue;
            }

            // Collect image paths in order of appearance
            preg_match_all(
                '#<img[^>]+src="/storage/(media/posts/[^"]+?\.(?:jpe?g|png|webp|gif))"[^>]*>#i',
                $translation->body,
                $matches
            );
            foreach ($matches[1] as $path) {
                if ($path !== $featuredPath) {
                    $orderedPaths[$path] = true;
                }
            }

            // Strip the images (and now-empty wrappers) from the body
            $cleaned = $this->stripImages($translation->body);
            if ($cleaned !== $translation->body) {
                $translation->update(['body' => $cleaned]);
            }
        }

        $sortOrder = 0;
        foreach (array_keys($orderedPaths) as $relPath) {
            $media = $this->resolveMedia($relPath);
            if (! $media) {
                continue;
            }

            if (! $post->galleryMedia()->where('media_id', $media->id)->exists()) {
                $post->galleryMedia()->attach($media->id, [
                    'type' => 'gallery',
                    'sort_order' => $sortOrder++,
                ]);
                $this->galleryCount++;
            }
        }
    }

    private function stripImages(string $body): string
    {
        // Remove <figure>…<img …/storage/media/posts…>…</figure> blocks
        $body = preg_replace(
            '#<figure[^>]*>(?:(?!</figure>).)*?<img[^>]+/storage/media/posts[^>]*>.*?</figure>#is',
            '',
            $body
        );

        // Remove <a …><img …/storage/media/posts…></a> (image links)
        $body = preg_replace(
            '#<a[^>]*>\s*<img[^>]+/storage/media/posts[^>]*>\s*</a>#is',
            '',
            $body
        );

        // Remove standalone <img …/storage/media/posts…>
        $body = preg_replace(
            '#<img[^>]+src="/storage/media/posts/[^"]+?\.(?:jpe?g|png|webp|gif)"[^>]*>#i',
            '',
            $body
        );

        // Collapse wrappers left empty by the removal
        $body = preg_replace('#<div[^>]*dir="auto"[^>]*>\s*</div>#i', '', $body);
        $body = preg_replace('#<p>\s*</p>#i', '', $body);
        $body = preg_replace('#<figure[^>]*>\s*</figure>#i', '', $body);
        $body = preg_replace("/\n{3,}/", "\n\n", $body);

        return trim($body);
    }

    private function resolveMedia(string $relPath): ?Media
    {
        if (isset($this->mediaByPath[$relPath])) {
            return $this->mediaByPath[$relPath];
        }

        $existing = Media::where('path', $relPath)->first();
        if ($existing) {
            return $this->mediaByPath[$relPath] = $existing;
        }

        $fullPath = storage_path('app/public/' . $relPath);
        if (! File::exists($fullPath)) {
            return null;
        }

        [$width, $height] = @getimagesize($fullPath) ?: [null, null];

        $media = Media::create([
            'path' => $relPath,
            'filename' => basename($relPath),
            'original_name' => basename($relPath),
            'mime_type' => $this->mimeFromExt($relPath),
            'size' => File::size($fullPath),
            'width' => $width,
            'height' => $height,
            'uploaded_by' => 1,
        ]);

        return $this->mediaByPath[$relPath] = $media;
    }

    private function mimeFromExt(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'application/octet-stream',
        };
    }
}
