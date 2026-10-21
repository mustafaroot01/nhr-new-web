<?php

namespace App\Filament\Widgets;

use App\Models\ContactSubmission;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestContacts extends TableWidget
{
    protected static ?string $heading = 'آخر رسائل التواصل';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = ['lg' => 1, 'default' => 'full'];

    public function table(Table $table): Table
    {
        return $table
            ->query(ContactSubmission::query()->latest()->limit(5))
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('المرسل')->weight('medium'),
                Tables\Columns\TextColumn::make('subject')->label('الموضوع')->limit(30),
                Tables\Columns\IconColumn::make('is_read')
                    ->label('')
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope-open')
                    ->falseIcon('heroicon-s-envelope')
                    ->falseColor('warning'),
                Tables\Columns\TextColumn::make('created_at')->label('منذ')->since(),
            ]);
    }
}
