@props(['news'])

@php
    $toneMap = [
        'brand' => 'text-brand-700 ring-brand-200',
        'sky'   => 'text-sky-700 ring-sky-200',
        'green' => 'text-emerald-700 ring-emerald-200',
    ];
    $coverIcons = ['conference' => 'users', 'handshake' => 'academic', 'kids' => 'heart'];
@endphp

<section id="news" class="container-px scroll-mt-24 py-20 sm:py-24">
    <div x-data="reveal()" x-intersect.once="show()" class="reveal">
        <x-section-heading
            eyebrow="المركز الإخباري"
            title="آخر الأخبار والمستجدات"
            subtitle="تابع أحدث أنشطة النقابة ومشاركاتها المحلية والعلمية أولاً بأول.">
            <x-slot:action>
                <a href="#" class="btn-soft">عرض كل الأخبار <x-icon name="arrow-left" class="size-4" /></a>
            </x-slot:action>
        </x-section-heading>
    </div>

    <div class="mt-12 grid gap-6 lg:grid-cols-3">
        @foreach($news as $i => $item)
            <article x-data="reveal()" x-intersect.once="show()"
                     class="reveal group card card-hover flex flex-col overflow-hidden"
                     style="transition-delay: {{ $i * 90 }}ms">
                {{-- cover --}}
                <div class="relative overflow-hidden border-b border-cloud">
                    <x-cover :tone="$item['tone']" :icon="$coverIcons[$item['image']] ?? 'image'" />
                    <span class="absolute right-4 top-4 inline-flex items-center gap-1.5 rounded-sm px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $toneMap[$item['tone']] }} bg-white">
                        {{ $item['category'] }}
                    </span>
                </div>

                {{-- body --}}
                <div class="flex flex-1 flex-col p-6">
                    <div class="flex items-center gap-3 text-xs font-medium text-slate-400">
                        <span class="flex items-center gap-1.5"><x-icon name="calendar" class="size-3.5" /> {{ $item['date'] }}</span>
                        <span class="size-1 rounded-full bg-slate-300"></span>
                        <span class="flex items-center gap-1.5"><x-icon name="clock" class="size-3.5" /> {{ $item['reading'] }}</span>
                    </div>

                    <h3 class="mt-3 text-lg font-bold leading-snug text-brand-900 transition group-hover:text-brand-700 text-balance">
                        {{ $item['title'] }}
                    </h3>
                    <p class="mt-2.5 flex-1 text-sm leading-relaxed text-slate-500 text-pretty">{{ $item['excerpt'] }}</p>

                    <a href="#" class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-brand-700 transition group-hover:gap-3">
                        اقرأ المزيد <x-icon name="arrow-left" class="size-4" />
                    </a>
                </div>
            </article>
        @endforeach
    </div>
</section>
