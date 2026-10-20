<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Post;
use App\Models\PostTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WordPressMigrationSeeder extends Seeder
{
    private $wpDb;

    public function run(): void
    {
        $this->wpDb = DB::connection('wordpress');

        $this->migrateCategories();
        $this->migratePages();
        $this->migratePosts();
    }

    private function migrateCategories(): void
    {
        $termTranslations = $this->wpDb->table('wp_term_taxonomy')
            ->where('taxonomy', 'term_translations')
            ->pluck('description');

        $categoryPairs = [];
        foreach ($termTranslations as $desc) {
            $pair = @unserialize($desc);
            if ($pair && isset($pair['ar'])) {
                $categoryPairs[] = $pair;
            }
        }

        foreach ($categoryPairs as $pair) {
            $arId = $pair['ar'] ?? null;
            $enId = $pair['en'] ?? null;

            $arCat = $arId ? $this->wpDb->table('wp_terms')
                ->join('wp_term_taxonomy', 'wp_terms.term_id', '=', 'wp_term_taxonomy.term_id')
                ->where('wp_terms.term_id', $arId)
                ->where('wp_term_taxonomy.taxonomy', 'category')
                ->first() : null;

            if (! $arCat) {
                continue;
            }

            $enCat = $enId ? $this->wpDb->table('wp_terms')
                ->join('wp_term_taxonomy', 'wp_terms.term_id', '=', 'wp_term_taxonomy.term_id')
                ->where('wp_terms.term_id', $enId)
                ->where('wp_term_taxonomy.taxonomy', 'category')
                ->first() : null;

            $slug = urldecode($arCat->slug);
            if (strlen($slug) > 100) {
                $slug = Str::slug($arCat->name);
            }

            $parentId = null;
            if ($arCat->parent > 0) {
                $parentCat = Category::whereHas('translations', function ($q) use ($arCat) {
                    $parentName = $this->wpDb->table('wp_terms')->where('term_id', $arCat->parent)->value('name');
                    $q->where('locale', 'ar')->where('name', $parentName);
                })->first();
                $parentId = $parentCat?->id;
            }

            $category = Category::create([
                'slug' => $slug,
                'parent_id' => $parentId,
                'is_visible' => true,
                'sort_order' => 0,
            ]);

            CategoryTranslation::create([
                'category_id' => $category->id,
                'locale' => 'ar',
                'name' => $arCat->name,
            ]);

            if ($enCat) {
                CategoryTranslation::create([
                    'category_id' => $category->id,
                    'locale' => 'en',
                    'name' => $enCat->name,
                ]);
            }
        }
    }

    private function migratePages(): void
    {
        $postTranslations = $this->wpDb->table('wp_term_taxonomy')
            ->where('taxonomy', 'post_translations')
            ->pluck('description');

        foreach ($postTranslations as $desc) {
            $pair = @unserialize($desc);
            if (! $pair || ! isset($pair['ar'])) {
                continue;
            }

            $arPage = $this->wpDb->table('wp_posts')
                ->where('ID', $pair['ar'])
                ->where('post_type', 'page')
                ->where('post_status', 'publish')
                ->first();

            if (! $arPage) {
                continue;
            }

            $enPage = isset($pair['en']) ? $this->wpDb->table('wp_posts')
                ->where('ID', $pair['en'])
                ->where('post_type', 'page')
                ->where('post_status', 'publish')
                ->first() : null;

            $slug = urldecode($arPage->post_name);
            if ($slug === urlencode($slug) || strlen($slug) > 100) {
                $slug = Str::slug($arPage->post_title) ?: 'page-' . $arPage->ID;
            }

            if (Post::where('slug', $slug)->exists()) {
                continue;
            }

            $post = Post::create([
                'type' => 'page',
                'slug' => $slug,
                'status' => 'published',
                'author_id' => 1,
                'published_at' => $arPage->post_date,
            ]);

            PostTranslation::create([
                'post_id' => $post->id,
                'locale' => 'ar',
                'title' => $arPage->post_title,
                'body' => $this->cleanHtml($arPage->post_content),
            ]);

            if ($enPage && ! empty($enPage->post_title)) {
                PostTranslation::create([
                    'post_id' => $post->id,
                    'locale' => 'en',
                    'title' => $enPage->post_title,
                    'body' => $this->cleanHtml($enPage->post_content),
                ]);
            }
        }
    }

    private function migratePosts(): void
    {
        $postTranslations = $this->wpDb->table('wp_term_taxonomy')
            ->where('taxonomy', 'post_translations')
            ->pluck('description');

        $migratedArIds = [];

        foreach ($postTranslations as $desc) {
            $pair = @unserialize($desc);
            if (! $pair || ! isset($pair['ar'])) {
                continue;
            }

            $arPost = $this->wpDb->table('wp_posts')
                ->where('ID', $pair['ar'])
                ->where('post_type', 'post')
                ->where('post_status', 'publish')
                ->first();

            if (! $arPost) {
                continue;
            }

            $migratedArIds[] = $pair['ar'];

            $enPost = isset($pair['en']) ? $this->wpDb->table('wp_posts')
                ->where('ID', $pair['en'])
                ->where('post_type', 'post')
                ->where('post_status', 'publish')
                ->first() : null;

            $slug = urldecode($arPost->post_name);
            if (strlen($slug) > 200) {
                $slug = Str::slug(mb_substr($arPost->post_title, 0, 80)) ?: 'post-' . $arPost->ID;
            }

            if (Post::where('slug', $slug)->exists()) {
                $slug .= '-' . $arPost->ID;
            }

            $categoryId = $this->findCategoryId($arPost->ID);

            $post = Post::create([
                'type' => 'post',
                'slug' => $slug,
                'status' => 'published',
                'author_id' => 1,
                'category_id' => $categoryId,
                'published_at' => $arPost->post_date,
                'view_count' => 0,
            ]);

            PostTranslation::create([
                'post_id' => $post->id,
                'locale' => 'ar',
                'title' => $arPost->post_title,
                'body' => $this->cleanHtml($arPost->post_content),
                'excerpt' => $arPost->post_excerpt ?: null,
            ]);

            if ($enPost && ! empty($enPost->post_title)) {
                PostTranslation::create([
                    'post_id' => $post->id,
                    'locale' => 'en',
                    'title' => $enPost->post_title,
                    'body' => $this->cleanHtml($enPost->post_content),
                    'excerpt' => $enPost->post_excerpt ?: null,
                ]);
            }
        }
    }

    private function findCategoryId(int $wpPostId): ?int
    {
        $wpCatId = $this->wpDb->table('wp_term_relationships')
            ->join('wp_term_taxonomy', 'wp_term_relationships.term_taxonomy_id', '=', 'wp_term_taxonomy.term_taxonomy_id')
            ->where('wp_term_relationships.object_id', $wpPostId)
            ->where('wp_term_taxonomy.taxonomy', 'category')
            ->value('wp_term_taxonomy.term_id');

        if (! $wpCatId) {
            return null;
        }

        $wpCatName = $this->wpDb->table('wp_terms')->where('term_id', $wpCatId)->value('name');

        $cat = Category::whereHas('translations', fn ($q) => $q->where('name', $wpCatName))->first();

        return $cat?->id;
    }

    private function cleanHtml(string $content): string
    {
        $content = preg_replace('/<!--\s*wp:[^>]*-->/', '', $content);
        $content = preg_replace('/<!--\s*\/wp:[^>]*-->/', '', $content);
        $content = preg_replace('/\[contact-form-7[^\]]*\]/', '', $content);
        $content = preg_replace('/\[[^\]]*\]/', '', $content);
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $content = preg_replace('/\n{3,}/', "\n\n", $content);

        return trim($content);
    }
}
