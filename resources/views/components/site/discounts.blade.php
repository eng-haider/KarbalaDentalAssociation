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

            <div class="discount-list">
                @foreach ($items as $discount)
                    <article class="discount-card reveal">
                        <div class="discount-media">
                            @if ($discount->image)
                                <img src="{{ Storage::url($discount->image) }}" alt="{{ $discount->brand }}" class="discount-img" loading="lazy">
                            @else
                                <div class="discount-img discount-img--fallback">
                                    <i class="bi {{ $discount->icon }}" aria-hidden="true"></i>
                                </div>
                            @endif

                            <span class="discount-ribbon">عرض حصري لأعضاء النقابة</span>

                            <div class="discount-value-badge">
                                <strong>{{ $discount->value_label }}</strong>
                                @if ($discount->value_caption)
                                    <span>{{ $discount->value_caption }}</span>
                                @endif
                            </div>

                            <div class="discount-brandbar">
                                <span class="discount-logo">
                                    @if ($discount->logo)
                                        <img src="{{ Storage::url($discount->logo) }}" alt="{{ $discount->brand }}" loading="lazy">
                                    @else
                                        <i class="bi {{ $discount->icon }}" aria-hidden="true"></i>
                                    @endif
                                </span>
                                <span class="discount-brand">{{ $discount->brand }}</span>
                            </div>
                        </div>

                        <div class="discount-body">
                            @if ($discount->tag)
                                <span class="discount-tag">
                                    <i class="bi bi-stars" aria-hidden="true"></i> {{ $discount->tag }}
                                </span>
                            @endif
                            <h3>{{ $discount->title }}</h3>
                            @if ($discount->description)
                                <p>{{ $discount->description }}</p>
                            @endif

                            @if ($discount->perks)
                                <ul class="discount-perks">
                                    @foreach ($discount->perks as $perk)
                                        <li><i class="bi bi-check-lg" aria-hidden="true"></i> {{ $perk }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            <div class="discount-actions">
                                <a href="{{ $discount->link ?: '#' }}"
                                   @if ($discount->link) target="_blank" rel="noopener" @endif
                                   class="btn btn-gov btn-lg">
                                    <i class="bi bi-box-arrow-up-left" aria-hidden="true"></i> سجّل واحصل على الخصم
                                </a>
                                @if ($discount->note)
                                    <span class="discount-note">
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
