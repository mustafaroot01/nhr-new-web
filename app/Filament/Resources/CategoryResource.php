<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    use \App\Filament\Concerns\HasPermissionGate;

    protected static ?string $model = Category::class;

    protected static function permissionResource(): string
    {
        return 'categories';
    }

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationLabel = 'الأقسام الرئيسية';

    protected static ?string $modelLabel = 'قسم رئيسي';

    protected static ?string $pluralModelLabel = 'الأقسام الرئيسية';

    protected static ?string $navigationGroup = 'المحتوى';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('translations')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('عربي')
                            ->schema([
                                Forms\Components\TextInput::make('translations.ar.name')
                                    ->label('الاسم بالعربي')
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
                                Forms\Components\Textarea::make('translations.ar.description')
                                    ->label('الوصف بالعربي')
                                    ->rows(3),
                            ]),
                        Forms\Components\Tabs\Tab::make('English')
                            ->schema([
                                Forms\Components\TextInput::make('translations.en.name')
                                    ->label('Name in English')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('translations.en.description')
                                    ->label('Description in English')
                                    ->rows(3),
                            ]),
                    ])
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('slug')
                    ->label('الرابط')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->helperText('اتركه فارغاً وسيُستخدم رقم التصنيف تلقائياً'),
                Forms\Components\TextInput::make('sort_order')
                    ->label('الترتيب')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_visible')
                    ->label('ظاهر')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->emptyStateHeading('لا توجد أقسام رئيسية')
            ->emptyStateDescription('ابدأ بإضافة أول قسم رئيسي')
            ->emptyStateIcon('heroicon-o-folder')
            ->columns([
                Tables\Columns\TextColumn::make('translatedName')
                    ->label('الاسم بالعربي')
                    ->getStateUsing(fn (Category $record) => $record->translation('ar')?->name ?? '-')
                    ->searchable(query: fn ($query, string $search) => $query->whereHas('translations', fn ($q) => $q->where('name', 'like', "%{$search}%")))
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('translatedNameEn')
                    ->label('Name')
                    ->getStateUsing(fn (Category $record) => $record->translation('en')?->name ?? '-')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('children_count')
                    ->label('الأقسام الفرعية')
                    ->counts('children')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('posts_count')
                    ->label('المقالات')
                    ->counts('posts')
                    ->badge()
                    ->color('success'),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label('ظاهر')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_visible')->label('الظهور'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد حذف القسم')
                    ->modalDescription(fn (Category $record) => $record->children()->count() > 0
                        ? 'تحذير: هذا القسم فيه ' . $record->children()->count() . ' قسم فرعي و ' . $record->posts()->count() . ' مقال.'
                        : 'سيُنقل القسم لسلة المحذوفات.'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()->requiresConfirmation(),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereNull('parent_id')
            ->withoutGlobalScopes([\Illuminate\Database\Eloquent\SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
