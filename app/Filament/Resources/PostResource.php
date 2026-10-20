<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPermissionGate;
use App\Filament\Resources\PostResource\Pages;
use App\Models\Category;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    use HasPermissionGate;

    protected static ?string $model = Post::class;

    protected static function permissionResource(): string
    {
        return 'posts';
    }

    protected static function userCanPublish(): bool
    {
        $user = auth()->user();

        return $user && ($user->hasRole('admin') || $user->can('publish_posts'));
    }

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'المقالات';

    protected static ?string $modelLabel = 'مقال';

    protected static ?string $pluralModelLabel = 'المقالات';

    protected static ?string $navigationGroup = 'المحتوى';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Tabs::make('translations')
                            ->tabs([
                                Forms\Components\Tabs\Tab::make('عربي')
                                    ->schema([
                                        Forms\Components\TextInput::make('translations.ar.title')
                                            ->label('العنوان')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (?string $state, Forms\Set $set, Forms\Get $get, string $operation) {
                                                if ($operation === 'create' && blank($get('slug')) && filled($state)) {
                                                    $slug = Str::slug($state);
                                                    if (filled($slug)) {
                                                        $set('slug', $slug);
                                                    }
                                                }
                                            }),
                                        Forms\Components\TextInput::make('translations.ar.subtitle')
                                            ->label('العنوان الفرعي')
                                            ->maxLength(255),
                                        Forms\Components\Textarea::make('translations.ar.excerpt')
                                            ->label('المقتطف')
                                            ->rows(3)
                                            ->maxLength(500),
                                        Forms\Components\RichEditor::make('translations.ar.body')
                                            ->label('المحتوى')
                                            ->required()
                                            ->columnSpanFull()
                                            ->fileAttachmentsDisk('public')
                                            ->fileAttachmentsDirectory('media/content'),
                                        Forms\Components\TextInput::make('translations.ar.meta_title')
                                            ->label('عنوان SEO')
                                            ->maxLength(70),
                                        Forms\Components\Textarea::make('translations.ar.meta_description')
                                            ->label('وصف SEO')
                                            ->rows(2)
                                            ->maxLength(160),
                                    ]),
                                Forms\Components\Tabs\Tab::make('English')
                                    ->schema([
                                        Forms\Components\TextInput::make('translations.en.title')
                                            ->label('Title')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('translations.en.subtitle')
                                            ->label('Subtitle')
                                            ->maxLength(255),
                                        Forms\Components\Textarea::make('translations.en.excerpt')
                                            ->label('Excerpt')
                                            ->rows(3)
                                            ->maxLength(500),
                                        Forms\Components\RichEditor::make('translations.en.body')
                                            ->label('Content')
                                            ->columnSpanFull()
                                            ->fileAttachmentsDisk('public')
                                            ->fileAttachmentsDirectory('media/content'),
                                        Forms\Components\TextInput::make('translations.en.meta_title')
                                            ->label('SEO Title')
                                            ->maxLength(70),
                                        Forms\Components\Textarea::make('translations.en.meta_description')
                                            ->label('SEO Description')
                                            ->rows(2)
                                            ->maxLength(160),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('الإعدادات')
                            ->schema([
                                Forms\Components\TextInput::make('slug')
                                    ->label('الرابط')
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->helperText('اتركه فارغاً وسيُستخدم رقم المقال تلقائياً'),
                                Forms\Components\Select::make('status')
                                    ->label('الحالة')
                                    ->options([
                                        'draft' => 'مسودة',
                                        'published' => 'منشور',
                                        'archived' => 'مؤرشف',
                                    ])
                                    ->default('draft')
                                    ->required(),
                                Forms\Components\Select::make('category_id')
                                    ->label('التصنيف')
                                    ->relationship('category')
                                    ->getOptionLabelFromRecordUsing(fn (Category $record) => $record->translation('ar')?->name ?? $record->slug)
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\DateTimePicker::make('published_at')
                                    ->label('تاريخ النشر'),
                                Forms\Components\Toggle::make('is_featured')
                                    ->label('مقال مميز'),
                            ]),
                        Forms\Components\Section::make('الصورة الرئيسية')
                            ->schema([
                                Forms\Components\FileUpload::make('featured_image_path')
                                    ->label('الصورة')
                                    ->image()
                                    ->disk('public')
                                    ->directory('media/featured')
                                    ->imageEditor()
                                    ->openable()
                                    ->downloadable()
                                    ->imagePreviewHeight('150')
                                    ->imageResizeMode('cover')
                                    ->imageCropAspectRatio('16:9')
                                    ->imageResizeTargetWidth('1200')
                                    ->imageResizeTargetHeight('630'),
                            ]),
                        Forms\Components\Section::make('معرض الصور')
                            ->description('اسحب الصور لإعادة الترتيب — اضغط على صورة للمعاينة أو الحذف')
                            ->schema([
                                Forms\Components\FileUpload::make('gallery')
                                    ->label('')
                                    ->image()
                                    ->multiple()
                                    ->disk('public')
                                    ->directory('media/gallery')
                                    ->panelLayout('grid')
                                    ->imagePreviewHeight('100')
                                    ->reorderable()
                                    ->appendFiles()
                                    ->openable()
                                    ->downloadable()
                                    ->uploadingMessage('جارِ رفع الصور...')
                                    ->helperText('يمكنك رفع عدة صور دفعة واحدة'),
                            ]),
                        Forms\Components\Section::make('المرفقات')
                            ->description('ملفات PDF / Word — اضغط على الملف للفتح أو الحذف')
                            ->schema([
                                Forms\Components\FileUpload::make('attachment_files')
                                    ->label('')
                                    ->multiple()
                                    ->disk('public')
                                    ->directory('media/attachments')
                                    ->panelLayout('grid')
                                    ->openable()
                                    ->downloadable()
                                    ->reorderable()
                                    ->appendFiles()
                                    ->acceptedFileTypes([
                                        'application/pdf',
                                        'application/msword',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                    ])
                                    ->helperText('الصيغ المدعومة: PDF، Word'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([10, 25, 50, 100])
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->emptyStateHeading('لا توجد مقالات')
            ->emptyStateDescription('ابدأ بإضافة أول مقال')
            ->emptyStateIcon('heroicon-o-document-text')
            ->columns([
                Tables\Columns\ImageColumn::make('featuredImage.path')
                    ->label('الصورة')
                    ->disk('public')
                    ->height(45)
                    ->width(60)
                    ->extraImgAttributes(['style' => 'object-fit: cover; border-radius: 6px;'])
                    ->defaultImageUrl(asset('favicon.png')),
                Tables\Columns\TextColumn::make('translatedTitle')
                    ->label('العنوان')
                    ->getStateUsing(fn (Post $record) => $record->translation('ar')?->title ?? '-')
                    ->description(fn (Post $record) => $record->translation('en')?->title)
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('translations', fn ($q) => $q->where('title', 'like', "%{$search}%"));
                    })
                    ->wrap()
                    ->limit(60),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'published' => 'منشور',
                        'draft' => 'مسودة',
                        'archived' => 'مؤرشف',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'published' => 'success',
                        'draft' => 'warning',
                        'archived' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('category.slug')
                    ->label('التصنيف')
                    ->badge()
                    ->color('info')
                    ->getStateUsing(fn (Post $record) => $record->category?->translation('ar')?->name ?? '-'),
                Tables\Columns\TextColumn::make('view_count')
                    ->label('المشاهدات')
                    ->sortable()
                    ->numeric()
                    ->icon('heroicon-m-eye')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('مميز')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('تاريخ النشر')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'published' => 'منشور',
                        'draft' => 'مسودة',
                        'archived' => 'مؤرشف',
                    ]),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('التصنيف')
                    ->relationship('category', 'slug')
                    ->getOptionLabelFromRecordUsing(fn (Category $record) => $record->translation('ar')?->name ?? $record->slug)
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('مميز'),
                Tables\Filters\TrashedFilter::make()
                    ->label('المحذوفات'),
            ])
            ->actions([
                Tables\Actions\Action::make('toggleStatus')
                    ->label(fn (Post $record) => $record->status === 'published' ? 'إلغاء النشر' : 'نشر')
                    ->icon(fn (Post $record) => $record->status === 'published' ? 'heroicon-m-eye-slash' : 'heroicon-m-check-circle')
                    ->color(fn (Post $record) => $record->status === 'published' ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Post $record) => $record->status === 'published' ? 'إلغاء نشر المقال' : 'نشر المقال')
                    ->visible(fn (Post $record) => ! $record->trashed() && static::userCanPublish())
                    ->action(function (Post $record) {
                        if ($record->status === 'published') {
                            $record->update(['status' => 'draft']);
                        } else {
                            $record->update(['status' => 'published', 'published_at' => $record->published_at ?? now()]);
                        }
                        \Filament\Notifications\Notification::make()->title('تم تحديث الحالة')->success()->send();
                    }),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('duplicate')
                        ->label('نسخ')
                        ->icon('heroicon-m-document-duplicate')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('نسخ المقال')
                        ->modalDescription('سيتم إنشاء نسخة مسودة من هذا المقال بكل ترجماته.')
                        ->action(function (Post $record) {
                            $copy = $record->replicate(['uuid', 'slug', 'view_count']);
                            $copy->uuid = (string) Str::uuid();
                            $copy->status = 'draft';
                            $copy->view_count = 0;
                            $copy->save();
                            $copy->update(['slug' => (string) $copy->id]);
                            foreach ($record->translations as $t) {
                                $copy->translations()->create([
                                    'locale' => $t->locale,
                                    'title' => $t->title . ' (نسخة)',
                                    'subtitle' => $t->subtitle,
                                    'excerpt' => $t->excerpt,
                                    'body' => $t->body,
                                ]);
                            }
                            \Filament\Notifications\Notification::make()->title('تم نسخ المقال')->success()->send();
                        }),
                    Tables\Actions\RestoreAction::make()->label('استرجاع'),
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('تأكيد حذف المقال')
                        ->modalDescription('سيُنقل المقال لسلة المحذوفات ويمكن استرجاعه لاحقاً.'),
                    Tables\Actions\ForceDeleteAction::make()
                        ->label('حذف نهائي')
                        ->requiresConfirmation()
                        ->modalHeading('حذف نهائي')
                        ->modalDescription('تحذير: سيُحذف المقال نهائياً ولا يمكن استرجاعه!'),
                ])
                    ->label('إجراءات')
                    ->icon('heroicon-m-ellipsis-horizontal')
                    ->button()
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publish')
                        ->label('نشر المحدد')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn () => static::userCanPublish())
                        ->action(fn ($records) => $records->each(fn (Post $p) => $p->update(['status' => 'published', 'published_at' => $p->published_at ?? now()])))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('archive')
                        ->label('أرشفة المحدد')
                        ->icon('heroicon-m-archive-box')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each(fn (Post $p) => $p->update(['status' => 'archived'])))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('feature')
                        ->label('تمييز المحدد')
                        ->icon('heroicon-m-star')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each(fn (Post $p) => $p->update(['is_featured' => true])))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد')
                        ->requiresConfirmation(),
                    Tables\Actions\RestoreBulkAction::make()->label('استرجاع المحدد'),
                    Tables\Actions\ForceDeleteBulkAction::make()->label('حذف نهائي'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('type', 'post')
            ->withoutGlobalScopes([\Illuminate\Database\Eloquent\SoftDeletingScope::class]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['slug'];
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return $record->translation('ar')?->title ?? $record->slug;
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return ['التصنيف' => $record->category?->translation('ar')?->name ?? '—'];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('type', 'post')->where('status', 'published')->count();
    }
}
