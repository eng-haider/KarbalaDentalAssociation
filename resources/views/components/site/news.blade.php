@props(['items', 'heading' => true])

@if ($items->isNotEmpty())
    <section class="section bg-light-2" id="news">
        <div class="container">
            @if ($heading)
                <div class="text-center mb-5 reveal">
                    <span class="eyebrow">آخر المستجدات</span>
                    <h2 class="section-title">نشاطات النقابة</h2>
                    <p class="section-subtitle">تابع آخر نشاطات ونشاطات نقابة أطباء الأسنان – فرع كربلاء المقدسة.</p>
                </div>
            @endif

            <div class="row g-4">
                @foreach ($items as $item)
                    <div class="col-md-6 col-lg-4 reveal @if($loop->index) delay-{{ min($loop->index, 3) }} @endif">
                        <article class="card news-card hover-lift h-100">
                            <x-site.news-thumb :item="$item" />
                            <div class="news-body">
                                <span class="news-date">
                                    <i class="bi bi-calendar3"></i> {{ $item->published_at?->translatedFormat('j F Y') }}
                                </span>
                                <h3>{{ $item->title }}</h3>
                                <p>{{ $item->excerpt }}</p>
                                <a href="{{ route('news.show', $item) }}" class="service-link">اقرأ المزيد <i class="bi bi-arrow-left"></i></a>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>

            @if ($heading)
                <div class="text-center mt-5 reveal">
                    <a href="{{ route('news.index') }}" class="btn btn-outline-gov">
                        كل النشاطات <i class="bi bi-arrow-left"></i>
                    </a>
                </div>
            @endif
        </div>
    </section>
@endif
