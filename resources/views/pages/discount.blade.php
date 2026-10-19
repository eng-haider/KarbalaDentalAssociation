<x-layouts.site
    :title="$item->title . ' | ' . config('app.name')"
    :description="$item->description"
    :og-image="$item->image ? url(Storage::url($item->image)) : null">
    <x-site.page-header title="{{ $item->title }}" eyebrow="خصومات الأعضاء" />

    <article class="article-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    @if ($item->image)
                        <figure class="article-figure">
                            <img src="{{ Storage::url($item->image) }}" alt="{{ $item->title }}">
                        </figure>
                    @endif

                    @if ($item->description)
                        <p class="article-lead">{{ $item->description }}</p>
                    @endif

                    @if (filled(strip_tags((string) $item->body)))
                        <div class="article-body">
                            {!! $item->body !!}
                        </div>
                    @endif

                    @if ($item->attachment)
                        @php($isPdf = $item->attachmentIsPdf())
                        <div class="offer-file">
                            <span @class(['offer-file-icon', 'is-pdf' => $isPdf])>
                                <i class="bi {{ $isPdf ? 'bi-file-earmark-pdf' : 'bi-file-earmark-word' }}" aria-hidden="true"></i>
                            </span>
                            <span class="offer-file-text">
                                <strong>{{ $isPdf ? 'ملف PDF' : 'ملف Word' }}</strong>
                                <small>{{ $item->attachmentFileName() }}</small>
                            </span>
                            <a href="{{ route('discounts.file', $item) }}" class="btn btn-outline-gov" @if ($isPdf) target="_blank" rel="noopener" @endif>
                                <i class="bi {{ $isPdf ? 'bi-eye' : 'bi-download' }}" aria-hidden="true"></i>
                                {{ $isPdf ? 'فتح الملف' : 'تحميل الملف' }}
                            </a>
                        </div>
                    @endif

                    @if ($item->link)
                        <a href="{{ $item->link }}" class="btn btn-gov offer-cta" target="_blank" rel="noopener">
                            <i class="bi bi-box-arrow-up-left" aria-hidden="true"></i> الانتقال إلى رابط العرض
                        </a>
                    @endif

                    @if ($item->note)
                        <p class="offer-note"><i class="bi bi-info-circle" aria-hidden="true"></i> {{ $item->note }}</p>
                    @endif

                    <div>
                        <a href="{{ route('discounts') }}" class="article-back">
                            <i class="bi bi-arrow-right" aria-hidden="true"></i> العودة إلى خصومات الأعضاء
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </article>
</x-layouts.site>
