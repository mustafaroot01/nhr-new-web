<?php

namespace App\Providers;

use App\Models\LoginLog;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerLoginLogging();

        // Enlarge the brand logo on the login / simple pages only.
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => Blade::render(<<<'HTML'
                <style>
                    /* Enlarge the logo CONTAINER on the login page so it doesn't overflow onto the heading */
                    .fi-simple-header .fi-logo {
                        height: 7rem !important;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin-bottom: 1.5rem;
                    }
                    .fi-simple-header .fi-logo .nahr-brand-logo {
                        height: 100% !important;
                        width: auto;
                        object-fit: contain;
                    }
                    .fi-simple-header {
                        text-align: center;
                    }
                </style>
            HTML),
        );

        // Confirm before removing a single file/image from any upload field (FilePond).
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn (): string => Blade::render(<<<'HTML'
                <script>
                    document.addEventListener('click', function (e) {
                        var btn = e.target.closest('button.filepond--action-remove-item');
                        if (! btn) { return; }
                        if (btn.dataset.nahrConfirmed === '1') {
                            btn.dataset.nahrConfirmed = '';
                            return;
                        }
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        if (window.confirm('هل أنت متأكد من حذف هذا الملف؟ لن يُحفظ الحذف نهائياً إلا بعد حفظ النموذج.')) {
                            btn.dataset.nahrConfirmed = '1';
                            btn.click();
                        }
                    }, true);
                </script>
            HTML),
        );
    }

    private function registerLoginLogging(): void
    {
        Event::listen(Login::class, function (Login $event) {
            LoginLog::create([
                'user_id' => $event->user->getAuthIdentifier(),
                'email' => $event->user->email ?? null,
                'successful' => true,
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
                'created_at' => now(),
            ]);
        });

        Event::listen(Failed::class, function (Failed $event) {
            LoginLog::create([
                'user_id' => $event->user?->getAuthIdentifier(),
                'email' => $event->credentials['email'] ?? null,
                'successful' => false,
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
                'created_at' => now(),
            ]);
        });
    }
}
