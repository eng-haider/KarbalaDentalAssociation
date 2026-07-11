@php($org = \App\Support\SiteData::org())
@php($nav = \App\Support\SiteData::nav())

<footer class="relative mt-24 bg-brand-900 text-slate-300">
    {{-- official top rule --}}
    <div class="h-1 bg-gov-gold"></div>
    {{-- subtle texture --}}
    <div class="pointer-events-none absolute inset-0 bg-grid opacity-60"></div>

    <div class="relative">
        {{-- CTA strip --}}
        <div class="container-px">
            <div class="-mt-12 rounded-lg border-r-4 border-gov-gold bg-brand-800 p-8 shadow-[var(--shadow-lift)] ring-1 ring-white/10 sm:p-10">
                <div class="flex flex-col items-center gap-6 text-center md:flex-row md:justify-between md:text-right">
                    <div>
                        <h3 class="text-xl font-extrabold text-white sm:text-2xl">هل أنت طبيب أسنان في كربلاء؟</h3>
                        <p class="mt-2 text-sm text-brand-100">انضمّ إلى البوابة الرقمية وأكمل معاملاتك إلكترونياً خلال دقائق.</p>
                    </div>
                    <div class="flex flex-wrap justify-center gap-3">
                        <a href="#services" class="btn bg-gov-gold text-brand-900 hover:brightness-95">سجّل عضويتك</a>
                        <a href="#contact" class="btn bg-white/5 text-white ring-1 ring-inset ring-white/30 hover:bg-white/10">تواصل معنا</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main footer grid --}}
        <div class="container-px grid gap-12 py-16 md:grid-cols-2 lg:grid-cols-12">
            {{-- Identity --}}
            <div class="lg:col-span-4">
                <a href="#hero" class="flex items-center gap-3">
                    <x-logo class="size-12" :mono="true" />
                    <span class="flex flex-col leading-tight">
                        <span class="text-[0.7rem] font-semibold tracking-wide text-gov-gold-soft">جمهورية العراق</span>
                        <span class="text-base font-extrabold text-white">نقابة أطباء الأسنان</span>
                        <span class="text-sm font-semibold text-brand-200">فرع كربلاء المقدسة</span>
                    </span>
                </a>
                <p class="mt-5 max-w-sm text-sm leading-relaxed text-slate-400">
                    {{ $org['tagline'] }}. بوابة رسمية تخدم أعضاء النقابة والمجتمع بأعلى معايير الشفافية والجودة الرقمية.
                </p>
                <div class="mt-6 flex items-center gap-3">
                    @foreach($org['socials'] as $s)
                        <a href="{{ $s['url'] }}" aria-label="{{ $s['name'] }}"
                           class="grid size-10 place-items-center rounded-sm bg-white/5 text-slate-300 ring-1 ring-white/10 transition-colors hover:bg-gov-gold hover:text-brand-900">
                            <x-icon :name="$s['icon']" class="size-4" />
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Quick links --}}
            <div class="lg:col-span-2">
                <h4 class="text-sm font-bold text-white">روابط سريعة</h4>
                <ul class="mt-4 space-y-2.5 text-sm">
                    @foreach(array_slice($nav, 0, 6) as $item)
                        <li><a href="{{ $item['href'] }}" class="text-slate-400 transition-colors hover:text-gov-gold-soft">{{ $item['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- Services --}}
            <div class="lg:col-span-3">
                <h4 class="text-sm font-bold text-white">الخدمات الرقمية</h4>
                <ul class="mt-4 space-y-2.5 text-sm">
                    @foreach(\App\Support\SiteData::services() as $svc)
                        <li><a href="#services" class="flex items-center gap-2 text-slate-400 transition-colors hover:text-gov-gold-soft">
                            <x-icon name="chevron-left" class="size-3.5 text-gov-gold-soft" /> {{ $svc['title'] }}
                        </a></li>
                    @endforeach
                </ul>
            </div>

            {{-- Contact --}}
            <div class="lg:col-span-3">
                <h4 class="text-sm font-bold text-white">معلومات التواصل</h4>
                <ul class="mt-4 space-y-3.5 text-sm">
                    <li class="flex items-start gap-3">
                        <x-icon name="pin" class="mt-0.5 size-4 shrink-0 text-gov-gold-soft" />
                        <span class="text-slate-400">{{ $org['address'] }}</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <x-icon name="phone" class="size-4 shrink-0 text-gov-gold-soft" />
                        <a href="tel:{{ $org['phone'] }}" dir="ltr" class="text-slate-400 transition hover:text-white">{{ $org['phone'] }}</a>
                    </li>
                    <li class="flex items-center gap-3">
                        <x-icon name="mail" class="size-4 shrink-0 text-gov-gold-soft" />
                        <a href="mailto:{{ $org['email'] }}" class="text-slate-400 transition hover:text-white">{{ $org['email'] }}</a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="border-t border-white/10">
            <div class="container-px flex flex-col items-center justify-between gap-4 py-6 text-xs text-slate-500 sm:flex-row">
                <p>© {{ date('Y') }} {{ $org['name'] }}. جميع الحقوق محفوظة.</p>
                <div class="flex flex-wrap items-center justify-center gap-x-5 gap-y-2">
                    <a href="#" class="transition hover:text-slate-300">سياسة الخصوصية</a>
                    <a href="#" class="transition hover:text-slate-300">شروط الاستخدام</a>
                    <a href="#" class="transition hover:text-slate-300">إمكانية الوصول</a>
                    <a href="#" class="transition hover:text-slate-300">خريطة الموقع</a>
                </div>
            </div>
        </div>
    </div>
</footer>
