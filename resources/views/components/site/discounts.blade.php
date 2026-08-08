@props(['items', 'heading' => true])

@if ($items->isNotEmpty())
    <section class="section discounts-section" id="discounts">
        <div class="container">
            @if ($heading)
            <div class="text-center mb-5 reveal">
                <span class="eyebrow">امتيازات العضوية</span>
                <h2 class="section-title">خصومات أعضاء النقابة</h2>
                <p class="section-subtitle">امتيازات وخصومات حصرية لأعضاء نقابة أطباء الأسنان المجدّدين.</p>
            </div>
            @endif

            <div class="ad-list">
                @foreach ($items as $discount)
                    <article class="ad-card reveal">
                        {{-- Backdrop: the partner's photo, or a branded panel when there isn't one --}}
                        @if ($discount->image)
                            <img src="{{ Storage::url($discount->image) }}" alt="" class="ad-bg" loading="lazy">
                        @else
                            <div class="ad-bg ad-bg--fallback" aria-hidden="true">
                                <i class="bi {{ $discount->icon }}"></i>
                            </div>
                        @endif

                        <span class="ad-flag">إعلان</span>

                        <div class="ad-inner">
                            <div class="ad-main">
                                <div class="ad-brandline">
                                    <span class="ad-logo">
                                        @if ($discount->logo)
                                            <img src="{{ Storage::url($discount->logo) }}" alt="{{ $discount->brand }}" loading="lazy">
                                        @else
                                            <i class="bi {{ $discount->icon }}" aria-hidden="true"></i>
                                        @endif
                                    </span>
                                    <span class="ad-brandtext">
                                        <span class="ad-brand">{{ $discount->brand }}</span>
                                        @if ($discount->tag)
                                            <span class="ad-tag">{{ $discount->tag }}</span>
                                        @endif
                                    </span>
                                </div>

                                <h3 class="ad-title">{{ $discount->title }}</h3>

                                @if ($discount->description)
                                    <p class="ad-desc">{{ $discount->description }}</p>
                                @endif

                                @if ($discount->perks)
                                    <ul class="ad-perks">
                                        @foreach ($discount->perks as $perk)
                                            <li><i class="bi bi-check-lg" aria-hidden="true"></i> {{ $perk }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="ad-offer">
                                <span class="ad-offer-ribbon">عرض حصري لأعضاء النقابة</span>
                                <strong class="ad-value">{{ $discount->value_label }}</strong>
                                @if ($discount->value_caption)
                                    <span class="ad-value-sub">{{ $discount->value_caption }}</span>
                                @endif

                                <a href="{{ $discount->link ?: '#' }}"
                                   @if ($discount->link) target="_blank" rel="noopener" @endif
                                   class="ad-cta">
                                    <i class="bi bi-box-arrow-up-left" aria-hidden="true"></i>
                                    سجّل واحصل على الخصم
                                </a>

                                @if ($discount->note)
                                    <span class="ad-note">
                                        <i class="bi bi-info-circle" aria-hidden="true"></i> {{ $discount->note }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
