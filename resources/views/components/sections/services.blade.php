@props(['services'])

@php
    $tone = [
        'brand' => ['ic' => 'bg-brand-50 text-brand-700 group-hover:bg-brand-800'],
        'sky'   => ['ic' => 'bg-sky-50 text-sky-700 group-hover:bg-sky-accent'],
        'green' => ['ic' => 'bg-emerald-50 text-emerald-700 group-hover:bg-green-accent'],
    ];
@endphp

<section id="services" class="container-px scroll-mt-24 py-20 sm:py-24">
    <div x-data="reveal()" x-intersect.once="show()" class="reveal">
        <x-section-heading
            center
            eyebrow="الخدمات الرقمية"
            title="أنجز معاملاتك إلكترونياً خلال دقائق"
            subtitle="منظومة خدمات متكاملة تختصر الوقت والجهد، متاحة على مدار الساعة من أي جهاز." />
    </div>

    <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($services as $i => $svc)
            @php($t = $tone[$svc['tone']])
            <a href="#" x-data="reveal()" x-intersect.once="show()"
               class="reveal group relative card card-hover overflow-hidden border-t-2 border-transparent p-7 hover:border-gov-gold"
               style="transition-delay: {{ $i * 70 }}ms">
                <div class="relative">
                    <span class="grid size-14 place-items-center rounded-sm {{ $t['ic'] }} text-current transition-colors duration-200 group-hover:text-white">
                        <x-icon :name="$svc['icon']" class="size-7" />
                    </span>

                    <h3 class="mt-5 text-lg font-bold text-brand-900">{{ $svc['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500 text-pretty">{{ $svc['desc'] }}</p>

                    <span class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-brand-700 transition-all group-hover:gap-3">
                        {{ $svc['cta'] }} <x-icon name="arrow-left" class="size-4" />
                    </span>
                </div>
            </a>
        @endforeach
    </div>

    {{-- helper banner --}}
    <div x-data="reveal()" x-intersect.once="show()"
         class="reveal mt-8 flex flex-col items-center justify-between gap-4 rounded-lg border-r-4 border-gov-gold bg-brand-50 p-6 ring-1 ring-brand-100 sm:flex-row">
        <div class="flex items-center gap-4">
            <span class="grid size-12 place-items-center rounded-sm bg-white text-brand-700 ring-1 ring-cloud"><x-icon name="phone" class="size-6" /></span>
            <div>
                <p class="font-bold text-brand-900">تحتاج مساعدة في إحدى الخدمات؟</p>
                <p class="text-sm text-slate-500">فريق الدعم متاح لمساعدتك خلال أوقات الدوام الرسمي.</p>
            </div>
        </div>
        <a href="#contact" class="btn-primary shrink-0">تواصل مع الدعم</a>
    </div>
</section>
