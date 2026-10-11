<x-layouts.osif title="التسجيل | OSIF KARBALA 2026" :description="'التسجيل الإلكتروني في ملتقى كربلاء لجراحة الفم وزراعة الأسنان'">
    <main id="main" class="osif-registration-page">
        <div class="osif-container osif-registration-grid">
            <header class="osif-registration-banner" aria-labelledby="osif-about-title">
                <div class="osif-banner-copy">
                    <p class="osif-section-kicker">عن OSIF</p>
                    <h2 id="osif-about-title">ملتقى كربلاء لجراحة الفم وزراعة الأسنان</h2>
                    <p>منصة علمية تجمع الأطباء والخبراء حول أحدث التقنيات والممارسات السريرية.</p>
                </div>
                <ul class="osif-banner-logos" aria-label="الجهات المشاركة في الملتقى">
                    <li>
                        <span class="osif-banner-seal is-brand"><img src="{{ asset('images/osif/logos/osif.png') }}" alt="" width="80" height="80"></span>
                        <span>OSIF KARBALA</span>
                    </li>
                    <li>
                        <span class="osif-banner-seal"><img src="{{ asset('images/osif/logos/syndicate.png') }}" alt="" width="88" height="88"></span>
                        <span>نقابة أطباء أسنان كربلاء</span>
                    </li>
                    <li>
                        <span class="osif-banner-seal"><img src="{{ asset('images/osif/partners/sums-c.svg') }}" alt="" width="88" height="88"></span>
                        <span>جامعة السبطين للعلوم الطبية</span>
                    </li>
                </ul>
            </header>

            <section class="osif-registration-copy" aria-labelledby="osif-registration-title">
                <div class="osif-copy-head">
                    <div class="osif-registration-status">
                        <p class="osif-section-kicker">التسجيل الإلكتروني</p>
                        @if ($event->registration_open)
                            <span class="osif-status-pill is-open"><span aria-hidden="true"></span>التسجيل مفتوح</span>
                        @else
                            <span class="osif-status-pill"><span aria-hidden="true"></span>التسجيل مغلق</span>
                        @endif
                    </div>
                    <h2 id="osif-registration-title">كن جزءاً من <em>OSIF KARBALA</em></h2>
                </div>

                <ul class="osif-event-facts" aria-label="تفاصيل الملتقى">
                    <li>
                        <i class="bi bi-calendar-event" aria-hidden="true"></i>
                        <div><small>الموعد</small><strong>{{ $event->starts_at->translatedFormat('l j F Y') }}</strong><span>يبدأ {{ $event->starts_at->translatedFormat('g:i A') }}</span></div>
                    </li>
                    <li>
                        <i class="bi bi-ticket-perforated" aria-hidden="true"></i>
                        <div><small>رسوم التسجيل</small><strong>مجاني بالكامل</strong></div>
                    </li>
                    <li class="osif-fact-wide">
                        <i class="bi bi-geo-alt" aria-hidden="true"></i>
                        <div><small>المكان</small><strong>{{ $event->location }}</strong></div>
                    </li>
                    <li class="osif-fact-wide">
                        <i class="bi bi-easel" aria-hidden="true"></i>
                        <div><small>البرنامج</small><strong>محاضرات وورش علمية متخصصة</strong></div>
                    </li>
                </ul>
            </section>

            @include('osif.partials.registration-form', ['event' => $event])

            @if ($event->registration_open)
                <section class="osif-registration-steps" aria-labelledby="osif-steps-title">
                    <h3 id="osif-steps-title">ماذا يحدث بعد التسجيل؟</h3>
                    <ol>
                        <li><strong>أرسل طلبك</strong><span>أكمل نموذج التسجيل بالبيانات الصحيحة وأرسله.</span></li>
                        <li><strong>مراجعة الطلب</strong><span>يراجع فريق OSIF بيانات التسجيل.</span></li>
                        <li><strong>استلام الدعوة</strong><span>نرسل بطاقة الدعوة الخاصة بك إلى رقمك على واتساب.</span></li>
                    </ol>
                </section>
            @endif
        </div>
    </main>
</x-layouts.osif>
