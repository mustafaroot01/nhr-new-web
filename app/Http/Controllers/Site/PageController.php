<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Support\Presenter;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function show(string $locale, string $slug): Response
    {
        $page = Post::published()->pages()
            ->where('slug', $slug)
            ->with('translations')
            ->firstOrFail();

        if (in_array($slug, ['من-نحن', 'about-us', 'about'])) {
            return Inertia::render('About', [
                'page' => Presenter::postFull($page),
            ]);
        }

        return Inertia::render('Page', [
            'page' => Presenter::postFull($page),
        ]);
    }
}
