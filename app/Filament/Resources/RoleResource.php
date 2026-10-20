<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    use \App\Filament\Concerns\HasPermissionGate;

    protected static ?string $model = Role::class;

    protected static function permissionResource(): string
    {
        return 'roles';
    }

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'الأدوار والصلاحيات';

    protected static ?string $modelLabel = 'دور';

    protected static ?string $pluralModelLabel = 'الأدوار والصلاحيات';

    protected static ?string $navigationGroup = 'النظام';

    protected static ?int $navigationSort = 20;

    private static array $resources = [
        'posts' => 'المقالات',
        'pages' => 'الصفحات',
        'categories' => 'التصنيفات',
        'media' => 'الوسائط',
        'menus' => 'القوائم',
        'contacts' => 'رسائل التواصل',
        'settings' => 'الإعدادات',
        'users' => 'المستخدمين',
        'roles' => 'الأدوار والصلاحيات',
        'activity' => 'سجل النشاط',
    ];

    private static array $abilities = [
        'view' => 'عرض',
        'create' => 'إضافة',
        'update' => 'تعديل',
        'delete' => 'حذف',
    ];

    public static function form(Form $form): Form
    {
        $permissionSections = [];

        foreach (self::$resources as $resource => $label) {
            $options = [];
            foreach (self::$abilities as $ability => $abilityLabel) {
                $options["{$ability}_{$resource}"] = $abilityLabel;
            }

            $permissionSections[] = Forms\Components\Section::make($label)
                ->schema([
                    Forms\Components\CheckboxList::make("perm_{$resource}")
                        ->label('')
                        ->options($options)
                        ->columns(4)
                        ->gridDirection('row'),
                ])
                ->compact()
                ->columnSpan(1);
        }

        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الدور')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم الدور')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('مثال: محرر، كاتب، مشرف'),
                        Forms\Components\Toggle::make('can_publish')
                            ->label('صلاحية النشر (نشر/إلغاء نشر المقالات)')
                            ->helperText('يسمح لهذا الدور بنشر المقالات وتغيير حالتها'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Forms\Components\Section::make('الصلاحيات حسب القسم')
                    ->description('حدّد ما يستطيع هذا الدور فعله في كل قسم')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema($permissionSections),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->emptyStateHeading('لا توجد أدوار')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الدور')
                    ->badge()
                    ->color('primary')
                    ->weight('medium')
                    ->searchable(),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('عدد الصلاحيات')
                    ->counts('permissions')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('عدد المستخدمين')
                    ->counts('users')
                    ->badge()
                    ->color('info'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('تأكيد حذف الدور')
                        ->modalDescription('سيفقد المستخدمون المرتبطون بهذا الدور صلاحياتهم. متأكد؟')
                        ->hidden(fn (Role $record) => in_array($record->name, ['admin'])),
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
