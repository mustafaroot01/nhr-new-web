<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactSubmissionResource\Pages;
use App\Models\ContactSubmission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContactSubmissionResource extends Resource
{
    use \App\Filament\Concerns\HasPermissionGate;

    protected static ?string $model = ContactSubmission::class;

    protected static function permissionResource(): string
    {
        return 'contacts';
    }

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'رسائل التواصل';

    protected static ?string $modelLabel = 'رسالة';

    protected static ?string $pluralModelLabel = 'رسائل التواصل';

    protected static ?string $navigationGroup = 'النظام';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->label('الاسم')->disabled(),
                Forms\Components\TextInput::make('email')->label('الإيميل')->disabled(),
                Forms\Components\TextInput::make('phone')->label('الهاتف')->disabled(),
                Forms\Components\TextInput::make('subject')->label('الموضوع')->disabled(),
                Forms\Components\Textarea::make('message')->label('الرسالة')->disabled()->rows(6)->columnSpanFull(),
                Forms\Components\TextInput::make('ip_address')->label('IP')->disabled(),
                Forms\Components\TextInput::make('locale')->label('اللغة')->disabled(),
                Forms\Components\Toggle::make('is_read')->label('مقروءة'),
                Forms\Components\Toggle::make('is_archived')->label('مؤرشفة'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([10, 25, 50])
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->emptyStateHeading('لا توجد رسائل')
            ->emptyStateDescription('رسائل التواصل من الزوار ستظهر هنا')
            ->emptyStateIcon('heroicon-o-envelope')
            ->recordClasses(fn ($record) => $record->is_read ? null : 'font-semibold')
            ->columns([
                Tables\Columns\IconColumn::make('is_read')
                    ->label('')
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope-open')
                    ->falseIcon('heroicon-s-envelope')
                    ->trueColor('gray')
                    ->falseColor('warning'),
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->weight('medium')
                    ->icon('heroicon-m-user'),
                Tables\Columns\TextColumn::make('email')
                    ->label('الإيميل')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('تم نسخ الإيميل')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('subject')
                    ->label('الموضوع')
                    ->limit(45)
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->since()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_archived')
                    ->label('مؤرشف')
                    ->boolean()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_read')->label('مقروءة'),
                Tables\Filters\TernaryFilter::make('is_archived')->label('مؤرشفة'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('markRead')
                        ->label('تعيين مقروءة')
                        ->icon('heroicon-o-check')
                        ->action(fn (ContactSubmission $record) => $record->markAsRead())
                        ->hidden(fn (ContactSubmission $record) => $record->is_read),
                    Tables\Actions\Action::make('archive')
                        ->label('أرشفة')
                        ->icon('heroicon-o-archive-box')
                        ->action(fn (ContactSubmission $record) => $record->archive())
                        ->hidden(fn (ContactSubmission $record) => $record->is_archived),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactSubmissions::route('/'),
            'view' => Pages\ViewContactSubmission::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('is_read', false)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
