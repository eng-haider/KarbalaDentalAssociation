@props(['events'])

@php
    $tone = [
        'brand' => 'bg-brand-800',
        'sky'   => 'bg-sky-accent',
        'green' => 'bg-green-accent',
    ];
    $chip = [
        'brand' => 'bg-brand-50 text-brand-700',
        'sky'   => 'bg-sky-50 text-sky-700',
        'green' => 'bg-emerald-50 text-emerald-700',
    ];
@endphp

<section id="events" class="relative scroll-mt-24 bg-mist py-20 sm:py-24">
    <div class="container-px">
        <div x-data="reveal()" x-intersect.once="show()" class="reveal">
            <x-section-heading
                eyebrow="الأجندة القادمة"
                title="الفعاليات والمؤتمرات القادمة"
                subtitle="سجّل حضورك مبكراً في الندوات وورش العمل والمؤتمرات المعتمدة.">
                <x-slot:action>
                    <a href="#" class="btn-soft"><x-icon name="calendar" class="size-4" /> التقويم الكامل</a>
                </x-slot:action>
            </x-section-heading>
        </div>

        <div class="relative mt-12">
            {{-- timeline spine (RTL: on the right) --}}
            <div class="absolute bottom-2 right-[1.4rem] top-2 hidden w-px bg-cloud sm:block"></div>

            <div class="space-y-4">
                @foreach($events as $i => $e)
                    <article x-data="reveal()" x-intersect.once="show()"
                             class="reveal group relative flex flex-col gap-4 sm:flex-row sm:items-stretch sm:pr-16"
                             style="transition-delay: {{ $i * 80 }}ms">
                        {{-- timeline node --}}
                        <span class="absolute right-3.5 top-7 hidden size-3.5 -translate-y-1/2 rounded-sm bg-white ring-2 ring-brand-300 sm:block group-hover:ring-gov-gold transition-colors"></span>

                        {{-- date chip --}}
                        <div class="flex shrink-0 items-center gap-4 sm:flex-col sm:items-stretch sm:gap-0">
                            <div class="grid w-20 place-items-center rounded-lg {{ $tone[$e['tone']] }} px-3 py-3 text-white ring-1 ring-black/5">
                                <span class="nums text-2xl font-black leading-none">{{ $e['day'] }}</span>
                                <span class="mt-1 text-xs font-bold">{{ $e['month'] }}</span>
                            </div>
                        </div>

                        {{-- card --}}
                        <div class="card card-hover flex flex-1 flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-sm px-2 py-0.5 text-xs font-bold {{ $chip[$e['tone']] }}">{{ $e['tag'] }}</span>
                                    @if($e['soon'])
                                        <span class="inline-flex items-center gap-1 rounded-sm bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-700 ring-1 ring-inset ring-amber-200">
                                            <span class="size-1.5 rounded-sm bg-amber-500"></span> قريباً
                                        </span>
                                    @endif
                                </div>
                                <h3 class="mt-2.5 text-lg font-bold text-brand-900 text-balance">{{ $e['title'] }}</h3>
                                <div class="mt-2 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-sm text-slate-500">
                                    <span class="flex items-center gap-1.5"><x-icon name="clock" class="size-4 text-brand-500" /> {{ $e['time'] }}</span>
                                    <span class="flex items-center gap-1.5"><x-icon name="pin" class="size-4 text-brand-500" /> {{ $e['place'] }}</span>
                                </div>
                            </div>
                            <a href="#" class="btn-primary shrink-0 self-start sm:self-center">
                                <x-icon name="check" class="size-4" /> سجّل الآن
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
