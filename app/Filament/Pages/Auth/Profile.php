<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class Profile extends BaseEditProfile
{
    public static function getLabel(): string
    {
        return 'الملف الشخصي';
    }

    public function getTitle(): string
    {
        return 'الملف الشخصي';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('الاسم')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->label('كلمة المرور الجديدة')
                    ->password()
                    ->revealable()
                    ->rule(\Illuminate\Validation\Rules\Password::default())
                    ->autocomplete('new-password')
                    ->dehydrated(fn ($state): bool => filled($state))
                    ->dehydrateStateUsing(fn ($state): string => \Illuminate\Support\Facades\Hash::make($state))
                    ->live(debounce: 500)
                    ->same('passwordConfirmation')
                    ->helperText('اتركها فارغة للإبقاء على كلمة المرور الحالية'),
                TextInput::make('passwordConfirmation')
                    ->label('تأكيد كلمة المرور')
                    ->password()
                    ->revealable()
                    ->required(fn ($get): bool => filled($get('password')))
                    ->dehydrated(false),
            ]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم تحديث الملف الشخصي بنجاح';
    }
}
