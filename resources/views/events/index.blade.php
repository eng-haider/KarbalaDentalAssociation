<x-layouts.site title="فعاليات النقابة | {{ config('app.name') }}">
    <x-site.page-header
        title="فعاليات النقابة"
        eyebrow="أجندة النقابة"
        subtitle="مواعيد المؤتمرات والدورات والاجتماعات القادمة." />

    <section class="section">
        <div class="container">
            <h2 class="section-title mb-4" style="font-size:1.4rem">الفعاليات القادمة</h2>
            @if ($upcoming->isEmpty())
                <p class="text-muted-2 mb-5">لا توجد فعاليات قادمة حالياً.</p>
            @else
                <div class="row g-4 mb-5">
                    @foreach ($upcoming as $event)
                        <div class="col-md-6 reveal">
                            <article class="card event-list-card hover-lift h-100 p-4">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="event-date-badge text-center">
                                        <strong>{{ $event->starts_at->translatedFormat('j') }}</strong>
                                        <span>{{ $event->starts_at->translatedFormat('M') }}</span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h3 style="font-size:1.1rem">{{ $event->title }}</h3>
                                        <p class="text-muted-2 mb-2">{{ Str::limit($event->description, 110) }}</p>
                                        <div class="article-meta mb-3">
                                            <span><i class="bi bi-clock"></i> {{ $event->starts_at->translatedFormat('g:i A') }}</span>
                                            @if ($event->location)
                                                <span><i class="bi bi-geo-alt"></i> {{ $event->location }}</span>
                                            @endif
                                        </div>
                                        <a href="{{ route('events.show', $event) }}" class="btn btn-sm btn-gov">
                                            التفاصيل والتسجيل <i class="bi bi-arrow-left"></i>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($past->isNotEmpty())
                <h2 class="section-title mb-4" style="font-size:1.4rem">فعاليات سابقة</h2>
                <div class="row g-3">
                    @foreach ($past as $event)
                        <div class="col-md-6 col-lg-4">
                            <div class="card p-3 h-100">
                                <h3 style="font-size:1rem" class="mb-1">{{ $event->title }}</h3>
                                <small class="text-muted-2">
                                    <i class="bi bi-calendar3"></i> {{ $event->starts_at->translatedFormat('j F Y') }}
                                </small>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-layouts.site>
