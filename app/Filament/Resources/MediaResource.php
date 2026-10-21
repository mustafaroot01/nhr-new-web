<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Models\Media;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MediaResource extends Resource
{
    use \App\Filament\Concerns\HasPermissionGate;

    protected static ?string $model = Media::class;

    protected static function permissionResource(): string
    {
        return 'media';
    }

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'الوسائط';

    protected static ?string $modelLabel = 'ملف';

    protected static ?string $pluralModelLabel = 'الوسائط';

    protected static ?string $navigationGroup = 'المحتوى';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('path')
                    ->label('الملف')
                    ->required()
                    ->disk('public')
                    ->directory('media/uploads')
                    ->maxSize(10240)
                    ->acceptedFileTypes([
                        'image/jpeg', 'image/png', 'image/webp', 'image/gif',
                        'application/pdf', 'video/mp4',
                    ])
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('alt_text')
                    ->label('النص البديل')
                    ->maxLength(255),
                Forms\Components\TextInput::make('original_name')
                    ->label('اسم الملف الأصلي')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([12, 25, 50, 100])
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->emptyStateHeading('لا توجد وسائط')
            ->emptyStateDescription('ارفع أول صورة أو ملف')
            ->emptyStateIcon('heroicon-o-photo')
            ->columns([
                Tables\Columns\ImageColumn::make('path')
                    ->label('معاينة')
                    ->disk('public')
                    ->height(50)
                    ->width(65)
                    ->extraImgAttributes(['style' => 'object-fit: cover; border-radius: 6px;'])
                    ->defaultImageUrl(asset('favicon.png'))
                    ->visibility('public'),
                Tables\Columns\TextColumn::make('original_name')
                    ->label('اسم الملف')
                    ->searchable()
                    ->weight('medium')
                    ->limit(45),
                Tables\Columns\TextColumn::make('mime_type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match (true) {
                        str_starts_with($state, 'image/') => 'صورة',
                        str_starts_with($state, 'video/') => 'فيديو',
                        $state === 'application/pdf' => 'PDF',
                        str_contains($state, 'word') => 'Word',
                        default => 'ملف',
                    })
                    ->color(fn (string $state) => match (true) {
                        str_starts_with($state, 'image/') => 'success',
                        str_starts_with($state, 'video/') => 'info',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('size')
                    ->label('الحجم')
                    ->formatStateUsing(fn (int $state) => $state >= 1048576
                        ? number_format($state / 1048576, 1) . ' MB'
                        : number_format($state / 1024, 0) . ' KB')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        'image' => 'صور',
                        'video' => 'فيديو',
                        'document' => 'مستندات',
                    ])
                    ->query(fn ($query, array $data) => match ($data['value'] ?? null) {
                        'image' => $query->where('mime_type', 'like', 'image/%'),
                        'video' => $query->where('mime_type', 'like', 'video/%'),
                        'document' => $query->whereIn('mime_type', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']),
                        default => $query,
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->label('إجراءات')
                    ->icon('heroicon-m-ellipsis-horizontal')
                    ->button()
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedia::route('/'),
            'create' => Pages\CreateMedia::route('/create'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }
}
