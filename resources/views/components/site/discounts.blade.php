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

            <div class="row justify-content-center g-4">
                @foreach ($items as $discount)
                    <div class="col-xl-11 reveal">
                        <article class="discount-card">
                            <div class="row g-0">
                                <div class="col-md-5">
                                    <div class="discount-panel">
                                        <span class="discount-ribbon">عرض حصري لأعضاء النقابة</span>
                                        <span class="discount-logo"><i class="bi {{ $discount->icon }}" aria-hidden="true"></i></span>
                                        <p class="discount-value">{{ $discount->value_label }}</p>
                                        @if ($discount->value_caption)
                                            <p class="discount-value-sub">{{ $discount->value_caption }}</p>
                                        @endif
                                        <span class="discount-brand">{{ $discount->brand }}</span>
                                    </div>
                                </div>

                                <div class="col-md-7">
                                    <div class="discount-body">
                                        @if ($discount->tag)
                                            <span class="discount-tag">
                                                <i class="bi bi-phone" aria-hidden="true"></i> {{ $discount->tag }}
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
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
