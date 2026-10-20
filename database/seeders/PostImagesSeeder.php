<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\Post;
use App\Models\PostTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PostImagesSeeder extends Seeder
{
    private $wpDb;

    private string $wpUploads = '/Users/root1/Downloads/test/wp-content/uploads';

    private string $destBase;

    private array $copiedFiles = [];

    private int $featuredCount = 0;

    private int $inlineCount = 0;

    public function run(): void
    {
        $this->wpDb = DB::connection('wordpress');
        $this->destBase = storage_path('app/public/media/posts');
        File::ensureDirectoryExists($this->destBase);

        $map = $this->buildPostMap();
        $this->command->info('مقالات مطابقة: ' . count($map));

        foreach ($map as $newPostId => $wp) {
            $this->migrateFeaturedImage($newPostId, $wp['ar_id'], $wp['en_id']);
            $this->rewriteInlineImages($newPostId);
        }

        $this->command->info("صور رئيسية منقولة: {$this->featuredCount}");
        $this->command->info("صور محتوى منقولة: {$this->inlineCount}");
    }

    /**
     * Map new post id => [ar_id, en_id] by matching AR translation title to WP title.
     */
    private function buildPostMap(): array
    {
        $enPairOfAr = [];
        $descriptions = $this->wpDb->table('wp_term_taxonomy')
            ->where('taxonomy', 'post_translations')
            ->pluck('description');
        foreach ($descriptions as $desc) {
            $pair = @unserialize($desc);
            if ($pair && isset($pair['ar'])) {
                $enPairOfAr[$pair['ar']] = $pair['en'] ?? null;
            }
        }

        $map = [];
        $newPosts = Post::where('type', 'post')->with('translations')->get();

        foreach ($newPosts as $post) {
            $arTitle = $post->translation('ar')?->title;
            if (! $arTitle) {
                continue;
            }

            $wpPost = $this->wpDb->table('wp_posts')
                ->where('post_type', 'post')
                ->where('post_status', 'publish')
                ->where('post_title', $arTitle)
                ->first();

            if (! $wpPost) {
                continue;
            }

            $map[$post->id] = [
                'ar_id' => $wpPost->ID,
                'en_id' => $enPairOfAr[$wpPost->ID] ?? null,
            ];
        }

        return $map;
    }

    private function migrateFeaturedImage(int $newPostId, int $wpArId, ?int $wpEnId): void
    {
        $thumbId = $this->wpDb->table('wp_postmeta')
            ->where('post_id', $wpArId)
            ->where('meta_key', '_thumbnail_id')
            ->value('meta_value');

        if (! $thumbId && $wpEnId) {
            $thumbId = $this->wpDb->table('wp_postmeta')
                ->where('post_id', $wpEnId)
                ->where('meta_key', '_thumbnail_id')
                ->value('meta_value');
        }

        if (! $thumbId) {
            return;
        }

        $relPath = $this->wpDb->table('wp_postmeta')
            ->where('post_id', $thumbId)
            ->where('meta_key', '_wp_attached_file')
            ->value('meta_value');

        if (! $relPath) {
            return;
        }

        $newPath = $this->copyUpload($relPath);
        if (! $newPath) {
            return;
        }

        $media = Media::create([
            'path' => $newPath,
            'filename' => basename($newPath),
            'original_name' => basename($relPath),
            'mime_type' => $this->mimeFromExt($newPath),
            'size' => File::exists(storage_path('app/public/' . $newPath)) ? File::size(storage_path('app/public/' . $newPath)) : 0,
            'uploaded_by' => 1,
        ]);

        Post::where('id', $newPostId)->update(['featured_image_id' => $media->id]);
        $this->featuredCount++;
    }

    private function rewriteInlineImages(int $newPostId): void
    {
        $translations = PostTranslation::where('post_id', $newPostId)->get();

        foreach ($translations as $translation) {
            $body = $translation->body;
            if (! $body || ! str_contains($body, 'wp-content/uploads')) {
                continue;
            }

            $pattern = '#https?://[^"\'\s)]*?/wp-content/uploads/([^"\'\s)]+)#i';
            $body = preg_replace_callback($pattern, function ($matches) {
                $relPath = urldecode($matches[1]);
                $newPath = $this->copyUpload($relPath);
                if ($newPath) {
                    $this->inlineCount++;

                    return '/storage/' . $newPath;
                }

                return $matches[0];
            }, $body);

            if ($body !== $translation->body) {
                $translation->update(['body' => $body]);
            }
        }
    }

    /**
     * Copy a WP upload (relative path like 2024/01/file.png) into media/posts, return new relative path.
     */
    private function copyUpload(string $relPath): ?string
    {
        $relPath = ltrim($relPath, '/');

        if (isset($this->copiedFiles[$relPath])) {
            return $this->copiedFiles[$relPath];
        }

        $source = $this->wpUploads . '/' . $relPath;
        if (! File::exists($source)) {
            return null;
        }

        $newRel = 'media/posts/' . $relPath;
        $dest = storage_path('app/public/' . $newRel);
        File::ensureDirectoryExists(dirname($dest));

        if (! File::exists($dest)) {
            File::copy($source, $dest);
        }

        $this->copiedFiles[$relPath] = $newRel;

        return $newRel;
    }

    private function mimeFromExt(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            default => 'application/octet-stream',
        };
    }
}
