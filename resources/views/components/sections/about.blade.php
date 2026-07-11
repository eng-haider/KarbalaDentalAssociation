@props(['about'])

<section id="about" class="container-px scroll-mt-24 py-20 sm:py-24">
    <div class="grid items-center gap-14 lg:grid-cols-2">
        {{-- ===== Visual ===== --}}
        <div x-data="reveal()" x-intersect.once="show()" class="reveal order-last lg:order-first">
            <div class="relative">
                <div class="overflow-hidden rounded-lg ring-1 ring-cloud">
                    <x-cover tone="brand" icon="building" class="aspect-[4/3]" />
                </div>
                {{-- accreditation card --}}
                <div class="absolute -bottom-6 right-6 max-w-[15rem] rounded-lg border-r-4 border-gov-gold bg-white p-5 shadow-[var(--shadow-lift)] ring-1 ring-cloud">
                    <div class="flex items-center gap-3">
                        <span class="grid size-11 place-items-center rounded-sm bg-emerald-50 text-emerald-700"><x-icon name="verify" class="size-6" /></span>
                        <div>
                            <p class="nums text-2xl font-black text-brand-900">٢٧</p>
                            <p class="text-xs text-slate-500">عاماً في خدمة المهنة</p>
                        </div>
                    </div>
                </div>
                {{-- logo badge --}}
                <div class="absolute -top-5 left-5 grid size-16 place-items-center rounded-lg bg-white shadow-[var(--shadow-lift)] ring-1 ring-cloud">
                    <x-logo class="size-10" />
                </div>
            </div>
        </div>

        {{-- ===== Copy + cards ===== --}}
        <div>
            <div x-data="reveal()" x-intersect.once="show()" class="reveal">
                <span class="eyebrow"><span class="size-2 bg-gov-gold"></span> عن النقابة</span>
                <h2 class="mt-4 text-2xl font-extrabold tracking-tight text-brand-900 sm:text-3xl lg:text-[2.1rem] text-balance">
                    مؤسسة مهنية رائدة في خدمة أطباء الأسنان
                </h2>
                <p class="mt-4 text-base leading-relaxed text-slate-500 text-pretty">
                    تأسست نقابة أطباء الأسنان – فرع كربلاء المقدسة لتكون البيت المهني الذي ينظّم المهنة،
                    ويصون حقوق أعضائها، ويواكب التطور الرقمي في تقديم الخدمات للأطباء والمجتمع.
                </p>
            </div>

            <div class="mt-8 space-y-4">
                @foreach($about as $i => $item)
                    <div x-data="reveal()" x-intersect.once="show()"
                         class="reveal group flex gap-4 rounded-2xl bg-white p-5 ring-1 ring-cloud/70 transition hover:shadow-[var(--shadow-card)] hover:ring-brand-200"
                         style="transition-delay: {{ $i * 80 }}ms">
                        <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 transition group-hover:bg-brand-700 group-hover:text-white">
                            <x-icon :name="$item['icon']" class="size-6" />
                        </span>
                        <div>
                            <h3 class="font-bold text-brand-900">{{ $item['title'] }}</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-500 text-pretty">{{ $item['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
