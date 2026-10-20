<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Post;
use App\Models\Report;
use App\Support\Presenter;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $locale = app()->getLocale();

        $banners = Banner::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Banner $b) => [
                'type' => $b->media_type,
                'image' => $b->image_path ? asset('storage/' . $b->image_path) : null,
                'video' => $b->video_path ? asset('storage/' . $b->video_path) : null,
                'title' => $b->{"title_{$locale}"} ?: $b->title_ar,
                'subtitle' => $b->{"subtitle_{$locale}"} ?: $b->subtitle_ar,
                'link' => $b->link,
                'button' => $b->{"button_text_{$locale}"} ?: $b->button_text_ar,
            ]);

        $featured = Post::published()->posts()->featured()
            ->with(['featuredImage', 'category.translations', 'translations'])
            ->latest('published_at')->take(5)->get()
            ->map(fn ($p) => Presenter::postCard($p));

        $latest = Post::published()->posts()
            ->with(['featuredImage', 'category.translations', 'translations'])
            ->latest('published_at')->take(9)->get()
            ->map(fn ($p) => Presenter::postCard($p));

        // One section per visible root category, with its latest posts
        $sections = Category::whereNull('parent_id')
            ->where('is_visible', true)
            ->with('translations')
            ->orderBy('sort_order')
            ->get()
            ->map(function (Category $cat) {
                $posts = $cat->posts()->published()
                    ->with(['featuredImage', 'category.translations', 'translations'])
                    ->latest('published_at')->take(4)->get();

                if ($posts->isEmpty()) {
                    return null;
                }

                return [
                    'category' => Presenter::categoryBrief($cat),
                    'posts' => $posts->map(fn ($p) => Presenter::postCard($p))->values(),
                ];
            })
            ->filter()
            ->values();

        $reports = Report::where('is_published', true)
            ->orderByDesc('year')->orderBy('sort_order')->take(4)->get()
            ->map(fn ($r) => Presenter::report($r));

        return Inertia::render('Home', [
            'banners' => $banners,
            'featured' => $featured,
            'latest' => $latest,
            'sections' => $sections,
            'reports' => $reports,
        ]);
    }
}
