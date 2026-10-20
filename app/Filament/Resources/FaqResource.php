<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasPermissionGate;
use App\Filament\Resources\FaqResource\Pages;
use App\Models\Faq;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FaqResource extends Resource
{
    use HasPermissionGate;

    protected static ?string $model = Faq::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationLabel = 'الأسئلة الشائعة';

    protected static ?string $modelLabel = 'سؤال';

    protected static ?string $pluralModelLabel = 'الأسئلة الشائعة';

    protected static ?string $navigationGroup = 'المحتوى';

    protected static ?int $navigationSort = 5;

    protected static function permissionResource(): string
    {
        return 'faqs';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('t')->tabs([
                    Forms\Components\Tabs\Tab::make('عربي')->schema([
                        Forms\Components\TextInput::make('question_ar')->label('السؤال')->required(),
                        Forms\Components\Textarea::make('answer_ar')->label('الجواب')->required()->rows(4),
                    ]),
                    Forms\Components\Tabs\Tab::make('English')->schema([
                        Forms\Components\TextInput::make('question_en')->label('Question'),
                        Forms\Components\Textarea::make('answer_en')->label('Answer')->rows(4),
                    ]),
                ])->columnSpanFull(),
                Forms\Components\TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
                Forms\Components\Toggle::make('is_visible')->label('ظاهر')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->emptyStateHeading('لا توجد أسئلة')
            ->emptyStateDescription('أضف الأسئلة الشائعة')
            ->emptyStateIcon('heroicon-o-question-mark-circle')
            ->columns([
                Tables\Columns\TextColumn::make('question_ar')->label('السؤال')->searchable()->limit(60)->weight('medium'),
                Tables\Columns\IconColumn::make('is_visible')->label('ظاهر')->boolean(),
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
            'index' => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit' => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}
