@props(['announcements'])

@php
    $tone = [
        'brand' => ['chip' => 'bg-brand-50 text-brand-700 ring-brand-100',   'ic' => 'bg-brand-100 text-brand-700'],
        'sky'   => ['chip' => 'bg-sky-50 text-sky-700 ring-sky-100',         'ic' => 'bg-sky-100 text-sky-700'],
        'green' => ['chip' => 'bg-emerald-50 text-emerald-700 ring-emerald-100', 'ic' => 'bg-emerald-100 text-emerald-700'],
        'amber' => ['chip' => 'bg-amber-50 text-amber-700 ring-amber-100',   'ic' => 'bg-amber-100 text-amber-700'],
    ];
@endphp

<section id="announcements" class="relative scroll-mt-24 overflow-hidden bg-brand-900 py-20 sm:py-24">
    <div class="pointer-events-none absolute inset-0 bg-grid opacity-50"></div>
    <div class="pointer-events-none absolute inset-x-0 top-0 h-0.5 bg-gov-gold/60"></div>

    <div class="container-px relative">
        <div x-data="reveal()" x-intersect.once="show()" class="reveal">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center gap-2 rounded-sm border-r-2 border-gov-gold bg-white/10 px-3 py-1.5 text-sm font-bold text-white">
                        <span class="size-2 bg-gov-gold"></span> إعلانات رسمية
                    </span>
                    <h2 class="mt-4 text-2xl font-extrabold tracking-tight text-white sm:text-3xl lg:text-[2.1rem]">
                        التعاميم والإشعارات المهمة
                    </h2>
                    <p class="mt-3 text-base text-brand-100/80">تعاميم، تنبيهات، فعاليات، وانتخابات النقابة في مكان واحد موثّق.</p>
                    <div class="mt-5 accent-bar"></div>
                </div>
                <a href="#" class="btn bg-white/5 text-white ring-1 ring-inset ring-white/25 hover:bg-white/10">
                    جميع الإعلانات <x-icon name="arrow-left" class="size-4" />
                </a>
            </div>
        </div>

        <div class="mt-12 grid gap-4 lg:grid-cols-2">
            @foreach($announcements as $i => $a)
                @php($t = $tone[$a['tone']])
                <article x-data="reveal()" x-intersect.once="show()"
                         class="reveal group relative flex gap-4 rounded-lg bg-white/[0.04] p-5 ring-1 ring-inset ring-white/10 transition-colors hover:bg-white/[0.07] hover:ring-white/20 {{ $a['urgent'] ? 'border-r-2 border-gov-gold' : '' }}"
                         style="transition-delay: {{ $i * 70 }}ms">
                    @if($a['urgent'])
                        <span class="absolute left-4 top-4 rounded-sm bg-gov-gold px-1.5 py-0.5 text-[0.65rem] font-bold text-brand-900">عاجل</span>
                    @endif

                    <span class="grid size-12 shrink-0 place-items-center rounded-sm {{ $t['ic'] }}">
                        <x-icon :name="$a['icon']" class="size-6" />
                    </span>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2.5">
                            <span class="rounded-sm px-2 py-0.5 text-xs font-bold ring-1 ring-inset {{ $t['chip'] }}">{{ $a['type'] }}</span>
                            <span class="text-xs text-brand-100/60">{{ $a['date'] }}</span>
                        </div>
                        <h3 class="mt-2 font-bold leading-snug text-white text-balance">{{ $a['title'] }}</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-brand-100/70 text-pretty">{{ $a['desc'] }}</p>
                        <a href="#" class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-gov-gold-soft transition-all group-hover:gap-2.5">
                            التفاصيل <x-icon name="arrow-left" class="size-3.5" />
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
