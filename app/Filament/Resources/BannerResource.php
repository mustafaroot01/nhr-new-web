<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPermissionGate;
use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BannerResource extends Resource
{
    use HasPermissionGate;

    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationLabel = 'البانرات / السلايدر';

    protected static ?string $modelLabel = 'بانر';

    protected static ?string $pluralModelLabel = 'البانرات';

    protected static ?string $navigationGroup = 'المحتوى';

    protected static ?int $navigationSort = 4;

    protected static function permissionResource(): string
    {
        return 'banners';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('وسائط البانر')
                    ->schema([
                        Forms\Components\Radio::make('media_type')
                            ->label('نوع المحتوى')
                            ->options([
                                'image' => 'صورة',
                                'video' => 'فيديو',
                            ])
                            ->default('image')
                            ->inline()
                            ->live(),
                        Forms\Components\FileUpload::make('video_path')
                            ->label('فيديو البانر')
                            ->required(fn (Forms\Get $get) => $get('media_type') === 'video')
                            ->visible(fn (Forms\Get $get) => $get('media_type') === 'video')
                            ->disk('public')
                            ->directory('media/banners/videos')
                            ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg'])
                            ->maxSize(51200)
                            ->openable()
                            ->helperText('صيغة MP4 مفضّلة، حد أقصى 50 ميغا. يُفضّل فيديو قصير بدون صوت'),
                        Forms\Components\FileUpload::make('image_path')
                            ->label(fn (Forms\Get $get) => $get('media_type') === 'video' ? 'صورة بديلة (اختياري)' : 'صورة البانر')
                            ->image()
                            ->required(fn (Forms\Get $get) => $get('media_type') === 'image')
                            ->disk('public')
                            ->directory('media/banners')
                            ->imageEditor()
                            ->openable()
                            ->imagePreviewHeight('150')
                            ->maxSize(5120)
                            ->helperText(fn (Forms\Get $get) => $get('media_type') === 'video'
                                ? 'تظهر لحظة تحميل الفيديو (اختيارية)'
                                : 'يُفضّل أبعاد عريضة مثل 1920×700'),
                    ]),
                Forms\Components\Section::make('النصوص')
                    ->schema([
                        Forms\Components\Tabs::make('t')->tabs([
                            Forms\Components\Tabs\Tab::make('عربي')->schema([
                                Forms\Components\TextInput::make('title_ar')->label('العنوان'),
                                Forms\Components\TextInput::make('subtitle_ar')->label('العنوان الفرعي'),
                                Forms\Components\TextInput::make('button_text_ar')->label('نص الزر'),
                            ]),
                            Forms\Components\Tabs\Tab::make('English')->schema([
                                Forms\Components\TextInput::make('title_en')->label('Title'),
                                Forms\Components\TextInput::make('subtitle_en')->label('Subtitle'),
                                Forms\Components\TextInput::make('button_text_en')->label('Button text'),
                            ]),
                        ]),
                    ]),
                Forms\Components\Section::make('الإعدادات')
                    ->schema([
                        Forms\Components\TextInput::make('link')->label('الرابط عند الضغط')->url()->helperText('اختياري'),
                        Forms\Components\TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
                        Forms\Components\Toggle::make('is_active')->label('مفعّل')->default(true),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->emptyStateHeading('لا توجد بانرات')
            ->emptyStateDescription('أضف صور السلايدر للصفحة الرئيسية')
            ->emptyStateIcon('heroicon-o-rectangle-group')
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('المعاينة')
                    ->disk('public')
                    ->height(45)
                    ->width(120)
                    ->defaultImageUrl(asset('favicon.png'))
                    ->extraImgAttributes(['style' => 'object-fit: cover; border-radius: 6px;']),
                Tables\Columns\TextColumn::make('media_type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'video' ? 'فيديو' : 'صورة')
                    ->color(fn (string $state) => $state === 'video' ? 'warning' : 'info')
                    ->icon(fn (string $state) => $state === 'video' ? 'heroicon-m-film' : 'heroicon-m-photo'),
                Tables\Columns\TextColumn::make('title_ar')->label('العنوان')->limit(40)->placeholder('—'),
                Tables\Columns\IconColumn::make('is_active')->label('مفعّل')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('الترتيب')->sortable(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()->requiresConfirmation(),
                ])->label('إجراءات')->icon('heroicon-m-ellipsis-horizontal')->button()->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
