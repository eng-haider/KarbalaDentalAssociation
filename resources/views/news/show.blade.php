<x-layouts.site :title="$item->title . ' | ' . config('app.name')" :description="$item->excerpt">
    <x-site.page-header title="{{ $item->title }}" eyebrow="خبر" />

    <article class="article-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <x-site.news-media :item="$item" />

                    <div class="article-meta">
                        <span><i class="bi bi-calendar3"></i> {{ $item->published_at?->translatedFormat('l j F Y') }}</span>
                    </div>

                    <div class="article-body">
                        {!! $item->body !!}
                    </div>

                    <a href="{{ route('news.index') }}" class="article-back">
                        <i class="bi bi-arrow-right"></i> العودة إلى النشاطات
                    </a>
                </div>
            </div>

            @if ($related->isNotEmpty())
                <div class="row g-4 mt-5">
                    <div class="col-12">
                        <h2 class="section-title mb-4" style="font-size:1.4rem">نشاطات ذات صلة</h2>
                    </div>
                    @foreach ($related as $rel)
                        <div class="col-md-4">
                            <article class="card news-card hover-lift h-100">
                                @if ($rel->image)
                                    <div class="news-thumb"><img src="{{ Storage::url($rel->image) }}" alt="{{ $rel->title }}"></div>
                                @endif
                                <div class="news-body">
                                    <h3>{{ $rel->title }}</h3>
                                    <a href="{{ route('news.show', $rel) }}" class="btn btn-sm btn-outline-gov mt-2">
                                        اقرأ المزيد <i class="bi bi-arrow-left"></i>
                                    </a>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </article>
</x-layouts.site>
