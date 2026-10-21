<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubCategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SubCategoryResource extends Resource
{
    use \App\Filament\Concerns\HasPermissionGate;

    protected static ?string $model = Category::class;

    protected static function permissionResource(): string
    {
        return 'categories';
    }

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $navigationLabel = 'الأقسام الفرعية';

    protected static ?string $modelLabel = 'قسم فرعي';

    protected static ?string $pluralModelLabel = 'الأقسام الفرعية';

    protected static ?string $navigationGroup = 'المحتوى';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'sub-categories';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('parent_id')
                    ->label('القسم الرئيسي')
                    ->options(fn () => Category::whereNull('parent_id')
                        ->where('is_visible', true)
                        ->with('translations')
                        ->orderBy('sort_order')
                        ->get()
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),

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
            ->emptyStateHeading('لا توجد أقسام فرعية')
            ->emptyStateDescription('ابدأ بإضافة أول قسم فرعي')
            ->emptyStateIcon('heroicon-o-folder-open')
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
                Tables\Columns\TextColumn::make('parentName')
                    ->label('القسم الرئيسي')
                    ->badge()
                    ->color('success')
                    ->getStateUsing(fn (Category $record) => $record->parent?->translation('ar')?->name ?? '-'),
                Tables\Columns\TextColumn::make('posts_count')
                    ->label('المقالات')
                    ->counts('posts')
                    ->badge()
                    ->color('info'),
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
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('القسم الرئيسي')
                    ->options(fn () => Category::whereNull('parent_id')
                        ->with('translations')
                        ->orderBy('sort_order')
                        ->get()
                        ->pluck('name', 'id')),
                Tables\Filters\TernaryFilter::make('is_visible')->label('الظهور'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد حذف القسم الفرعي'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()->requiresConfirmation(),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereNotNull('parent_id')
            ->withoutGlobalScopes([\Illuminate\Database\Eloquent\SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubCategories::route('/'),
            'create' => Pages\CreateSubCategory::route('/create'),
            'edit' => Pages\EditSubCategory::route('/{record}/edit'),
        ];
    }
}
