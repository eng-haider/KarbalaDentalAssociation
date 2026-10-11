<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#180529">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'OSIF KARBALA 2026' }}</title>
    <meta name="description" content="{{ $description ?? 'ملتقى كربلاء لجراحة الفم وزراعة الأسنان — OSIF KARBALA 2026' }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ar_IQ">
    <meta property="og:title" content="{{ $title ?? 'OSIF KARBALA 2026' }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @vite(['resources/css/site.css', 'resources/js/site.js', 'resources/css/osif.css', 'resources/js/osif.js'])
</head>
<body>
    <a href="#main" class="osif-skip">تخطَّ إلى المحتوى</a>
    <x-site.main-navbar />
    {{ $slot }}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
