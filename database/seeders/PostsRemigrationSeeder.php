<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostsRemigrationSeeder extends Seeder
{
    private $wpDb;

    private array $enPairOfAr = [];

    public function run(): void
    {
        $this->wpDb = DB::connection('wordpress');

        // Clear existing articles (keep pages + categories)
        $postIds = Post::where('type', 'post')->pluck('id');
        PostTranslation::whereIn('post_id', $postIds)->delete();
        Post::where('type', 'post')->forceDelete();

        $this->buildTranslationMap();

        $arPosts = $this->wpDb->table('wp_posts as p')
            ->join('wp_term_relationships as tr', 'p.ID', '=', 'tr.object_id')
            ->join('wp_term_taxonomy as tt', 'tr.term_taxonomy_id', '=', 'tt.term_taxonomy_id')
            ->join('wp_terms as t', 'tt.term_id', '=', 't.term_id')
            ->where('p.post_type', 'post')
            ->where('p.post_status', 'publish')
            ->where('tt.taxonomy', 'language')
            ->where('t.slug', 'ar')
            ->select('p.*')
            ->distinct()
            ->get();

        foreach ($arPosts as $arPost) {
            $this->migratePost($arPost);
        }
    }

    private function buildTranslationMap(): void
    {
        $descriptions = $this->wpDb->table('wp_term_taxonomy')
            ->where('taxonomy', 'post_translations')
            ->pluck('description');

        foreach ($descriptions as $desc) {
            $pair = @unserialize($desc);
            if ($pair && isset($pair['ar']) && isset($pair['en'])) {
                $this->enPairOfAr[$pair['ar']] = $pair['en'];
            }
        }
    }

    private function migratePost(object $arPost): void
    {
        $slug = urldecode($arPost->post_name);
        if (strlen($slug) > 190) {
            $slug = Str::slug(mb_substr($arPost->post_title, 0, 60)) ?: 'post-' . $arPost->ID;
        }
        if (Post::where('slug', $slug)->exists()) {
            $slug .= '-' . $arPost->ID;
        }

        $categoryId = $this->resolveCategory($arPost->ID);

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

        $enId = $this->enPairOfAr[$arPost->ID] ?? null;
        if ($enId) {
            $enPost = $this->wpDb->table('wp_posts')
                ->where('ID', $enId)
                ->where('post_status', 'publish')
                ->first();

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

    /**
     * Pick the most specific category: prefer a child category over its parent.
     */
    private function resolveCategory(int $wpPostId): ?int
    {
        $wpCatNames = $this->wpDb->table('wp_term_relationships as tr')
            ->join('wp_term_taxonomy as tt', 'tr.term_taxonomy_id', '=', 'tt.term_taxonomy_id')
            ->join('wp_terms as t', 'tt.term_id', '=', 't.term_id')
            ->where('tr.object_id', $wpPostId)
            ->where('tt.taxonomy', 'category')
            ->pluck('t.name')
            ->toArray();

        if (empty($wpCatNames)) {
            return null;
        }

        $matched = Category::whereHas('translations', fn ($q) => $q->whereIn('name', $wpCatNames))->get();

        if ($matched->isEmpty()) {
            return null;
        }

        // Prefer a child category (parent_id not null) if present
        $child = $matched->firstWhere('parent_id', '!=', null);

        return ($child ?? $matched->first())->id;
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
