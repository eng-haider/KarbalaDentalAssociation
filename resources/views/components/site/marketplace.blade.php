@props(['items', 'heading' => true])

@if ($items->isNotEmpty())
    <section class="section" id="marketplace">
        <div class="container">
            @if ($heading)
                <div class="text-center mb-5 reveal">
                    <span class="eyebrow">سوق النقابة</span>
                    <h2 class="section-title">بيع وشراء</h2>
                    <p class="section-subtitle">أجهزة ومواد معروضة للبيع أو مطلوبة للشراء من قبل أعضاء النقابة.</p>
                </div>
            @endif

            <div class="row g-4">
                @foreach ($items as $item)
                    <div class="col-md-6 col-lg-4 reveal">
                        <x-site.listing-card :item="$item" />
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-5">
                <a href="{{ route('marketplace.index') }}" class="btn btn-gov">
                    تصفّح جميع الإعلانات <i class="bi bi-arrow-left" aria-hidden="true"></i>
                </a>
                <a href="{{ route('marketplace.index') }}#marketplace-add" class="btn btn-outline-gov">
                    <i class="bi bi-plus-circle" aria-hidden="true"></i> أضف إعلانك
                </a>
            </div>
        </div>
    </section>
@endif
