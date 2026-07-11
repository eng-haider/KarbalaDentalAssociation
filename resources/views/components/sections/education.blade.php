@props(['categories', 'playlists'])

@php($featured = collect($playlists)->firstWhere('featured', true) ?? $playlists[0])
@php($rest = collect($playlists)->where('featured', false)->values())

<section id="education" class="relative scroll-mt-24 bg-mist py-20 sm:py-24">
    <div class="container-px">
        <div x-data="reveal()" x-intersect.once="show()" class="reveal">
            <x-section-heading
                eyebrow="مركز التعليم المستمر"
                title="دورات وبرامج تعليمية معتمدة"
                subtitle="ارتقِ بمهاراتك المهنية عبر مكتبة متنامية من المحاضرات وقوائم التشغيل المتخصصة.">
                <x-slot:action>
                    <a href="#" class="btn-primary"><x-icon name="academic" class="size-4" /> تصفّح المكتبة</a>
                </x-slot:action>
            </x-section-heading>
        </div>

        {{-- ===== Category filter (scrollable on mobile) ===== --}}
        <div x-data="{ active: 'الكل' }" class="mt-10">
            <div class="no-scrollbar -mx-4 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:flex-wrap sm:px-0">
                @foreach($categories as $cat)
                    <button @click="active = '{{ $cat['label'] }}'"
                            :class="active === '{{ $cat['label'] }}' ? 'bg-brand-800 text-white ring-brand-800' : 'bg-white text-slate-600 ring-cloud hover:bg-brand-50 hover:text-brand-700'"
                            class="flex shrink-0 items-center gap-2 rounded-sm px-4 py-2 text-sm font-semibold ring-1 ring-inset transition-colors">
                        {{ $cat['label'] }}
                        <span :class="active === '{{ $cat['label'] }}' ? 'bg-white/20' : 'bg-slate-100'"
                              class="rounded-sm px-2 py-0.5 text-xs nums">{{ $cat['count'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="mt-10 grid gap-6 lg:grid-cols-12">
            {{-- ===== Featured lesson with video facade ===== --}}
            <div x-data="reveal()" x-intersect.once="show()" class="reveal lg:col-span-7">
                <div class="card overflow-hidden">
                    <div x-data="litePlayer('{{ $featured['youtube'] }}')" class="relative aspect-video bg-brand-900">
                        {{-- facade --}}
                        <template x-if="!playing">
                            <button @click="play()" class="group absolute inset-0 grid place-items-center" aria-label="تشغيل الفيديو">
                                <x-cover tone="ink" icon="academic" class="absolute inset-0 h-full w-full" />
                                <span class="absolute inset-0 bg-brand-900/40 transition-colors group-hover:bg-brand-900/25"></span>
                                <span class="relative grid size-18 place-items-center rounded-sm bg-white text-brand-800 ring-1 ring-white/40 transition-transform group-hover:scale-105">
                                    <x-icon name="play" class="size-8 translate-x-0.5 fill-current" />
                                </span>
                            </button>
                        </template>
                        {{-- real iframe loads only on click --}}
                        <template x-if="playing">
                            <iframe class="absolute inset-0 h-full w-full" :src="src"
                                    title="درس مميز" frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                        </template>
                    </div>

                    <div class="p-6">
                        <div class="flex items-center gap-2">
                            <span class="eyebrow"><x-icon name="star" class="size-3.5 fill-current text-amber-400" /> درس مميز</span>
                            <span class="rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-700">{{ $featured['level'] }}</span>
                        </div>
                        <h3 class="mt-4 text-xl font-extrabold text-brand-900">{{ $featured['title'] }}</h3>
                        <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-slate-500">
                            <span class="flex items-center gap-1.5"><x-icon name="list" class="size-4 text-brand-500" /> {{ $featured['lessons'] }} درساً</span>
                            <span class="flex items-center gap-1.5"><x-icon name="clock" class="size-4 text-brand-500" /> {{ $featured['duration'] }}</span>
                            <span class="flex items-center gap-1.5"><x-icon name="academic" class="size-4 text-brand-500" /> {{ $featured['category'] }}</span>
                        </div>
                        <a href="#" class="btn-primary mt-6 w-full sm:w-auto"><x-icon name="play" class="size-4" /> متابعة المشاهدة</a>
                    </div>
                </div>
            </div>

            {{-- ===== Playlist list ===== --}}
            <div class="lg:col-span-5">
                <div class="flex h-full flex-col gap-4">
                    @foreach($rest as $i => $pl)
                        <article x-data="reveal()" x-intersect.once="show()"
                                 class="reveal group card card-hover flex items-center gap-4 p-3"
                                 style="transition-delay: {{ $i * 80 }}ms">
                            <div class="relative shrink-0 overflow-hidden rounded-xl">
                                <x-cover :tone="['sky','brand','green'][$i % 3]" icon="play" class="aspect-square w-28" />
                                <span class="absolute bottom-1.5 right-1.5 rounded-sm bg-brand-900/75 px-1.5 py-0.5 text-[0.65rem] font-bold text-white">{{ $pl['duration'] }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-xs font-semibold text-sky-accent">{{ $pl['category'] }}</span>
                                <h4 class="mt-1 truncate font-bold text-brand-900 transition group-hover:text-brand-700">{{ $pl['title'] }}</h4>
                                <p class="mt-1 flex items-center gap-1.5 text-xs text-slate-400">
                                    <x-icon name="list" class="size-3.5" /> {{ $pl['lessons'] }} دروس
                                </p>
                                <a href="#" class="mt-2.5 inline-flex items-center gap-1.5 rounded-lg bg-brand-50 px-3 py-1.5 text-xs font-bold text-brand-700 transition group-hover:bg-brand-100">
                                    متابعة <x-icon name="arrow-left" class="size-3.5" />
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
