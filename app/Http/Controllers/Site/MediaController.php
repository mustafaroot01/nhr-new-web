<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Inertia\Inertia;
use Inertia\Response;

class MediaController extends Controller
{
    public function index(): Response
    {
        $locale = app()->getLocale();

        // Gallery images across published posts, each linked back to its post.
        $images = Post::published()->posts()
            ->with(['galleryMedia', 'translations'])
            ->latest('published_at')
            ->take(60)
            ->get()
            ->flatMap(function (Post $post) use ($locale) {
                $title = $post->translation($locale)?->title ?? $post->translation('ar')?->title;

                return $post->galleryMedia->map(fn ($m) => [
                    'url' => asset('storage/' . $m->path),
                    'post_slug' => $post->slug,
                    'post_title' => $title,
                ]);
            })
            ->take(48)
            ->values();

        return Inertia::render('Media', [
            'images' => $images,
        ]);
    }
}
