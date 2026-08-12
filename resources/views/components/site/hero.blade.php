@props(['slides'])

<section class="hero" id="hero">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="6000">
        @if ($slides->count() > 1)
            <div class="carousel-indicators">
                @foreach ($slides as $slide)
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $loop->index }}"
                            @class(['active' => $loop->first]) @if($loop->first) aria-current="true" @endif
                            aria-label="الشريحة {{ $loop->iteration }}"></button>
                @endforeach
            </div>
        @endif

        <div class="carousel-inner">
            @foreach ($slides as $slide)
                <div class="carousel-item @if($loop->first) active @endif">
                    @if ($slide->imageUrl())
                        <img src="{{ $slide->imageUrl() }}" class="hero-bg" alt="{{ $slide->title }}">
                    @endif
                    <div class="carousel-caption">
                        <div class="container">
                            <div class="col-lg-8">
                                @if ($slide->badge)
                                    <span class="hero-badge reveal"><i class="bi {{ $slide->badge_icon }}"></i> {{ $slide->badge }}</span>
                                @endif
                                <h1 class="hero-title mt-3 reveal delay-1">{!! nl2br(e($slide->title)) !!}</h1>
                                @if ($slide->subtitle)
                                    <p class="hero-sub mt-3 reveal delay-2">{{ $slide->subtitle }}</p>
                                @endif
                                @if ($slide->button_label || $slide->button2_label)
                                    <div class="d-flex flex-wrap gap-3 mt-4 reveal delay-3">
                                        @if ($slide->button_label)
                                            <a href="{{ $slide->button_url ?: '#' }}" class="btn btn-lg btn-gold">{{ $slide->button_label }}</a>
                                        @endif
                                        @if ($slide->button2_label)
                                            <a href="{{ $slide->button2_url ?: '#' }}" class="btn btn-lg btn-outline-white">{{ $slide->button2_label }}</a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($slides->count() > 1)
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">السابق</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">التالي</span>
            </button>
        @endif
    </div>

    <div class="container hero-strip">
        <div class="card-strip">
            <div class="row g-0">
                {{-- Values come from لوحة التحكم → إعدادات الموقع (معلومات التواصل). --}}
                <div class="col-md-4 strip-item reveal">
                    <i class="bi bi-clock-history"></i>
                    <div><small>أوقات الدوام الرسمي</small><br><strong>{{ setting('working_hours', 'الأحد – الخميس: ٩:٠٠ ص – ٣:٠٠ م') }}</strong></div>
                </div>
                <div class="col-md-4 strip-item reveal delay-1">
                    <i class="bi bi-geo-alt-fill"></i>
                    <div><small>مقر النقابة</small><br><strong>{{ setting('address', 'كربلاء المقدسة – حي الحسين') }}</strong></div>
                </div>
                <div class="col-md-4 strip-item reveal delay-2">
                    <i class="bi bi-headset"></i>
                    <div><small>خط الدعم والاستفسار</small><br><strong dir="ltr">{{ setting('phone', '+964 780 123 4567') }}</strong></div>
                </div>
            </div>
        </div>
    </div>
</section>
