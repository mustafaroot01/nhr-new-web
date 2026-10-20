<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    use \App\Filament\Concerns\HasPermissionGate;

    protected static ?string $model = Post::class;

    protected static function permissionResource(): string
    {
        return 'pages';
    }

    protected static ?string $navigationIcon = 'heroicon-o-document';

    protected static ?string $navigationLabel = 'الصفحات';

    protected static ?string $modelLabel = 'صفحة';

    protected static ?string $pluralModelLabel = 'الصفحات';

    protected static ?string $navigationGroup = 'المحتوى';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'pages';

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
                                            ->afterStateUpdated(function (string $state, Forms\Set $set, string $operation) {
                                                if ($operation === 'create') {
                                                    $set('slug', Str::slug($state));
                                                }
                                            }),
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
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                                Forms\Components\Select::make('status')
                                    ->label('الحالة')
                                    ->options([
                                        'draft' => 'مسودة',
                                        'published' => 'منشور',
                                        'archived' => 'مؤرشف',
                                    ])
                                    ->default('published')
                                    ->required(),
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
            ->persistSortInSession()
            ->persistSearchInSession()
            ->emptyStateHeading('لا توجد صفحات')
            ->emptyStateDescription('ابدأ بإضافة أول صفحة')
            ->emptyStateIcon('heroicon-o-document')
            ->columns([
                Tables\Columns\TextColumn::make('translatedTitle')
                    ->label('العنوان')
                    ->getStateUsing(fn (Post $record) => $record->translation('ar')?->title ?? '-')
                    ->description(fn (Post $record) => $record->translation('en')?->title)
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('translations', fn ($q) => $q->where('title', 'like', "%{$search}%"));
                    })
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('slug')
                    ->label('الرابط')
                    ->color('gray')
                    ->copyable()
                    ->toggleable(),
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
                Tables\Columns\IconColumn::make('hasEnglish')
                    ->label('إنكليزي')
                    ->getStateUsing(fn (Post $record) => $record->translation('en') !== null)
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخر تعديل')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', 'page');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
