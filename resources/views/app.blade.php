<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @php
            $gaId = \App\Models\Setting::get('seo', 'google_analytics_id');
            $theme = \App\Models\Setting::getGroup('theme');
            $fontAr = $theme['font_ar'] ?? 'Tajawal';
            $fontEn = $theme['font_en'] ?? 'Inter';
            $fontFamilies = collect([$fontAr, $fontEn, 'Tajawal', 'Inter'])->unique()->map(fn($f) => 'family=' . urlencode($f) . ':wght@300;400;500;600;700;800')->implode('&');
        @endphp
        @if($gaId)
            <meta name="ga-id" content="{{ $gaId }}">
        @endif
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?{{ $fontFamilies }}&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
