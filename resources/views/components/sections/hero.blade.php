@props(['org', 'stats', 'slides' => []])

@php
    $ctaStyles = [
        'light'  => 'bg-white text-brand-800 hover:bg-brand-50',
        'accent' => 'bg-gov-gold text-brand-900 hover:brightness-95',
        'ghost'  => 'bg-white/5 text-white ring-1 ring-inset ring-white/25 hover:bg-white/10',
    ];
@endphp

<section id="hero" class="relative overflow-hidden bg-brand-900">
    {{-- ===== Background: official photo + navy legibility overlay ===== --}}
    {{-- Swap the photo by dropping your image at public/images/hero-bg.jpg and
         changing 'hero-bg.svg' → 'hero-bg.jpg' below. --}}
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="absolute inset-0 bg-brand-900"></div>
        <div class="absolute inset-0 bg-cover bg-center"
             style="background-image: url('{{ asset('images/hero-bg.svg') }}');"></div>
        {{-- lighter wash: lets the background photo show, still dark on the
             right (reading side, RTL) so the headline text stays legible --}}
        <div class="absolute inset-0 bg-gradient-to-l from-brand-900/92 via-brand-900/55 to-brand-900/20"></div>
        <div class="absolute inset-0 bg-grid opacity-30"></div>
        <div class="absolute inset-x-0 top-0 h-0.5 bg-gov-gold/70"></div>
        <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-brand-900 to-transparent"></div>
    </div>

    <div class="container-px relative pt-14 pb-24 sm:pt-16 lg:pt-20">
        <div class="grid items-center gap-14 lg:grid-cols-12">

            {{-- ===== Slider (copy) ===== --}}
            <div class="lg:col-span-12"
                 x-data="carousel({{ count($slides) }})"
                 @mouseenter="paused = true" @mouseleave="paused = false"
                 @touchstart.passive="touchStart($event)" @touchend.passive="touchEnd($event)"
                 @keydown.arrow-left.prevent="next()" @keydown.arrow-right.prevent="prev()"
                 tabindex="0" role="region" aria-roledescription="عرض شرائح"
                 aria-label="أبرز خدمات وأخبار النقابة"
                 class="focus:outline-none focus-visible:ring-2 focus-visible:ring-white/40 focus-visible:ring-offset-4 focus-visible:ring-offset-brand-900 rounded-sm">

                {{-- stacked slides: all rendered, only the active one is visible (crossfade) --}}
                <div class="grid">
                    @foreach($slides as $i => $slide)
                        <div class="col-start-1 row-start-1 transition-opacity duration-500 ease-out motion-reduce:transition-none"
                             :class="active === {{ $i }} ? 'opacity-100' : 'opacity-0 pointer-events-none'"
                             :aria-hidden="(active !== {{ $i }}).toString()"
                             role="group" aria-roledescription="شريحة"
                             aria-label="{{ $i + 1 }} من {{ count($slides) }}">

                            <span class="inline-flex items-center gap-2 rounded-sm border-r-2 border-gov-gold bg-white/10 px-3.5 py-1.5 text-sm font-bold text-white">
                                <span class="size-2 bg-gov-gold"></span>
                                {{ $slide['eyebrow'] }}
                            </span>

                            <h1 class="mt-6 text-4xl font-black leading-[1.15] tracking-tight text-white text-balance sm:text-5xl lg:text-[3.4rem]">
                                {{ $slide['title_main'] }}
                                <span class="mt-2 block text-gov-gold-soft">
                                    {{ $slide['title_accent'] }}
                                </span>
                            </h1>

                            <p class="mt-6 max-w-xl text-lg leading-relaxed text-brand-100 text-pretty">
                                {{ $slide['desc'] }}
                            </p>

                            {{-- CTAs --}}
                            <div class="mt-9 flex flex-wrap items-center gap-3">
                                @foreach($slide['ctas'] as $cta)
                                    <a href="{{ $cta['href'] }}" class="btn {{ $ctaStyles[$cta['style']] ?? $ctaStyles['ghost'] }}">
                                        <x-icon :name="$cta['icon']" class="size-4" /> {{ $cta['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- ===== Slider controls ===== --}}
                <div class="mt-9 flex flex-wrap items-center gap-x-6 gap-y-4">
                    {{-- prev / next --}}
                    <div class="flex items-center gap-2">
                        <button @click="prev()" aria-label="الشريحة السابقة"
                                class="grid size-11 place-items-center rounded-sm bg-white/5 text-white ring-1 ring-inset ring-white/20 transition-colors hover:bg-white/10">
                            <x-icon name="chevron-left" class="size-5 rotate-180" />
                        </button>
                        <button @click="next()" aria-label="الشريحة التالية"
                                class="grid size-11 place-items-center rounded-sm bg-white/5 text-white ring-1 ring-inset ring-white/20 transition-colors hover:bg-white/10">
                            <x-icon name="chevron-left" class="size-5" />
                        </button>
                    </div>

                    {{-- progress dots --}}
                    <div class="flex items-center gap-2" role="tablist" aria-label="مؤشر الشرائح">
                        @foreach($slides as $i => $slide)
                            <button @click="go({{ $i }})" role="tab"
                                    :aria-selected="(active === {{ $i }}).toString()"
                                    aria-label="الانتقال إلى الشريحة {{ $i + 1 }}"
                                    class="relative h-1.5 overflow-hidden rounded-sm bg-white/25 transition-all duration-300"
                                    :class="active === {{ $i }} ? 'w-10' : 'w-4 hover:bg-white/40'">
                                <span class="absolute inset-y-0 right-0 bg-gov-gold"
                                      :style="active === {{ $i }} ? ('width:' + progress + '%') : 'width:0%'"></span>
                            </button>
                        @endforeach
                    </div>

                    {{-- counter --}}
                    <span class="nums text-sm font-semibold text-brand-100/70">
                        <span class="text-white" x-text="new Intl.NumberFormat('ar-IQ').format(active + 1)">١</span>
                        <span class="mx-1 opacity-50">/</span>
                        <span x-text="new Intl.NumberFormat('ar-IQ').format(count)">{{ count($slides) }}</span>
                    </span>
                </div>

                {{-- trust row (persistent) --}}
                <div class="mt-10 flex flex-wrap items-center gap-x-8 gap-y-3 border-t border-white/10 pt-6 text-sm text-brand-100/80">
                    <span class="flex items-center gap-2"><x-icon name="check" class="size-4 text-gov-gold-soft" /> توثيق رسمي للأعضاء</span>
                    <span class="flex items-center gap-2"><x-icon name="check" class="size-4 text-gov-gold-soft" /> معاملات إلكترونية آمنة</span>
                    <span class="flex items-center gap-2"><x-icon name="check" class="size-4 text-gov-gold-soft" /> دعم على مدار الأسبوع</span>
                </div>
            </div>

        </div>
    </div>

    {{-- ===== Announcement ticker ===== --}}
    <div class="relative border-y border-white/10 bg-brand-950">
        <div class="container-px flex items-center gap-4 py-3 text-sm">
            <span class="flex shrink-0 items-center gap-2 rounded-sm bg-gov-gold px-3 py-1 text-xs font-bold text-brand-900">
                <x-icon name="alert" class="size-3.5" /> عاجل
            </span>
            <div class="relative flex-1 overflow-hidden">
                <div class="flex w-max gap-12 whitespace-nowrap text-brand-100/90 animate-marquee">
                    @foreach(\App\Support\SiteData::announcements() as $a)
                        <span class="flex items-center gap-2"><span class="size-1.5 bg-gov-gold"></span> {{ $a['title'] }}</span>
                    @endforeach
                    @foreach(\App\Support\SiteData::announcements() as $a)
                        <span class="flex items-center gap-2"><span class="size-1.5 bg-gov-gold"></span> {{ $a['title'] }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
