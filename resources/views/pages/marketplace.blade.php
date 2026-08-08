@php($types = \App\Models\MarketplaceListing::types())

<x-layouts.site title="بيع وشراء | {{ config('app.name') }}">
    <x-site.page-header
        title="بيع وشراء"
        eyebrow="سوق النقابة"
        subtitle="مساحة مخصّصة لأعضاء النقابة لعرض الأجهزة والمواد المعروضة للبيع أو المطلوبة للشراء." />

    <div class="page-body">
        <section class="section" id="marketplace">
            <div class="container">
                <nav class="course-tabs reveal" aria-label="تصنيفات الإعلانات">
                    <a href="{{ route('marketplace.index') }}"
                       class="course-tab @if (! $activeType) active @endif"
                       @if (! $activeType) aria-current="page" @endif>
                        <i class="bi bi-grid" aria-hidden="true"></i>
                        الكل
                        <span class="course-tab-count">{{ $totalCount }}</span>
                    </a>
                    @foreach ($types as $value => $label)
                        <a href="{{ route('marketplace.index', ['type' => $value]) }}"
                           class="course-tab @if ($activeType === $value) active @endif"
                           @if ($activeType === $value) aria-current="page" @endif>
                            <i class="bi {{ $value === \App\Models\MarketplaceListing::TYPE_SALE ? 'bi-tag' : 'bi-search' }}" aria-hidden="true"></i>
                            {{ $label }}
                            <span class="course-tab-count">{{ $counts[$value] ?? 0 }}</span>
                        </a>
                    @endforeach

                    <a href="#marketplace-add" class="btn btn-gov btn-sm ms-auto listing-add-btn">
                        <i class="bi bi-plus-circle" aria-hidden="true"></i> أضف إعلانك
                    </a>
                </nav>

                @if ($items->isEmpty())
                    <div class="listing-empty text-center py-5">
                        <i class="bi bi-box-seam" aria-hidden="true"></i>
                        <p class="text-muted-2 mb-3">
                            @if ($activeType)
                                لا توجد إعلانات ضمن "{{ $types[$activeType] }}" حالياً.
                            @else
                                لا توجد إعلانات منشورة حالياً. كن أول من يضيف إعلاناً.
                            @endif
                        </p>
                        <a href="#marketplace-add" class="btn btn-gov">
                            <i class="bi bi-plus-circle" aria-hidden="true"></i> أضف إعلانك
                        </a>
                    </div>
                @else
                    <div class="row g-4">
                        @foreach ($items as $item)
                            <div class="col-md-6 col-lg-4 reveal">
                                <x-site.listing-card :item="$item" />
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-center mt-5">
                        {{ $items->links() }}
                    </div>
                @endif
            </div>
        </section>

        <x-site.marketplace-form />
    </div>
</x-layouts.site>
