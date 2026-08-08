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

            <div class="ad-list">
                @foreach ($items as $discount)
                    {{-- Only linked ads are clickable; the rest stay plain banners. --}}
                    @php($tag = $discount->link ? 'a' : 'div')
                    <{{ $tag }} class="ad-card reveal"
                        @if ($discount->link) href="{{ $discount->link }}" target="_blank" rel="noopener" @endif>

                        @if ($discount->image)
                            <img src="{{ Storage::url($discount->image) }}" alt="{{ $discount->title }}" class="ad-bg" loading="lazy">
                        @else
                            <div class="ad-bg ad-bg--fallback" aria-hidden="true">
                                <i class="bi {{ $discount->icon }}"></i>
                            </div>
                        @endif

                        <h3 class="ad-title">{{ $discount->title }}</h3>
                    </{{ $tag }}>
                @endforeach
            </div>
        </div>
    </section>
@endif
