<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1a5276; --accent: #c9941e; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Tajawal', sans-serif;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #1a2332 0%, #1a5276 100%);
            color: #fff; text-align: center; padding: 24px;
        }
        .box { max-width: 520px; }
        .icon { font-size: 64px; margin-bottom: 24px; }
        h1 { font-size: 28px; margin-bottom: 16px; }
        p { font-size: 18px; line-height: 1.7; opacity: 0.9; }
        .bar { width: 60px; height: 4px; background: var(--accent); margin: 24px auto; border-radius: 2px; }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon">🛠️</div>
        <h1>{{ $title }}</h1>
        <div class="bar"></div>
        <p>{{ $message }}</p>
    </div>
</body>
</html>
