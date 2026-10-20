<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Support\Presenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function index(Request $request): Response
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
        ]);

        $q = trim((string) $request->query('q', ''));
        $results = collect();
        $total = 0;

        if (mb_strlen($q) >= 2) {
            // Escape special LIKE characters to prevent wildcard injection
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $q);

            $query = Post::published()->posts()
                ->whereHas('translations', fn ($t) => $t
                    ->where('title', 'like', '%' . $escaped . '%')
                    ->orWhere('body', 'like', '%' . $escaped . '%')
                )
                ->with(['featuredImage', 'category.translations', 'translations'])
                ->latest('published_at');

            $total = $query->count();
            $results = $query->take(30)->get()->map(fn ($p) => Presenter::postCard($p));
        }

        return Inertia::render('Search', [
            'query' => $q,
            'results' => $results,
            'total' => $total,
        ]);
    }
}
