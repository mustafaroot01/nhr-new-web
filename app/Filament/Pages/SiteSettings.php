<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'إعدادات الموقع';

    protected static ?string $title = 'إعدادات الموقع';

    protected static ?string $navigationGroup = 'النظام';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && ($user->hasRole('admin') || $user->can('update_settings'));
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);

        $settings = Setting::all()->groupBy('group');
        $data = [];

        foreach ($settings as $group => $items) {
            foreach ($items as $item) {
                $data["{$group}__{$item->key}"] = $item->value;
            }
        }

        $this->form->fill($data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('settings')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('عام')
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                Forms\Components\Section::make('اسم الموقع')
                                    ->description('يظهر في الهيدر ونتائج البحث')
                                    ->icon('heroicon-o-identification')
                                    ->schema([
                                        Forms\Components\TextInput::make('general__site_name_ar')->label('اسم الموقع (عربي)')->required(),
                                        Forms\Components\TextInput::make('general__site_name_en')->label('Site Name (English)'),
                                        Forms\Components\Textarea::make('general__site_description_ar')->label('وصف الموقع (عربي)')->rows(2),
                                        Forms\Components\Textarea::make('general__site_description_en')->label('Site Description (English)')->rows(2),
                                    ])
                                    ->columns(2),
                                Forms\Components\Section::make('الشعار والأيقونة')
                                    ->description('الشعار يظهر في الهيدر والفوتر ولوحة التحكم، والأيقونة في تبويب المتصفح')
                                    ->icon('heroicon-o-photo')
                                    ->schema([
                                        Forms\Components\FileUpload::make('general__logo')
                                            ->label('شعار الموقع')
                                            ->image()
                                            ->disk('public')
                                            ->directory('media/branding')
                                            ->imageEditor()
                                            ->imagePreviewHeight('120')
                                            ->openable()
                                            ->downloadable()
                                            ->maxSize(2048)
                                            ->helperText('يُفضّل صيغة PNG بخلفية شفافة'),
                                        Forms\Components\FileUpload::make('general__favicon')
                                            ->label('أيقونة المتصفح (Favicon)')
                                            ->image()
                                            ->disk('public')
                                            ->directory('media/branding')
                                            ->imagePreviewHeight('80')
                                            ->openable()
                                            ->maxSize(512)
                                            ->helperText('مربعة 32×32 أو 64×64 بكسل'),
                                    ])
                                    ->columns(2),
                            ]),
                        Forms\Components\Tabs\Tab::make('المظهر')
                            ->icon('heroicon-o-paint-brush')
                            ->schema([
                                Forms\Components\Section::make('ألوان الموقع')
                                    ->description('تنعكس على الأزرار والروابط والعناوين في الموقع')
                                    ->icon('heroicon-o-swatch')
                                    ->schema([
                                        Forms\Components\ColorPicker::make('theme__primary_color')->label('اللون الرئيسي'),
                                        Forms\Components\ColorPicker::make('theme__secondary_color')->label('اللون الثانوي'),
                                        Forms\Components\ColorPicker::make('theme__accent_color')->label('لون التمييز'),
                                    ])
                                    ->columns(3),
                                Forms\Components\Section::make('نوع الخط')
                                    ->description('اختر الخط المعروض في الموقع — يتغير فوراً عند الحفظ')
                                    ->icon('heroicon-o-language')
                                    ->schema([
                                        Forms\Components\Select::make('theme__font_ar')
                                            ->label('الخط العربي')
                                            ->native(false)
                                            ->options([
                                                'Tajawal' => 'Tajawal — تجوال',
                                                'Cairo' => 'Cairo — القاهرة',
                                                'Almarai' => 'Almarai — المراعي',
                                                'Noto Kufi Arabic' => 'Noto Kufi — نوتو كوفي',
                                                'IBM Plex Sans Arabic' => 'IBM Plex Sans Arabic',
                                                'Amiri' => 'Amiri — أميري',
                                                'Changa' => 'Changa — تشنغا',
                                            ])
                                            ->default('Tajawal')
                                            ->live()
                                            ->helperText(fn ($state) => $state ? 'الخط المختار: ' . $state : null),
                                        Forms\Components\Select::make('theme__font_en')
                                            ->label('الخط الإنكليزي')
                                            ->native(false)
                                            ->options([
                                                'Inter' => 'Inter',
                                                'Roboto' => 'Roboto',
                                                'Open Sans' => 'Open Sans',
                                                'Poppins' => 'Poppins',
                                                'Lato' => 'Lato',
                                                'Montserrat' => 'Montserrat',
                                            ])
                                            ->default('Inter'),
                                    ])
                                    ->columns(2),
                            ]),
                        Forms\Components\Tabs\Tab::make('سوشيال ميديا')
                            ->icon('heroicon-o-share')
                            ->schema([
                                Forms\Components\TextInput::make('social__facebook')->label('Facebook')->url(),
                                Forms\Components\TextInput::make('social__twitter')->label('Twitter / X')->url(),
                                Forms\Components\TextInput::make('social__instagram')->label('Instagram')->url(),
                                Forms\Components\TextInput::make('social__youtube')->label('YouTube')->url(),
                                Forms\Components\TextInput::make('social__telegram')->label('Telegram')->url(),
                            ]),
                        Forms\Components\Tabs\Tab::make('SEO')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                Forms\Components\TextInput::make('seo__meta_title_ar')->label('عنوان SEO (عربي)'),
                                Forms\Components\TextInput::make('seo__meta_title_en')->label('SEO Title (English)'),
                                Forms\Components\Textarea::make('seo__meta_description_ar')->label('وصف SEO (عربي)')->rows(2),
                                Forms\Components\Textarea::make('seo__meta_description_en')->label('SEO Description (English)')->rows(2),
                                Forms\Components\TextInput::make('seo__google_analytics_id')->label('Google Analytics ID'),
                            ]),
                        Forms\Components\Tabs\Tab::make('اتصل بنا')
                            ->icon('heroicon-o-phone')
                            ->schema([
                                Forms\Components\TextInput::make('static_texts__contact_address_ar')->label('العنوان (عربي)'),
                                Forms\Components\TextInput::make('static_texts__contact_address_en')->label('Address (English)'),
                                Forms\Components\TextInput::make('static_texts__contact_phone')->label('رقم الهاتف'),
                                Forms\Components\TextInput::make('static_texts__contact_email')->label('الإيميل')->email(),
                                Forms\Components\TextInput::make('static_texts__contact_hours_ar')->label('ساعات العمل (عربي)'),
                                Forms\Components\TextInput::make('static_texts__contact_hours_en')->label('Working Hours (English)'),
                                Forms\Components\Textarea::make('static_texts__google_maps_embed')->label('Google Maps Embed Code')->rows(3),
                            ]),
                        Forms\Components\Tabs\Tab::make('الفوتر')
                            ->icon('heroicon-o-bars-3-bottom-left')
                            ->schema([
                                Forms\Components\TextInput::make('static_texts__footer_copyright_ar')->label('حقوق النشر (عربي)'),
                                Forms\Components\TextInput::make('static_texts__footer_copyright_en')->label('Copyright (English)'),
                            ]),
                        Forms\Components\Tabs\Tab::make('وضع الصيانة')
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->schema([
                                Forms\Components\Toggle::make('maintenance__enabled')
                                    ->label('تفعيل وضع الصيانة')
                                    ->helperText('عند التفعيل، يُغلق الموقع العام للزوار وتظهر رسالة الصيانة. لوحة التحكم تبقى تعمل.')
                                    ->live(),
                                Forms\Components\TextInput::make('maintenance__title_ar')
                                    ->label('عنوان الصيانة (عربي)')
                                    ->default('الموقع قيد الصيانة')
                                    ->visible(fn (Forms\Get $get) => $get('maintenance__enabled')),
                                Forms\Components\TextInput::make('maintenance__title_en')
                                    ->label('Maintenance title (English)')
                                    ->default('Site under maintenance')
                                    ->visible(fn (Forms\Get $get) => $get('maintenance__enabled')),
                                Forms\Components\Textarea::make('maintenance__message_ar')
                                    ->label('رسالة الصيانة (عربي)')
                                    ->rows(3)
                                    ->default('نعتذر، الموقع تحت الصيانة حالياً. يرجى العودة لاحقاً.')
                                    ->visible(fn (Forms\Get $get) => $get('maintenance__enabled')),
                                Forms\Components\Textarea::make('maintenance__message_en')
                                    ->label('Maintenance message (English)')
                                    ->rows(3)
                                    ->default('Sorry, the site is currently under maintenance. Please check back later.')
                                    ->visible(fn (Forms\Get $get) => $get('maintenance__enabled')),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);

        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            $parts = explode('__', $key, 2);
            if (count($parts) === 2) {
                Setting::set($parts[0], $parts[1], $value);
            }
        }

        Notification::make()
            ->title('تم حفظ الإعدادات بنجاح')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('save')
                ->label('حفظ الإعدادات')
                ->submit('save'),
        ];
    }
}
