<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPermissionGate;
use App\Filament\Resources\ReportResource\Pages;
use App\Models\Report;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReportResource extends Resource
{
    use HasPermissionGate;

    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'التقارير والإصدارات';

    protected static ?string $modelLabel = 'تقرير';

    protected static ?string $pluralModelLabel = 'التقارير والإصدارات';

    protected static ?string $navigationGroup = 'المحتوى';

    protected static ?int $navigationSort = 6;

    protected static function permissionResource(): string
    {
        return 'reports';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('معلومات التقرير')
                        ->schema([
                            Forms\Components\Tabs::make('t')->tabs([
                                Forms\Components\Tabs\Tab::make('عربي')->schema([
                                    Forms\Components\TextInput::make('title_ar')->label('العنوان')->required(),
                                    Forms\Components\Textarea::make('description_ar')->label('الوصف')->rows(4),
                                ]),
                                Forms\Components\Tabs\Tab::make('English')->schema([
                                    Forms\Components\TextInput::make('title_en')->label('Title'),
                                    Forms\Components\Textarea::make('description_en')->label('Description')->rows(4),
                                ]),
                            ])->columnSpanFull(),
                        ]),
                    Forms\Components\Section::make('ملفات PDF')
                        ->schema([
                            Forms\Components\FileUpload::make('pdf_ar_path')
                                ->label('ملف PDF (عربي)')
                                ->disk('public')->directory('media/reports')
                                ->acceptedFileTypes(['application/pdf'])->maxSize(20480)->openable()->downloadable(),
                            Forms\Components\FileUpload::make('pdf_en_path')
                                ->label('ملف PDF (إنكليزي)')
                                ->disk('public')->directory('media/reports')
                                ->acceptedFileTypes(['application/pdf'])->maxSize(20480)->openable()->downloadable(),
                        ])->columns(2),
                ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('الإعدادات')
                        ->schema([
                            Forms\Components\TextInput::make('year')
                                ->label('السنة')->numeric()->required()
                                ->default((int) date('Y'))->minValue(1990)->maxValue((int) date('Y') + 1),
                            Forms\Components\Select::make('category')
                                ->label('التصنيف')
                                ->options([
                                    'annual' => 'تقرير سنوي',
                                    'quarterly' => 'تقرير فصلي',
                                    'thematic' => 'تقرير موضوعي',
                                    'statement' => 'بيان',
                                    'guide' => 'دليل',
                                    'other' => 'أخرى',
                                ])
                                ->default('other'),
                            Forms\Components\TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
                            Forms\Components\Toggle::make('is_published')->label('منشور')->default(true),
                        ]),
                    Forms\Components\Section::make('صورة الغلاف')
                        ->schema([
                            Forms\Components\FileUpload::make('cover_image_path')
                                ->label('الغلاف')->image()->disk('public')->directory('media/reports/covers')
                                ->imageEditor()->openable()->imagePreviewHeight('150')->maxSize(5120),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultSort('year', 'desc')
            ->emptyStateHeading('لا توجد تقارير')
            ->emptyStateDescription('أضف التقارير والإصدارات الرسمية')
            ->emptyStateIcon('heroicon-o-document-chart-bar')
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image_path')
                    ->label('الغلاف')->disk('public')->height(55)->width(42)
                    ->extraImgAttributes(['style' => 'object-fit: cover; border-radius: 4px;'])
                    ->defaultImageUrl(asset('favicon.png')),
                Tables\Columns\TextColumn::make('title_ar')->label('العنوان')->searchable()->limit(50)->weight('medium'),
                Tables\Columns\TextColumn::make('year')->label('السنة')->badge()->color('info')->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('التصنيف')->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'annual' => 'سنوي', 'quarterly' => 'فصلي', 'thematic' => 'موضوعي',
                        'statement' => 'بيان', 'guide' => 'دليل', default => 'أخرى',
                    }),
                Tables\Columns\TextColumn::make('download_count')->label('التنزيلات')->badge()->color('gray')->toggleable(),
                Tables\Columns\IconColumn::make('is_published')->label('منشور')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('year')
                    ->label('السنة')
                    ->options(fn () => Report::query()->distinct()->orderByDesc('year')->pluck('year', 'year')->toArray()),
                Tables\Filters\SelectFilter::make('category')
                    ->label('التصنيف')
                    ->options([
                        'annual' => 'سنوي', 'quarterly' => 'فصلي', 'thematic' => 'موضوعي',
                        'statement' => 'بيان', 'guide' => 'دليل', 'other' => 'أخرى',
                    ]),
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
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }
}
