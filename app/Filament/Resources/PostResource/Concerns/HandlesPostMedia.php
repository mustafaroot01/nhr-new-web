<?php

namespace App\Filament\Resources\PostResource\Concerns;

use App\Models\Media;
use App\Models\Post;
use Illuminate\Support\Facades\File;

trait HandlesPostMedia
{
    protected function syncFeaturedImage(Post $post, string|array|null $path): void
    {
        // FileUpload may hand back an array even for a single file.
        if (is_array($path)) {
            $path = reset($path) ?: null;
        }

        if (! $path) {
            return;
        }

        if ($path === $post->featuredImage?->path) {
            return;
        }

        $media = $this->resolveMedia($path);
        $post->update(['featured_image_id' => $media->id]);
    }

    /**
     * @param  array<int, string>  $paths
     */
    protected function syncGallery(Post $post, array $paths): void
    {
        $this->syncPivot($post, $paths, 'gallery');
    }

    /**
     * @param  array<int, string>  $paths
     */
    protected function syncAttachments(Post $post, array $paths): void
    {
        $this->syncPivot($post, $paths, 'attachment');
    }

    /**
     * @param  array<int, string>  $paths
     */
    private function syncPivot(Post $post, array $paths, string $type): void
    {
        $relation = $type === 'gallery' ? $post->galleryMedia() : $post->attachments();

        // Detach everything of this type, then re-attach in given order.
        $relation->wherePivot('type', $type)->detach();

        $sortOrder = 0;
        foreach (array_values(array_filter($paths)) as $path) {
            if (! is_string($path)) {
                continue;
            }
            $media = $this->resolveMedia($path);
            $relation->attach($media->id, ['type' => $type, 'sort_order' => $sortOrder++]);
        }
    }

    private function resolveMedia(string $path): Media
    {
        $existing = Media::where('path', $path)->first();
        if ($existing) {
            return $existing;
        }

        $fullPath = storage_path('app/public/' . $path);
        $exists = File::exists($fullPath);

        [$width, $height] = $exists ? (@getimagesize($fullPath) ?: [null, null]) : [null, null];

        return Media::create([
            'path' => $path,
            'filename' => basename($path),
            'original_name' => basename($path),
            'mime_type' => $this->mimeFromExt($path),
            'size' => $exists ? File::size($fullPath) : 0,
            'width' => $width,
            'height' => $height,
            'uploaded_by' => auth()->id(),
        ]);
    }

    private function mimeFromExt(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            default => 'application/octet-stream',
        };
    }
}
