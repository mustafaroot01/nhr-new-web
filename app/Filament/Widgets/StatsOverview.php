<?php

namespace App\Filament\Widgets;

use App\Models\ContactSubmission;
use App\Models\Media;
use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $publishedPosts = Post::where('type', 'post')->where('status', 'published')->count();
        $totalViews = (int) Post::sum('view_count');
        $unreadMessages = ContactSubmission::where('is_read', false)->count();
        $mediaCount = Media::count();

        return [
            Stat::make('المقالات المنشورة', $publishedPosts)
                ->description('مقال منشور على الموقع')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),
            Stat::make('إجمالي المشاهدات', number_format($totalViews))
                ->description('مشاهدة لكل المقالات')
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),
            Stat::make('رسائل غير مقروءة', $unreadMessages)
                ->description($unreadMessages > 0 ? 'بحاجة لمراجعة' : 'لا رسائل جديدة')
                ->descriptionIcon('heroicon-m-envelope')
                ->color($unreadMessages > 0 ? 'danger' : 'gray'),
            Stat::make('ملفات الوسائط', number_format($mediaCount))
                ->description('صورة وملف')
                ->descriptionIcon('heroicon-m-photo')
                ->color('warning'),
        ];
    }
}
