<x-layouts.osif title="OSIF KARBALA 2026 | ملتقى كربلاء لجراحة الفم وزراعة الأسنان" :description="$event->description">
    <main id="main">
        <section id="top" class="osif-hero" data-event-date="{{ $event->starts_at->toIso8601String() }}">
            <div class="osif-orbit osif-orbit-one" aria-hidden="true"></div>
            <div class="osif-orbit osif-orbit-two" aria-hidden="true"></div>
            <div class="osif-container osif-hero-grid">
                <div class="osif-hero-copy">
                    <p class="osif-eyebrow"><span></span> النسخة الأولى · 2026</p>
                    <p class="osif-hero-arabic">ملتقى كربلاء لجراحة الفم وزراعة الأسنان</p>
                    <h1><span>ORAL SURGERY</span><br>&amp; IMPLANTOLOGY<br><em>FORUM</em></h1>
                    <p class="osif-hero-summary">منصة علمية تجمع أطباء الأسنان والخبراء والشركات الرائدة حول أحدث تقنيات جراحة الفم وزراعة الأسنان.</p>

                    <div class="osif-event-meta">
                        <div><i class="bi bi-calendar3"></i><span><strong>الجمعة</strong> 13 نوفمبر 2026</span></div>
                        <div><i class="bi bi-geo-alt"></i><span>{{ $event->location }}</span></div>
                    </div>

                    <div class="osif-hero-actions">
                        @if ($event->registration_open && ! $event->isFullyBooked())
                            <a class="osif-button osif-button-primary" href="{{ route('osif.register') }}">احجز مقعدك <i class="bi bi-arrow-down-left"></i></a>
                        @else
                            <span class="osif-button osif-button-disabled">تم حجز كافة المقاعد</span>
                        @endif
                        <a class="osif-button osif-button-ghost" href="https://www.instagram.com/osif.karbala/" target="_blank" rel="noopener">تابع المستجدات <i class="bi bi-instagram"></i></a>
                    </div>
                </div>

                <div class="osif-hero-art" aria-label="OSIF KARBALA 2026">
                    <img src="{{ asset('images/osif/logo-transparent.png') }}" alt="شعار ملتقى OSIF كربلاء" class="osif-hero-logo">
                    <div class="osif-countdown" role="timer" aria-live="polite">
                        <div><strong data-countdown="days">--</strong><small>يوم</small></div>
                        <div><strong data-countdown="hours">--</strong><small>ساعة</small></div>
                        <div><strong data-countdown="minutes">--</strong><small>دقيقة</small></div>
                    </div>
                </div>
            </div>
        </section>

        <section id="about" class="osif-intro-section">
            <div class="osif-container osif-intro-grid">
                <div>
                    <p class="osif-section-kicker">حدث علمي متخصص</p>
                    <h2>كل ما يهم جراحة الفم<br>وزراعة الأسنان، في مكان واحد.</h2>
                </div>
                <div class="osif-intro-copy">
                    <p>تُقام فعاليات OSIF بالتعاون مع جامعة السبطين للعلوم الطبية، لتجمع المعرفة السريرية والتقنيات الحديثة وفرص التواصل المهني في تجربة واحدة مصممة للأطباء والمهتمين بالمجال.</p>
                    <p>{{ $event->description }}</p>
                </div>
            </div>
            <div class="osif-container osif-stat-grid">
                <article><strong>01</strong><span>النسخة الأولى<br>من الملتقى</span></article>
                <article><strong>+<span>علم</span></strong><span>محاضرات وورش<br>تطبيقية</span></article>
                <article><strong>FREE</strong><span>الدخول والتسجيل<br>مجانيان</span></article>
                <article><strong>13.11</strong><span>جامعة السبطين<br>للعلوم الطبية</span></article>
            </div>
        </section>

        <section id="experience" class="osif-experience-section">
            <div class="osif-container">
                <div class="osif-section-heading">
                    <p class="osif-section-kicker">تجربة OSIF</p>
                    <h2>يومٌ كامل من المعرفة والتواصل.</h2>
                    <p>يُعلن البرنامج التفصيلي وأسماء المحاضرين قريباً عبر القنوات الرسمية للملتقى.</p>
                </div>
                <div class="osif-track-grid">
                    <article class="osif-track osif-track-featured">
                        <span class="osif-track-number">01</span>
                        <i class="bi bi-mic"></i>
                        <h3>المحاضرات العلمية</h3>
                        <p>رؤى سريرية ومناقشات حول أحدث التطورات في الجراحة وزراعة الأسنان.</p>
                        <span>جلسات الخبراء <i class="bi bi-arrow-down-left"></i></span>
                    </article>
                    <article class="osif-track">
                        <span class="osif-track-number">02</span>
                        <i class="bi bi-tools"></i>
                        <h3>ورش العمل</h3>
                        <p>مساحات عملية للمهتمين بتطوير المهارات والتقنيات العلاجية الحديثة.</p>
                        <span>تعلم تطبيقي <i class="bi bi-arrow-down-left"></i></span>
                    </article>
                    <article class="osif-track">
                        <span class="osif-track-number">03</span>
                        <i class="bi bi-buildings"></i>
                        <h3>المعرض المتخصص</h3>
                        <p>لقاء مباشر مع الشركات والمكاتب والمختبرات الرائدة في مجال طب الأسنان.</p>
                        <span>ابتكارات وفرص <i class="bi bi-arrow-down-left"></i></span>
                    </article>
                </div>
            </div>
        </section>

        @if ($companies->isNotEmpty())
            <section id="companies" class="osif-companies-section">
                <div class="osif-container">
                    <div class="osif-companies-head">
                        <div>
                            <p class="osif-section-kicker">المعرض المتخصص</p>
                            <h2>الشركات المشاركة</h2>
                            <p>تصفّح منتجات الشركات وعروضها الخاصة بالملتقى، وأرسل طلبك إلى الشركة عبر واتساب مباشرة.</p>
                        </div>
                        <a class="osif-button osif-button-primary" href="{{ route('osif.companies') }}">تصفّح المنتجات والعروض <i class="bi bi-arrow-down-left"></i></a>
                    </div>
                    <ul class="osif-company-logos">
                        @foreach ($companies->take(12) as $company)
                            <li>
                                <a href="{{ route('osif.companies.show', $company) }}" title="{{ $company->name }}">
                                    @if ($company->logoUrl())
                                        <img src="{{ $company->logoUrl() }}" alt="{{ $company->name }}" loading="lazy">
                                    @else
                                        <span>{{ $company->name }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif

        <section class="osif-registration-section osif-registration-cta">
            <div class="osif-container">
                <p class="osif-section-kicker">التسجيل الإلكتروني</p>
                <h2>المقاعد محدودة. سجّل حضورك الآن.</h2>
                <p>افتح صفحة التسجيل المنفصلة وأكمل بياناتك ليتم التواصل معك بخصوص التسجيل.</p>
                @if ($event->registration_open)
                    <a class="osif-button osif-button-primary" href="{{ route('osif.register') }}">انتقل إلى التسجيل <i class="bi bi-arrow-down-left"></i></a>
                @endif
            </div>
        </section>
    </main>

    <footer class="osif-footer">
        <div class="osif-container">
            <div class="osif-footer-wordmark"><strong>OSIF</strong> KARBALA <span>2026</span></div>
            <p>ملتقى كربلاء لجراحة الفم وزراعة الأسنان</p>
            <div>
                <a href="https://www.instagram.com/osif.karbala/" target="_blank" rel="noopener" aria-label="OSIF Instagram"><i class="bi bi-instagram"></i></a>
                <a href="{{ route('home') }}">نقابة أطباء الأسنان — كربلاء <i class="bi bi-arrow-up-left"></i></a>
            </div>
        </div>
    </footer>
</x-layouts.osif>
