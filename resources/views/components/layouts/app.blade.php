<!DOCTYPE html>
<html lang="ar" dir="rtl" class="scroll-smooth">
<head>
    {{-- Mark JS as available before paint so reveal-on-scroll only hides
         content when JS can reveal it again (no-JS = fully visible). --}}
    <script>document.documentElement.classList.add('js');</script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0d1a36">

    {{-- ---------------- SEO ---------------- --}}
    @php($org = \App\Support\SiteData::org())
    <title>{{ $title ?? $org['name'] }}</title>
    <meta name="description" content="{{ $description ?? $org['tagline'] }}">
    <meta name="keywords" content="نقابة أطباء الأسنان, كربلاء, طب الأسنان, تجديد العضوية, التعليم المستمر, خدمات رقمية">
    <meta name="author" content="{{ $org['name'] }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ar_IQ">
    <meta property="og:site_name" content="{{ $org['name'] }}">
    <meta property="og:title" content="{{ $title ?? $org['name'] }}">
    <meta property="og:description" content="{{ $description ?? $org['tagline'] }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/og-cover.svg') }}">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? $org['name'] }}">
    <meta name="twitter:description" content="{{ $description ?? $org['tagline'] }}">
    <meta name="twitter:image" content="{{ asset('images/og-cover.svg') }}">

    {{-- PWA --}}
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-192.svg') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    {{-- Fonts: Arabic (Cairo, IBM Plex Sans Arabic) + Latin (Inter) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Structured data (JSON-LD) --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "GovernmentOrganization",
        "name": "{{ $org['name'] }}",
        "alternateName": "{{ $org['name_en'] }}",
        "url": "{{ url('/') }}",
        "email": "{{ $org['email'] }}",
        "telephone": "{{ $org['phone'] }}",
        "address": {
            "@@type": "PostalAddress",
            "addressLocality": "كربلاء المقدسة",
            "addressCountry": "IQ",
            "streetAddress": "{{ $org['address'] }}"
        }
    }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-mist text-brand-900 selection:bg-brand-100">

    {{-- ===== Skip link (a11y) ===== --}}
    <a href="#main"
       class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:right-4 focus:z-[60]
              focus:rounded-sm focus:bg-brand-800 focus:px-5 focus:py-3 focus:text-white focus:shadow-lg">
        تخطَّ إلى المحتوى الرئيسي
    </a>

    @include('partials.header')

    <main id="main">
        {{ $slot }}
    </main>

    @include('partials.footer')

    {{-- ===== Scroll-to-top ===== --}}
    <button x-data="{ show: false }"
            x-init="window.addEventListener('scroll', () => show = window.scrollY > 600)"
            x-show="show" x-transition
            @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="fixed bottom-6 left-6 z-40 grid size-12 place-items-center rounded-md
                   bg-brand-800 text-white shadow-[var(--shadow-lift)] ring-1 ring-white/10 transition-colors hover:bg-brand-900"
            style="display:none;" aria-label="العودة إلى الأعلى">
        <x-icon name="chevron-down" class="size-5 rotate-180" />
    </button>
</body>
</html>
