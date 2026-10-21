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

    protected static ?string $navigationLabel = 'التصنيفات';

    protected static ?string $modelLabel = 'تصنيف';

    protected static ?string $pluralModelLabel = 'التصنيفات';

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
                Forms\Components\Select::make('parent_id')
                    ->label('التصنيف الأب')
                    ->options(fn () => Category::whereNull('parent_id')
                        ->where('is_visible', true)
                        ->with('translations')
                        ->orderBy('sort_order')
                        ->get()
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->placeholder('— قسم رئيسي —')
                    ->helperText('اتركه فارغاً لإنشاء قسم رئيسي، أو اختر قسماً لإضافة فرعي تحته')
                    ->nullable(),
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
            ->persistSortInSession()
            ->persistSearchInSession()
            ->emptyStateHeading('لا توجد تصنيفات')
            ->emptyStateDescription('ابدأ بإضافة أول تصنيف')
            ->emptyStateIcon('heroicon-o-folder')
            ->columns([
                Tables\Columns\TextColumn::make('translatedName')
                    ->label('الاسم')
                    ->getStateUsing(function (Category $record) {
                        $name = $record->translation('ar')?->name ?? '-';
                        return $record->parent_id ? "↳ {$name}" : $name;
                    })
                    ->description(fn (Category $record) => $record->translation('en')?->name)
                    ->searchable(query: fn ($query, string $search) => $query->whereHas('translations', fn ($q) => $q->where('name', 'like', "%{$search}%")))
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('parentName')
                    ->label('النوع')
                    ->badge()
                    ->getStateUsing(fn (Category $record) => $record->parent_id
                        ? $record->parent?->translation('ar')?->name
                        : 'رئيسي')
                    ->color(fn (Category $record) => $record->parent_id ? 'info' : 'success'),
                Tables\Columns\TextColumn::make('slug')
                    ->label('الرابط')
                    ->color('gray')
                    ->toggleable(),
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
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        'parent' => 'أقسام رئيسية',
                        'child' => 'أقسام فرعية',
                    ])
                    ->query(fn ($query, array $data) => match ($data['value'] ?? null) {
                        'parent' => $query->whereNull('parent_id'),
                        'child' => $query->whereNotNull('parent_id'),
                        default => $query,
                    }),
                Tables\Filters\TernaryFilter::make('is_visible')->label('الظهور'),
                Tables\Filters\TrashedFilter::make()->label('المحذوفات'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\RestoreAction::make()->label('استرجاع'),
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('تأكيد حذف التصنيف')
                        ->modalDescription(fn (Category $record) => $record->posts()->count() > 0
                            ? 'تحذير: هذا التصنيف فيه ' . $record->posts()->count() . ' مقال. سيُنقل لسلة المحذوفات.'
                            : 'سيُنقل التصنيف لسلة المحذوفات ويمكن استرجاعه.'),
                    Tables\Actions\ForceDeleteAction::make()
                        ->label('حذف نهائي')
                        ->requiresConfirmation()
                        ->modalHeading('حذف نهائي')
                        ->modalDescription('تحذير: لا يمكن استرجاع التصنيف بعد الحذف النهائي!'),
                ])
                    ->label('إجراءات')
                    ->icon('heroicon-m-ellipsis-horizontal')
                    ->button()
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->requiresConfirmation(),
                    Tables\Actions\RestoreBulkAction::make()->label('استرجاع المحدد'),
                    Tables\Actions\ForceDeleteBulkAction::make()->label('حذف نهائي'),
                ]),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
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
