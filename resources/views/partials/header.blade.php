@php($org = \App\Support\SiteData::org())
@php($nav = \App\Support\SiteData::nav())

<header x-data="{ mobile: false, searchOpen: false }" class="relative z-50">

    {{-- ===== Official gold rule ===== --}}
    <div class="h-1 bg-gov-gold"></div>

    {{-- ===== Top utility bar ===== --}}
    <div class="hidden bg-brand-900 text-white/75 lg:block">
        <div class="container-px flex h-9 items-center justify-between text-xs font-medium">
            <div class="flex items-center gap-5">
                <a href="tel:{{ $org['phone'] }}" class="flex items-center gap-1.5 transition-colors hover:text-white">
                    <x-icon name="phone" class="size-3.5" /> <span dir="ltr">{{ $org['phone'] }}</span>
                </a>
                <span class="h-3 w-px bg-white/15"></span>
                <a href="mailto:{{ $org['email'] }}" class="flex items-center gap-1.5 transition-colors hover:text-white">
                    <x-icon name="mail" class="size-3.5" /> {{ $org['email'] }}
                </a>
            </div>
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1.5"><x-icon name="clock" class="size-3.5" /> {{ $org['hours'][0]['day'] }}: {{ $org['hours'][0]['time'] }}</span>
                <span class="h-3 w-px bg-white/15"></span>
                <div class="flex items-center gap-3">
                    @foreach($org['socials'] as $s)
                        <a href="{{ $s['url'] }}" aria-label="{{ $s['name'] }}" class="transition-colors hover:text-white">
                            <x-icon :name="$s['icon']" class="size-3.5" />
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Brand row (official masthead); pins on mobile where the nav is hidden ===== --}}
    <div class="sticky top-0 z-40 border-b border-cloud bg-white xl:static">
        <div class="container-px flex h-20 items-center justify-between gap-4">

            {{-- Emblem + official name --}}
            <a href="#hero" class="flex items-center gap-3.5 shrink-0">
                <x-logo class="size-12" />
                <span class="flex flex-col leading-tight">
                    <span class="text-[0.7rem] font-semibold tracking-wide text-gov-gold">جمهورية العراق</span>
                    <span class="text-base font-extrabold text-brand-900 sm:text-[1.05rem]">نقابة أطباء الأسنان</span>
                    <span class="text-xs font-semibold text-brand-600">فرع كربلاء المقدسة</span>
                </span>
            </a>

            {{-- Actions --}}
            <div class="flex items-center gap-2">
                <button @click="searchOpen = true; $nextTick(() => $refs.searchInput.focus())"
                        class="grid size-10 place-items-center rounded-sm text-brand-700 ring-1 ring-cloud
                               transition-colors hover:bg-brand-50"
                        aria-label="بحث">
                    <x-icon name="search" class="size-5" />
                </button>

                <a href="#services" class="btn-primary hidden md:inline-flex">
                    <x-icon name="verify" class="size-4" />
                    بوابة الأعضاء
                </a>

                <button @click="mobile = true"
                        class="grid size-10 place-items-center rounded-sm text-brand-800 ring-1 ring-cloud
                               transition-colors hover:bg-brand-50 xl:hidden"
                        aria-label="القائمة">
                    <x-icon name="menu" class="size-5" />
                </button>
            </div>
        </div>
    </div>

    {{-- ===== Primary navigation (sticky) ===== --}}
    <nav class="sticky top-0 z-40 hidden border-b border-brand-950 bg-brand-800 shadow-[var(--shadow-soft)] xl:block">
        <div class="container-px flex items-center">
            @foreach($nav as $item)
                <a href="{{ $item['href'] }}"
                   class="border-b-2 border-transparent px-4 py-3.5 text-sm font-semibold text-white/80 transition-colors
                          hover:border-gov-gold hover:bg-brand-900 hover:text-white">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </nav>

    {{-- ===== Search overlay ===== --}}
    <div x-show="searchOpen" x-transition.opacity
         @keydown.escape.window="searchOpen = false"
         class="fixed inset-0 z-50 bg-brand-900/50" style="display:none;"
         @click.self="searchOpen = false">
        <div x-show="searchOpen"
             x-transition:enter="transition duration-200" x-transition:enter-start="-translate-y-6 opacity-0"
             class="container-px pt-28">
            <div class="mx-auto max-w-2xl rounded-lg bg-white p-2 shadow-[var(--shadow-lift)] ring-1 ring-cloud">
                <div class="flex items-center gap-3 px-3">
                    <x-icon name="search" class="size-5 text-brand-400" />
                    <input x-ref="searchInput" type="search"
                           placeholder="ابحث عن خدمة، خبر، دورة تعليمية…"
                           class="h-14 w-full bg-transparent text-base text-brand-900 placeholder:text-brand-400 focus:outline-none">
                    <button @click="searchOpen = false" class="rounded-sm px-2 py-1 text-xs font-bold text-brand-500 ring-1 ring-cloud">ESC</button>
                </div>
                <div class="border-t border-cloud px-4 py-3">
                    <p class="text-xs font-semibold text-brand-500">روابط سريعة</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach(['تجديد العضوية', 'التحقق من طبيب', 'الدورات التعليمية', 'التعاميم', 'الفعاليات'] as $q)
                            <a href="#services" @click="searchOpen=false" class="btn-soft px-3 py-1.5 text-xs">{{ $q }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Mobile drawer ===== --}}
    <div x-show="mobile" x-transition.opacity class="fixed inset-0 z-50 bg-brand-900/60 xl:hidden"
         style="display:none;" @click.self="mobile = false">
        <div x-show="mobile"
             x-transition:enter="transition duration-200" x-transition:enter-start="translate-x-full"
             x-transition:leave="transition duration-200" x-transition:leave-end="translate-x-full"
             class="absolute inset-y-0 left-0 flex w-[88%] max-w-sm flex-col bg-white shadow-2xl">

            <div class="h-1 bg-gov-gold"></div>
            <div class="flex items-center justify-between border-b border-cloud px-5 py-4">
                <a href="#hero" @click="mobile=false" class="flex items-center gap-2.5">
                    <x-logo class="size-10" />
                    <span class="flex flex-col leading-tight">
                        <span class="text-sm font-extrabold text-brand-900">نقابة أطباء الأسنان</span>
                        <span class="text-xs font-semibold text-brand-600">فرع كربلاء المقدسة</span>
                    </span>
                </a>
                <button @click="mobile = false" class="grid size-9 place-items-center rounded-sm text-brand-700 ring-1 ring-cloud" aria-label="إغلاق">
                    <x-icon name="close" class="size-5" />
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto py-2">
                @foreach($nav as $i => $item)
                    <a href="{{ $item['href'] }}" @click="mobile = false"
                       class="flex items-center justify-between border-b border-cloud/70 px-5 py-3.5 text-sm font-semibold text-brand-800 transition-colors hover:bg-brand-50">
                        {{ $item['label'] }}
                        <x-icon name="chevron-left" class="size-4 text-brand-300" />
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-cloud p-4">
                <a href="#services" @click="mobile=false" class="btn-primary w-full">
                    <x-icon name="verify" class="size-4" /> بوابة الأعضاء
                </a>
                <div class="mt-4 flex items-center justify-center gap-3">
                    @foreach($org['socials'] as $s)
                        <a href="{{ $s['url'] }}" aria-label="{{ $s['name'] }}" class="grid size-9 place-items-center rounded-sm text-brand-500 ring-1 ring-cloud transition-colors hover:bg-brand-50 hover:text-brand-700">
                            <x-icon :name="$s['icon']" class="size-4" />
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</header>
