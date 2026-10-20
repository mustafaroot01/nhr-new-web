<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Support\Presenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Report::where('is_published', true);

        if ($year = $request->integer('year')) {
            $query->where('year', $year);
        }

        $reports = $query->orderByDesc('year')->orderBy('sort_order')
            ->paginate(12)
            ->through(fn ($r) => Presenter::report($r));

        $years = Report::where('is_published', true)
            ->distinct()->orderByDesc('year')->pluck('year');

        return Inertia::render('Reports', [
            'reports' => $reports,
            'years' => $years,
            'activeYear' => $request->integer('year') ?: null,
        ]);
    }
}
