<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPermissionGate;
use App\Filament\Resources\LoginLogResource\Pages;
use App\Models\LoginLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LoginLogResource extends Resource
{
    use HasPermissionGate;

    protected static ?string $model = LoginLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-finger-print';

    protected static ?string $navigationLabel = 'سجل الدخول';

    protected static ?string $modelLabel = 'محاولة دخول';

    protected static ?string $pluralModelLabel = 'سجل الدخول';

    protected static ?string $navigationGroup = 'النظام';

    protected static ?int $navigationSort = 22;

    protected static function permissionResource(): string
    {
        return 'login_logs';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(50)
            ->persistFiltersInSession()
            ->emptyStateHeading('لا توجد سجلات دخول')
            ->emptyStateIcon('heroicon-o-finger-print')
            ->columns([
                Tables\Columns\IconColumn::make('successful')
                    ->label('')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('email')->label('الإيميل')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('المستخدم')->placeholder('—'),
                Tables\Columns\TextColumn::make('ip_address')->label('IP')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('user_agent')->label('المتصفح')->limit(40)->tooltip(fn ($state) => $state)->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->label('الوقت')->dateTime('Y-m-d H:i:s')->since()->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('successful')
                    ->label('النتيجة')
                    ->trueLabel('ناجحة')
                    ->falseLabel('فاشلة'),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoginLogs::route('/'),
        ];
    }
}
