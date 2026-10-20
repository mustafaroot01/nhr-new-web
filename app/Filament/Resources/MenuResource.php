<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages;
use App\Models\Menu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MenuResource extends Resource
{
    use \App\Filament\Concerns\HasPermissionGate;

    protected static ?string $model = Menu::class;

    protected static function permissionResource(): string
    {
        return 'menus';
    }

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';

    protected static ?string $navigationLabel = 'القوائم';

    protected static ?string $modelLabel = 'قائمة';

    protected static ?string $pluralModelLabel = 'القوائم';

    protected static ?string $navigationGroup = 'الموقع';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('location')
                    ->label('الموقع')
                    ->options([
                        'primary' => 'القائمة الرئيسية',
                        'footer' => 'قائمة الفوتر',
                    ])
                    ->required(),
                Forms\Components\Select::make('locale')
                    ->label('اللغة')
                    ->options([
                        'ar' => 'عربي',
                        'en' => 'English',
                    ])
                    ->required(),
                Forms\Components\Repeater::make('allItems')
                    ->label('عناصر القائمة')
                    ->relationship()
                    ->schema([
                        Forms\Components\TextInput::make('label')
                            ->label('النص')
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->label('النوع')
                            ->options([
                                'page' => 'صفحة',
                                'category' => 'تصنيف',
                                'url' => 'رابط مخصص',
                            ])
                            ->default('url')
                            ->reactive(),
                        Forms\Components\TextInput::make('url')
                            ->label('الرابط')
                            ->visible(fn (Forms\Get $get) => $get('type') === 'url'),
                        Forms\Components\Select::make('reference_id')
                            ->label('المرجع')
                            ->visible(fn (Forms\Get $get) => in_array($get('type'), ['page', 'category']))
                            ->options(function (Forms\Get $get) {
                                if ($get('type') === 'page') {
                                    return \App\Models\Post::where('type', 'page')->pluck('slug', 'id');
                                }

                                return \App\Models\Category::all()->mapWithKeys(fn ($c) => [$c->id => $c->translation('ar')?->name ?? $c->slug]);
                            }),
                        Forms\Components\Toggle::make('is_visible')
                            ->label('ظاهر')
                            ->default(true),
                        Forms\Components\Toggle::make('open_in_new_tab')
                            ->label('فتح بتاب جديد')
                            ->default(false),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('الترتيب')
                            ->numeric()
                            ->default(0),
                    ])
                    ->orderColumn('sort_order')
                    ->columnSpanFull()
                    ->collapsible()
                    ->itemLabel(fn (array $state) => $state['label'] ?? 'عنصر جديد'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->emptyStateHeading('لا توجد قوائم')
            ->emptyStateDescription('أنشئ قائمة تنقل للموقع')
            ->emptyStateIcon('heroicon-o-bars-3')
            ->columns([
                Tables\Columns\TextColumn::make('location')
                    ->label('الموقع')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'primary' => 'القائمة الرئيسية',
                        'footer' => 'قائمة الفوتر',
                        default => $state,
                    })
                    ->badge()
                    ->icon('heroicon-m-bars-3'),
                Tables\Columns\TextColumn::make('locale')
                    ->label('اللغة')
                    ->formatStateUsing(fn (string $state) => $state === 'ar' ? 'عربي' : 'English')
                    ->badge()
                    ->color(fn (string $state) => $state === 'ar' ? 'info' : 'success'),
                Tables\Columns\TextColumn::make('all_items_count')
                    ->label('العناصر')
                    ->counts('allItems')
                    ->badge()
                    ->color('gray'),
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
