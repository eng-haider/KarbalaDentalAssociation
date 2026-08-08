<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0C2D6B">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name') }}</title>
    <meta name="description" content="{{ $description ?? 'الموقع الرسمي لنقابة أطباء الأسنان في كربلاء المقدسة – الخدمات الإلكترونية، النشاطات، النشاطات، مجلس النقابة، الإعلانات والتعاميم والتواصل.' }}">
    <meta name="author" content="نقابة أطباء الأسنان - فرع كربلاء المقدسة">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:locale" content="ar_IQ">
    <meta property="og:title" content="{{ $title ?? config('app.name') }}">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">

    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    @vite(['resources/css/site.css', 'resources/js/site.js'])

    @stack('head')
</head>
<body>
<a href="#main" class="visually-hidden-focusable position-absolute top-0 start-0 m-2 btn btn-gov">تخطَّ إلى المحتوى</a>

<header>

    <div class="gold-rule"></div>


    <div class="topbar d-none d-lg-block">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between py-2">
                <div class="d-flex align-items-center gap-3">
                    <a href="tel:{{ preg_replace('/\\s+/', '', setting('phone', '+964 780 123 4567')) }}" class="d-inline-flex align-items-center gap-2">
                        <i class="bi bi-telephone-fill"></i><span dir="ltr">{{ setting('phone', '+964 780 123 4567') }}</span>
                    </a>
                    <span class="divider"></span>
                    <a href="mailto:{{ setting('email', 'info@karbala-dental.iq') }}" class="d-inline-flex align-items-center gap-2">
                        <i class="bi bi-envelope-fill"></i> {{ setting('email', 'info@karbala-dental.iq') }}
                    </a>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="d-inline-flex align-items-center gap-2"><i class="bi bi-clock-fill"></i> {{ setting('working_hours', 'الأحد – الخميس: ٩:٠٠ ص – ٣:٠٠ م') }}</span>
                    <span class="divider"></span>
                    <div class="d-flex align-items-center gap-2">
                        <x-site.social-links />
                    </div>
                </div>
            </div>
        </div>
    </div>


    <nav class="navbar navbar-expand-xl navbar-gov sticky-top py-2" id="mainNav">
        <div class="container">

            <a class="navbar-brand d-flex align-items-center p-0" href="{{ route('home') }}">
                <span class="brand-emblem-wrap">
                    <img src="{{ asset('images/logo.png') }}" alt="شعار نقابة أطباء الأسنان – فرع كربلاء المقدسة" class="brand-emblem">
                </span>
                <span class="brand-divider" aria-hidden="true"></span>
                <span class="d-flex flex-column brand-text">
                    <span class="brand-country">{{ setting('site_country', 'جمهورية العراق') }}</span>
                    <span class="brand-name">{{ setting('site_name', 'نقابة أطباء الأسنان') }}</span>
                    <span class="brand-branch">{{ setting('site_branch', 'فرع كربلاء المقدسة') }}</span>
                </span>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="فتح القائمة">
                <i class="bi bi-list fs-2 text-primary"></i>
            </button>

            <div class="collapse navbar-collapse" id="navMenu">
             <ul class="navbar-nav mx-auto mt-3 mt-xl-0">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">الرئيسية</a></li>
                    {{-- <li class="nav-item"><a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">عن النقابة</a></li> --}}
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('news.*') ? 'active' : '' }}" href="{{ route('news.index') }}">النشاطات</a></li>
                     <li class="nav-item"><a class="nav-link {{ request()->routeIs('transaction-search') ? 'active' : '' }}" href="{{ route('transaction-search') }}">البحث عن معاملة</a></li>
                    {{-- <li class="nav-item"><a class="nav-link {{ request()->routeIs('events.*') ? 'active' : '' }}" href="{{ route('events.index') }}">الفعاليات</a></li> --}}
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('courses.*') ? 'active' : '' }}" href="{{ route('courses.index') }}">الدورات التدريبية</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('regulations') ? 'active' : '' }}" href="{{ route('regulations') }}">الضوابط والشروط</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('discounts') ? 'active' : '' }}" href="{{ route('discounts') }}">الخصومات</a></li>

                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('complaint') ? 'active' : '' }}" href="{{ route('complaint') }}">إرسال شكوى</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">اتصل بنا</a></li>

                </ul>

                <div class="d-flex align-items-center gap-2 mt-3 mt-xl-0">

                </div>
            </div>
        </div>
    </nav>
