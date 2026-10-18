@props(['companies'])

{{-- The OSIF exhibitors, each opening its own page with products, offers and the WhatsApp cart. --}}
@if ($companies->isNotEmpty())
    <section class="section osif-companies-section" id="osif-companies">
        <div class="container">
            <div class="text-center mb-5 reveal">
                <span class="eyebrow">OSIF KARBALA 2026</span>
                <h2 class="section-title">عروض الشركات المشاركة</h2>
                <p class="section-subtitle">تصفّح منتجات الشركات المشاركة في الملتقى وعروضها، واطلب مباشرة عبر واتساب.</p>
            </div>

            <div class="row g-3 g-lg-4 justify-content-center">
                @foreach ($companies as $company)
                    <div class="col-12 col-md-6 col-lg-4">
                        <a class="card company-card hover-lift h-100 reveal" href="{{ route('osif.companies.show', $company) }}">
                            <span class="company-logo">
                                @if ($company->logoUrl())
                                    <img src="{{ $company->logoUrl() }}" alt="" loading="lazy">
                                @else
                                    {{ $company->initials() }}
                                @endif
                            </span>
                            <span class="company-text">
                                <strong class="company-name">{{ $company->name }}</strong>
                                <span class="company-chips">
                                    <span class="company-chip"><i class="bi bi-box-seam" aria-hidden="true"></i> {{ $company->products_count ? \App\Support\Arabic::products($company->products_count) : 'المنتجات قريباً' }}</span>
                                    @if ($company->offers_count)
                                        <span class="company-chip company-chip--offer"><i class="bi bi-tag-fill" aria-hidden="true"></i> {{ \App\Support\Arabic::offers($company->offers_count) }}</span>
                                    @endif
                                </span>
                            </span>
                            <i class="bi bi-chevron-left company-go" aria-hidden="true"></i>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
