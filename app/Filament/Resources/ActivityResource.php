<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class ActivityResource extends Resource
{
    use \App\Filament\Concerns\HasPermissionGate;

    protected static ?string $model = Activity::class;

    protected static function permissionResource(): string
    {
        return 'activity';
    }

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'سجل النشاط';

    protected static ?string $modelLabel = 'نشاط';

    protected static ?string $pluralModelLabel = 'سجل النشاط';

    protected static ?string $navigationGroup = 'النظام';

    protected static ?int $navigationSort = 21;

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([25, 50, 100])
            ->persistFiltersInSession()
            ->emptyStateHeading('لا يوجد نشاط مسجّل')
            ->emptyStateDescription('ستظهر هنا كل العمليات: من أنشأ، عدّل، أو حذف')
            ->emptyStateIcon('heroicon-o-clock')
            ->columns([
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('المستخدم')
                    ->default('النظام')
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-m-user'),
                Tables\Columns\TextColumn::make('description')
                    ->label('العملية')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'أنشأ' => 'success',
                        'عدّل' => 'warning',
                        'حذف' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('log_name')
                    ->label('النوع')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('subject_title')
                    ->label('العنصر')
                    ->getStateUsing(function (Activity $record) {
                        $subject = $record->subject;
                        if (! $subject) {
                            return '#' . $record->subject_id;
                        }
                        if (method_exists($subject, 'translation')) {
                            return $subject->translation('ar')?->title
                                ?? $subject->translation('ar')?->name
                                ?? ('#' . $subject->id);
                        }

                        return '#' . ($subject->id ?? $record->subject_id);
                    })
                    ->limit(45)
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('الوقت')
                    ->dateTime('Y-m-d H:i')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('description')
                    ->label('العملية')
                    ->options([
                        'أنشأ' => 'إنشاء',
                        'عدّل' => 'تعديل',
                        'حذف' => 'حذف',
                    ]),
                Tables\Filters\SelectFilter::make('log_name')
                    ->label('النوع')
                    ->options([
                        'مقال' => 'مقال',
                        'صفحة' => 'صفحة',
                        'تصنيف' => 'تصنيف',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('التفاصيل')
                    ->modalHeading('تفاصيل النشاط')
                    ->infolist([
                        \Filament\Infolists\Components\TextEntry::make('causer.name')->label('المستخدم')->default('النظام'),
                        \Filament\Infolists\Components\TextEntry::make('description')->label('العملية'),
                        \Filament\Infolists\Components\TextEntry::make('log_name')->label('النوع'),
                        \Filament\Infolists\Components\KeyValueEntry::make('properties.attributes')->label('القيم الجديدة'),
                        \Filament\Infolists\Components\KeyValueEntry::make('properties.old')->label('القيم القديمة'),
                        \Filament\Infolists\Components\TextEntry::make('created_at')->label('الوقت')->dateTime('Y-m-d H:i:s'),
                    ]),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
        ];
    }
}
