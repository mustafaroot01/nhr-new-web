<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ app()->getLocale() === 'ar' ? 'الصفحة غير موجودة' : 'Page Not Found' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Tajawal', 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: #1f2937;
            padding: 24px;
        }
        .error-page { text-align: center; max-width: 520px; }
        .error-code {
            font-size: clamp(100px, 20vw, 160px);
            font-weight: 800;
            background: linear-gradient(135deg, #1a5276, #c9941e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 8px;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .error-title {
            font-size: clamp(22px, 4vw, 32px);
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 14px;
        }
        .error-desc {
            font-size: 16px;
            color: #6b7280;
            line-height: 1.7;
            margin-bottom: 32px;
        }
        .error-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 28px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            font-family: inherit;
            text-decoration: none;
            transition: transform .2s, box-shadow .2s, background .2s;
            cursor: pointer; border: none;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.12); }
        .btn:active { transform: translateY(0); }
        .btn-primary { background: #1a5276; color: #fff; }
        .btn-primary:hover { background: #2c3e50; }
        .btn-outline { background: #fff; color: #1a5276; border: 1.5px solid #e5e7eb; }
        .btn-outline:hover { border-color: #1a5276; }
        .btn svg { width: 18px; height: 18px; }
        .decor {
            margin-top: 48px;
            display: flex; justify-content: center; gap: 6px;
        }
        .decor span {
            width: 8px; height: 8px; border-radius: 50%;
            animation: dots 1.5s ease-in-out infinite;
        }
        .decor span:nth-child(1) { background: #1a5276; animation-delay: 0s; }
        .decor span:nth-child(2) { background: #c9941e; animation-delay: .2s; }
        .decor span:nth-child(3) { background: #2c3e50; animation-delay: .4s; }
        @keyframes dots {
            0%, 100% { transform: scale(1); opacity: .4; }
            50% { transform: scale(1.5); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-code">404</div>
        @if(app()->getLocale() === 'ar')
            <h1 class="error-title">الصفحة غير موجودة</h1>
            <p class="error-desc">عذراً، الصفحة التي تبحث عنها غير موجودة أو تم نقلها. تأكد من صحة الرابط أو عُد للصفحة الرئيسية.</p>
            <div class="error-actions">
                <a href="/ar" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    الصفحة الرئيسية
                </a>
                <button onclick="history.back()" class="btn btn-outline">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                    رجوع
                </button>
            </div>
        @else
            <h1 class="error-title">Page Not Found</h1>
            <p class="error-desc">Sorry, the page you're looking for doesn't exist or has been moved. Please check the URL or go back to the homepage.</p>
            <div class="error-actions">
                <a href="/en" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Homepage
                </a>
                <button onclick="history.back()" class="btn btn-outline">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                    Go Back
                </button>
            </div>
        @endif
        <div class="decor">
            <span></span><span></span><span></span>
        </div>
    </div>
</body>
</html>
