<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPermissionGate;
use App\Filament\Resources\ComplaintResource\Pages;
use App\Models\Complaint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ComplaintResource extends Resource
{
    use HasPermissionGate;

    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $navigationLabel = 'الشكاوى والبلاغات';

    protected static ?string $modelLabel = 'بلاغ';

    protected static ?string $pluralModelLabel = 'الشكاوى والبلاغات';

    protected static ?string $navigationGroup = 'النظام';

    protected static ?int $navigationSort = 2;

    protected static function permissionResource(): string
    {
        return 'complaints';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('تفاصيل البلاغ')
                    ->description('بيانات سرية — مشفّرة في قاعدة البيانات')
                    ->icon('heroicon-o-lock-closed')
                    ->schema([
                        Forms\Components\TextInput::make('reference')->label('رقم المرجع')->disabled(),
                        Forms\Components\TextInput::make('subject')->label('الموضوع')->disabled(),
                        Forms\Components\Textarea::make('details')->label('التفاصيل')->disabled()->rows(5)->columnSpanFull(),
                        Forms\Components\TextInput::make('name')->label('اسم المُبلّغ')->disabled()->placeholder('مجهول'),
                        Forms\Components\TextInput::make('contact')->label('وسيلة التواصل')->disabled()->placeholder('غير متوفرة'),
                        Forms\Components\TextInput::make('incident_location')->label('مكان الحادثة')->disabled(),
                        Forms\Components\TextInput::make('incident_date')->label('تاريخ الحادثة')->disabled(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('المعالجة')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'new' => 'جديد',
                                'reviewing' => 'قيد المراجعة',
                                'resolved' => 'تمت المعالجة',
                                'closed' => 'مغلق',
                            ])
                            ->required(),
                        Forms\Components\Select::make('severity')
                            ->label('درجة الخطورة')
                            ->options([
                                'low' => 'منخفضة',
                                'medium' => 'متوسطة',
                                'high' => 'عالية',
                                'critical' => 'حرجة',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('admin_notes')->label('ملاحظات داخلية')->rows(3)->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->persistFiltersInSession()
            ->emptyStateHeading('لا توجد بلاغات')
            ->emptyStateDescription('البلاغات الواردة من الموقع ستظهر هنا')
            ->emptyStateIcon('heroicon-o-shield-exclamation')
            ->columns([
                Tables\Columns\TextColumn::make('reference')->label('المرجع')->searchable()->copyable()->weight('medium'),
                Tables\Columns\TextColumn::make('subject')->label('الموضوع')->limit(40)->wrap(),
                Tables\Columns\TextColumn::make('severity')
                    ->label('الخطورة')->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'low' => 'منخفضة', 'medium' => 'متوسطة', 'high' => 'عالية', 'critical' => 'حرجة',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'low' => 'gray', 'medium' => 'info', 'high' => 'warning', 'critical' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'new' => 'جديد', 'reviewing' => 'قيد المراجعة', 'resolved' => 'تمت المعالجة', 'closed' => 'مغلق',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'new' => 'danger', 'reviewing' => 'warning', 'resolved' => 'success', 'closed' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('التاريخ')->dateTime('Y-m-d H:i')->since()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(['new' => 'جديد', 'reviewing' => 'قيد المراجعة', 'resolved' => 'تمت المعالجة', 'closed' => 'مغلق']),
                Tables\Filters\SelectFilter::make('severity')
                    ->label('الخطورة')
                    ->options(['low' => 'منخفضة', 'medium' => 'متوسطة', 'high' => 'عالية', 'critical' => 'حرجة']),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('عرض ومعالجة'),
                    Tables\Actions\EditAction::make()->label('معالجة'),
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('حذف البلاغ')
                        ->modalDescription('تحذير: سيُحذف البلاغ نهائياً.'),
                ])->label('إجراءات')->icon('heroicon-m-ellipsis-horizontal')->button()->color('gray'),
            ]);
    }

    public static function canCreate(): bool
    {
        return false; // submissions arrive from the public site only
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'new')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComplaints::route('/'),
            'view' => Pages\ViewComplaint::route('/{record}'),
            'edit' => Pages\EditComplaint::route('/{record}/edit'),
        ];
    }
}
