<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class MostViewedPosts extends TableWidget
{
    protected static ?string $heading = 'الأكثر مشاهدة';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = ['lg' => 1, 'default' => 'full'];

    public function table(Table $table): Table
    {
        return $table
            ->query(Post::query()->where('type', 'post')->orderByDesc('view_count')->limit(5))
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('المقال')
                    ->getStateUsing(fn (Post $record) => $record->translation('ar')?->title ?? $record->slug)
                    ->limit(40)
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('view_count')
                    ->label('المشاهدات')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-m-eye'),
            ]);
    }
}
