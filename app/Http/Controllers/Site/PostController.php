<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Support\Presenter;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(): Response
    {
        $posts = Post::published()->posts()
            ->with(['featuredImage', 'category.translations', 'translations'])
            ->latest('published_at')
            ->paginate(12)
            ->through(fn ($p) => Presenter::postCard($p));

        return Inertia::render('Posts/Index', [
            'posts' => $posts,
        ]);
    }

    public function show(string $locale, string $slug): Response
    {
        $post = Post::published()->posts()
            ->where('slug', $slug)
            ->with([
                'featuredImage', 'author', 'category.translations',
                'translations', 'galleryMedia', 'attachments',
            ])
            ->firstOrFail();

        // Increment view count only once per session per post
        $viewedKey = 'viewed_posts.' . $post->id;
        if (! session()->has($viewedKey)) {
            $post->incrementViews();
            session()->put($viewedKey, true);
        }

        $related = $post->category
            ? $post->category->posts()->published()
                ->where('id', '!=', $post->id)
                ->with(['featuredImage', 'category.translations', 'translations'])
                ->latest('published_at')->take(3)->get()
                ->map(fn ($p) => Presenter::postCard($p))
            : collect();

        return Inertia::render('Posts/Show', [
            'post' => Presenter::postFull($post),
            'related' => $related,
        ]);
    }
}
