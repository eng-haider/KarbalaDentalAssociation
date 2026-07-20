<x-layouts.site title="نشاطات النقابة | {{ config('app.name') }}">
    <x-site.page-header
        title="نشاطات النقابة"
        eyebrow="آخر المستجدات"
        subtitle="تابع آخر نشاطات ونشاطات نقابة أطباء الأسنان – فرع كربلاء المقدسة." />

    <section class="section">
        <div class="container">
            @if ($items->isEmpty())
                <p class="text-center text-muted-2 py-5">لا توجد نشاطات منشورة حالياً.</p>
            @else
                <div class="row g-4">
                    @foreach ($items as $item)
                        <div class="col-md-6 col-lg-4 reveal">
                            <article class="card news-card hover-lift h-100">
                                <x-site.news-thumb :item="$item" />
                                <div class="news-body">
                                    <time class="news-date" datetime="{{ $item->published_at?->toDateString() }}">
                                        <i class="bi bi-calendar3"></i> {{ $item->published_at?->translatedFormat('j F Y') }}
                                    </time>
                                    <h3>{{ $item->title }}</h3>
                                    <p>{{ $item->excerpt }}</p>
                                    <a href="{{ route('news.show', $item) }}" class="service-link">اقرأ المزيد <i class="bi bi-arrow-left"></i>
                                    </a>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-center mt-5">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </section>
</x-layouts.site>
