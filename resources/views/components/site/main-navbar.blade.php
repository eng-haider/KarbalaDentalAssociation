<header>
    <div class="gold-rule"></div>

    <div class="topbar d-none d-lg-block">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between py-2">
                <div class="d-flex align-items-center gap-3">
                    <a href="tel:{{ preg_replace('/\s+/', '', setting('phone', '+964 780 123 4567')) }}" class="d-inline-flex align-items-center gap-2">
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

            <div class="d-flex align-items-center gap-2 d-xl-none">
                <a class="nav-link nav-link--featured nav-featured-inline {{ request()->routeIs('discounts') ? 'active' : '' }}" href="{{ route('discounts') }}">
                    <i class="bi bi-star-fill" aria-hidden="true"></i> الخصومات
                </a>
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="فتح القائمة">
                    <i class="bi bi-list fs-2 text-primary"></i>
                </button>
            </div>

            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav mx-auto mt-3 mt-xl-0">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">الرئيسية</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('news.*') ? 'active' : '' }}" href="{{ route('news.index') }}">النشاطات</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('osif*') ? 'active' : '' }}" href="{{ route('osif') }}">OSIF 2026</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('transaction-search') ? 'active' : '' }}" href="{{ route('transaction-search') }}">البحث عن معاملة</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('courses.*') ? 'active' : '' }}" href="{{ route('courses.index') }}">الدورات التدريبية</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('regulations') ? 'active' : '' }}" href="{{ route('regulations') }}">الضوابط والشروط</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('marketplace.*') ? 'active' : '' }}" href="{{ route('marketplace.index') }}">بيع وشراء</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('jobs.*') ? 'active' : '' }}" href="{{ route('jobs.index') }}">فرص العمل</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('app') ? 'active' : '' }}" href="{{ route('app') }}">تطبيق النقابة</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('complaint') ? 'active' : '' }}" href="{{ route('complaint') }}">إرسال شكوى</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">اتصل بنا</a></li>
                    <li class="nav-item d-none d-xl-block"><a class="nav-link nav-link--featured {{ request()->routeIs('discounts') ? 'active' : '' }}" href="{{ route('discounts') }}"><i class="bi bi-star-fill" aria-hidden="true"></i> الخصومات</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>
