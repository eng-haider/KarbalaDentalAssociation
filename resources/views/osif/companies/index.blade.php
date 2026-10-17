<x-layouts.osif title="الشركات المشاركة | OSIF KARBALA 2026" description="تصفّح منتجات الشركات المشاركة في ملتقى OSIF KARBALA 2026 وعروضها، واطلب ما تحتاجه عبر واتساب.">
    <x-slot:head>
        @vite(['resources/css/osif-shop.css', 'resources/js/osif-shop.js'])
    </x-slot:head>

    <main id="main" class="shop-page" x-data="osifCompanies">
        <header class="shop-hero is-index">
            <div class="osif-container">
                <nav class="shop-crumbs" aria-label="مسار التصفح">
                    <a href="{{ route('osif') }}">OSIF 2026</a>
                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                    <span>الشركات المشاركة</span>
                </nav>
                <p class="osif-section-kicker">المعرض المتخصص</p>
                <h1>الشركات المشاركة</h1>
                <p class="shop-hero-lead">تصفّح منتجات الشركات وعروضها الخاصة بالملتقى، وأرسل طلبك إلى الشركة مباشرة عبر واتساب.</p>

                <ol class="shop-how">
                    <li><b>1</b> اختر الشركة</li>
                    <li><b>2</b> أضف المنتجات إلى السلة</li>
                    <li><b>3</b> أرسل الطلب عبر واتساب</li>
                </ol>
            </div>
        </header>

        <section class="shop-catalog">
            <div class="osif-container">
                @if ($companies->isEmpty())
                    <div class="shop-empty">
                        <i class="bi bi-buildings" aria-hidden="true"></i>
                        <h3>تُعلن الشركات المشاركة قريباً</h3>
                        <p>تابع صفحة الملتقى لمعرفة الشركات ومنتجاتها وعروضها.</p>
                        <a class="shop-link" href="{{ route('osif') }}">العودة إلى صفحة الملتقى</a>
                    </div>
                @else
                    @if ($companies->count() > 6)
                        <div class="shop-toolbar">
                            <label class="shop-search">
                                <i class="bi bi-search" aria-hidden="true"></i>
                                <span class="visually-hidden">ابحث عن شركة</span>
                                <input type="search" x-model="query" placeholder="ابحث عن شركة…" autocomplete="off">
                            </label>
                        </div>
                    @endif

                    <div class="shop-companies">
                        @foreach ($companies as $company)
                            <a class="shop-company-card" href="{{ route('osif.companies.show', $company) }}" x-show="matches(@js($company->name))">
                                <span class="shop-company-card-logo">
                                    @if ($company->logoUrl())
                                        <img src="{{ $company->logoUrl() }}" alt="" loading="lazy">
                                    @else
                                        <span>{{ $company->initials() }}</span>
                                    @endif
                                </span>
                                <span class="shop-company-card-body">
                                    <strong>{{ $company->name }}</strong>
                                    <span class="shop-company-meta">
                                        @if ($company->booth)
                                            <span><i class="bi bi-geo-alt" aria-hidden="true"></i> جناح {{ $company->booth }}</span>
                                        @endif
                                        <span><i class="bi bi-box-seam" aria-hidden="true"></i> {{ $company->products_count ? \App\Support\Arabic::products($company->products_count) : 'المنتجات قريباً' }}</span>
                                        @if ($company->offers_count)
                                            <span class="is-offer"><i class="bi bi-tag-fill" aria-hidden="true"></i> {{ \App\Support\Arabic::offers($company->offers_count) }}</span>
                                        @endif
                                    </span>
                                </span>
                                <span class="shop-company-card-go">تصفّح المنتجات <i class="bi bi-arrow-left" aria-hidden="true"></i></span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </main>

    <footer class="osif-footer">
        <div class="osif-container">
            <div class="osif-footer-wordmark"><strong>OSIF</strong> KARBALA <span>2026</span></div>
            <p>ملتقى كربلاء لجراحة الفم وزراعة الأسنان</p>
            <div>
                <a href="{{ route('osif') }}">صفحة الملتقى <i class="bi bi-arrow-up-left"></i></a>
            </div>
        </div>
    </footer>
</x-layouts.osif>