</header>

<main id="main">
    {{ $slot }}
</main>

<footer class="footer">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-4">
                <a href="{{ route('home') }}" class="d-flex align-items-center gap-2 mb-3">
                    <img src="{{ asset('images/logo.png') }}" alt="شعار النقابة" width="52" height="52">
                    <span class="d-flex flex-column">
                        <span class="text-white fw-bold">{{ setting('site_name', 'نقابة أطباء الأسنان') }}</span>
                        <span style="color:var(--secondary);font-size:.82rem;font-weight:700">{{ setting('site_branch', 'فرع كربلاء المقدسة') }}</span>
                    </span>
                </a>
                <p class="mb-3">{{ setting('footer_about', 'المؤسسة المهنية الرسمية التي تنظّم مهنة طب الأسنان في كربلاء المقدسة وتُعنى بشؤون أعضائها وخدمة المجتمع.') }}</p>
                <div class="social-icons d-flex gap-2">
                    <x-site.social-links />
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <h5>روابط سريعة</h5>
                <div class="footer-links">
                    <a href="{{ route('about') }}"><i class="bi bi-chevron-left"></i>عن النقابة</a>
                    <a href="{{ route('board') }}"><i class="bi bi-chevron-left"></i>مجلس النقابة</a>
                    <a href="{{ route('news.index') }}"><i class="bi bi-chevron-left"></i>النشاطات</a>
                    <a href="{{ route('courses.index') }}"><i class="bi bi-chevron-left"></i>الدورات التدريبية</a>
                    <a href="{{ route('events.index') }}"><i class="bi bi-chevron-left"></i>الفعاليات</a>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <h5>الخدمات الإلكترونية</h5>
                <div class="footer-links">
                    <a href="{{ route('apply') }}"><i class="bi bi-chevron-left"></i>التجديد والانتماء</a>
                    <a href="{{ route('transaction-search') }}"><i class="bi bi-chevron-left"></i>البحث عن معاملة</a>
                    <a href="{{ route('regulations') }}"><i class="bi bi-chevron-left"></i>الضوابط والشروط</a>
                    <a href="{{ route('discounts') }}"><i class="bi bi-chevron-left"></i>خصومات الأعضاء</a>
                    <a href="{{ route('complaint') }}"><i class="bi bi-chevron-left"></i>إرسال شكوى</a>
                </div>
            </div>

            <div class="col-lg-3">
                <h5>النشرة البريدية</h5>
                <p>اشترك ليصلك جديد النقابة وإعلاناتها الرسمية.</p>
                <form class="newsletter" id="newsletterForm" novalidate>
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="بريدك الإلكتروني" aria-label="البريد الإلكتروني" required>
                        <button class="btn btn-gold" type="submit"><i class="bi bi-send"></i></button>
                    </div>
                    <div class="form-text text-white-50 mt-2 d-none" id="newsletterMsg">شكراً لاشتراكك في النشرة البريدية.</div>
                </form>
                <div class="mt-4">
                    <p class="mb-1 d-flex align-items-center gap-2"><i class="bi bi-telephone-fill" style="color:var(--secondary)"></i> <span dir="ltr">{{ setting('phone', '+964 780 123 4567') }}</span></p>
                    <p class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-envelope-fill" style="color:var(--secondary)"></i> {{ setting('email', 'info@karbala-dental.iq') }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span>© ٢٠٢٦ {{ setting('site_name', 'نقابة أطباء الأسنان') }} – {{ setting('site_branch', 'فرع كربلاء المقدسة') }}. جميع الحقوق محفوظة.</span>
            <span class="d-flex gap-3">
                <a href="#">سياسة الخصوصية</a>
                <a href="#">شروط الاستخدام</a>
            </span>
        </div>
    </div>
</footer>

<button class="back-to-top" id="backToTop" aria-label="العودة إلى الأعلى"><i class="bi bi-arrow-up"></i></button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
