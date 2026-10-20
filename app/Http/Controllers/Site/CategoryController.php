<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\Presenter;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function show(string $locale, string $slug): Response
    {
        $category = Category::where('slug', $slug)
            ->where('is_visible', true)
            ->with('translations')
            ->firstOrFail();

        $posts = $category->posts()->published()
            ->with(['featuredImage', 'category.translations', 'translations'])
            ->latest('published_at')
            ->paginate(12)
            ->through(fn ($p) => Presenter::postCard($p));

        return Inertia::render('Category', [
            'category' => Presenter::categoryBrief($category),
            'posts' => $posts,
        ]);
    }
}
