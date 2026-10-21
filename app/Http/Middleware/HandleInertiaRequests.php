<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\Setting;
use App\Support\Presenter;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'locale' => fn () => app()->getLocale(),
            'direction' => fn () => app()->getLocale() === 'ar' ? 'rtl' : 'ltr',
            'navigation' => fn () => $this->navigationTree(),
            'settings' => fn () => $this->publicSettings(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }

    /**
     * Only allow safe Google Maps <iframe> embeds; strip everything else.
     */
    private static function sanitizeMapEmbed(string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // Extract src from an <iframe> and only allow google.com/maps domains
        if (preg_match('/<iframe[^>]+src=["\']([^"\']+)["\']/', $html, $m)) {
            $src = $m[1];
            if (preg_match('#^https://(www\.)?google\.com/maps/embed#', $src)) {
                return '<iframe src="' . e($src) . '" width="100%" height="350" style="border:0;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
            }
        }

        return ''; // Reject anything that doesn't match
    }

    /**
     * Visible root categories with their visible children, for the main nav.
     */
    private function navigationTree(): array
    {
        return Category::whereNull('parent_id')
            ->where('is_visible', true)
            ->with(['translations', 'children' => fn ($q) => $q->where('is_visible', true), 'children.translations'])
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Category $cat) => [
                ...Presenter::categoryBrief($cat),
                'children' => $cat->children->map(fn ($c) => Presenter::categoryBrief($c))->values(),
            ])
            ->toArray();
    }

    /**
     * Public site settings exposed to the frontend (no sensitive values).
     */
    private function publicSettings(): array
    {
        $general = Setting::getGroup('general');
        $theme = Setting::getGroup('theme');
        $social = Setting::getGroup('social');
        $texts = Setting::getGroup('static_texts');
        $seo = Setting::getGroup('seo');

        return [
            'site_name_ar' => $general['site_name_ar'] ?? 'جمعية النضال لحقوق الإنسان',
            'site_name_en' => $general['site_name_en'] ?? 'Al-Nidal Association for Human Rights',
            'site_description_ar' => $general['site_description_ar'] ?? '',
            'site_description_en' => $general['site_description_en'] ?? '',
            'logo' => ! empty($general['logo']) ? asset('storage/' . $general['logo']) : asset('storage/media/branding/logo.png'),
            'colors' => [
                'primary' => $theme['primary_color'] ?? '#1a5276',
                'secondary' => $theme['secondary_color'] ?? '#2c3e50',
                'accent' => $theme['accent_color'] ?? '#c9941e',
            ],
            'fonts' => [
                'ar' => $theme['font_ar'] ?? 'Tajawal',
                'en' => $theme['font_en'] ?? 'Inter',
            ],
            'social' => array_filter([
                'facebook' => $social['facebook'] ?? '',
                'twitter' => $social['twitter'] ?? '',
                'instagram' => $social['instagram'] ?? '',
                'youtube' => $social['youtube'] ?? '',
                'telegram' => $social['telegram'] ?? '',
            ]),
            'contact' => [
                'address_ar' => $texts['contact_address_ar'] ?? '',
                'address_en' => $texts['contact_address_en'] ?? '',
                'phone' => $texts['contact_phone'] ?? '',
                'email' => $texts['contact_email'] ?? '',
                'hours_ar' => $texts['contact_hours_ar'] ?? '',
                'hours_en' => $texts['contact_hours_en'] ?? '',
                'maps' => self::sanitizeMapEmbed($texts['google_maps_embed'] ?? ''),
            ],
            'footer' => [
                'copyright_ar' => $texts['footer_copyright_ar'] ?? '',
                'copyright_en' => $texts['footer_copyright_en'] ?? '',
            ],
            'seo' => [
                'ga_id' => $seo['google_analytics_id'] ?? '',
            ],
        ];
    }
}
