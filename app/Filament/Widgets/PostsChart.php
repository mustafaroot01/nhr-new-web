<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class PostsChart extends ChartWidget
{
    protected static ?string $heading = 'المقالات المنشورة (آخر 6 أشهر)';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $labels = [];
        $counts = [];

        $months = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل', 5 => 'مايو', 6 => 'يونيو',
            7 => 'يوليو', 8 => 'أغسطس', 9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $months[$date->month] . ' ' . $date->year;
            $counts[] = Post::where('type', 'post')
                ->whereYear('published_at', $date->year)
                ->whereMonth('published_at', $date->month)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'عدد المقالات',
                    'data' => $counts,
                    'borderColor' => '#1a5276',
                    'backgroundColor' => 'rgba(26, 82, 118, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
