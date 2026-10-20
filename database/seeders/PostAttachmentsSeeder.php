<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\Post;
use App\Models\PostTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PostAttachmentsSeeder extends Seeder
{
    private int $attachedCount = 0;

    private array $mediaByPath = [];

    public function run(): void
    {
        $posts = Post::where('type', 'post')->with('translations')->get();

        foreach ($posts as $post) {
            $this->processPost($post);
        }

        $this->command->info("مرفقات أُضيفت للمنشورات: {$this->attachedCount}");
    }

    private function processPost(Post $post): void
    {
        $filePaths = [];

        foreach ($post->translations as $translation) {
            if (! $translation->body) {
                continue;
            }

            // Match local document links: /storage/media/posts/.../file.pdf|doc|docx
            preg_match_all(
                '#/storage/(media/posts/[^"\'\s)]+?\.(?:pdf|docx?|xlsx?|pptx?))#i',
                $translation->body,
                $matches
            );

            foreach ($matches[1] as $path) {
                $filePaths[$path] = true;
            }
        }

        $sortOrder = 0;
        foreach (array_keys($filePaths) as $relPath) {
            $media = $this->resolveMedia($relPath);
            if (! $media) {
                continue;
            }

            // Attach as 'attachment' if not already linked
            $exists = $post->attachments()->where('media_id', $media->id)->exists();
            if (! $exists) {
                $post->attachments()->attach($media->id, [
                    'type' => 'attachment',
                    'sort_order' => $sortOrder++,
                ]);
                $this->attachedCount++;
            }
        }
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

        $media = Media::create([
            'path' => $relPath,
            'filename' => basename($relPath),
            'original_name' => basename($relPath),
            'mime_type' => $this->mimeFromExt($relPath),
            'size' => File::size($fullPath),
            'uploaded_by' => 1,
        ]);

        return $this->mediaByPath[$relPath] = $media;
    }

    private function mimeFromExt(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
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
