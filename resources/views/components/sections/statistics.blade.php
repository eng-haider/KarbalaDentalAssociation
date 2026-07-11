@props(['stats'])

<section class="relative overflow-hidden bg-brand-900 py-20 sm:py-24">
    {{-- background --}}
    <div class="pointer-events-none absolute inset-0 bg-grid opacity-50"></div>
    <div class="pointer-events-none absolute inset-x-0 top-0 h-0.5 bg-gov-gold/60"></div>

    <div class="container-px relative">
        <div x-data="reveal()" x-intersect.once="show()" class="reveal text-center">
            <span class="inline-flex items-center gap-2 rounded-sm border-r-2 border-gov-gold bg-white/10 px-3 py-1.5 text-sm font-bold text-white">
                <span class="size-2 bg-gov-gold"></span> أرقام تتحدث عنّا
            </span>
            <h2 class="mx-auto mt-4 max-w-2xl text-2xl font-extrabold tracking-tight text-white sm:text-3xl lg:text-[2.1rem] text-balance">
                إنجازات النقابة في أرقام
            </h2>
            <p class="mx-auto mt-3 max-w-xl text-base text-brand-100/80">مؤشرات تعكس حجم العمل والخدمة المقدّمة لأعضاء النقابة والمجتمع.</p>
        </div>

        <div class="mt-14 grid grid-cols-2 gap-px overflow-hidden rounded-lg bg-white/10 ring-1 ring-white/10 lg:grid-cols-4">
            @foreach($stats as $i => $stat)
                <div x-data="counter({{ $stat['value'] }})" x-intersect.once="start()"
                     class="bg-brand-800 p-6 text-center transition-colors hover:bg-brand-700"
                     style="transition-delay: {{ $i * 80 }}ms">
                    <span class="mx-auto grid size-14 place-items-center rounded-sm bg-white/10 text-gov-gold-soft ring-1 ring-inset ring-white/15">
                        <x-icon :name="$stat['icon']" class="size-7" />
                    </span>
                    <p class="mt-5 nums text-4xl font-black text-white sm:text-5xl">
                        <span x-text="formatted">0</span><span class="text-gov-gold-soft">{{ $stat['suffix'] }}</span>
                    </p>
                    <p class="mt-2 text-sm font-medium text-brand-100/80">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
