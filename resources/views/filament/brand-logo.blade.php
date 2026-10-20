@php
    $logo = \App\Models\Setting::get('general', 'logo');
    $logoUrl = $logo ? asset('storage/' . $logo) : asset('storage/media/branding/logo.png');
    $siteName = \App\Models\Setting::get('general', 'site_name_ar', 'جمعية النضال لحقوق الإنسان');
@endphp
<img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="nahr-brand-logo" style="height: 100%; width: auto; object-fit: contain;">
