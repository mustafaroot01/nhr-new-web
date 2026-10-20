<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function index(): Response
    {
        $locale = app()->getLocale();

        $faqs = Faq::where('is_visible', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Faq $f) => [
                'question' => $f->{"question_{$locale}"} ?: $f->question_ar,
                'answer' => $f->{"answer_{$locale}"} ?: $f->answer_ar,
            ]);

        return Inertia::render('Faq', [
            'faqs' => $faqs,
        ]);
    }
}
