@props(['gallery', 'publications'])

<section id="media" class="container-px scroll-mt-24 py-20 sm:py-24"
         x-data="{ tab: 'gallery' }">
    <div x-data="reveal()" x-intersect.once="show()" class="reveal">
        <x-section-heading
            eyebrow="المركز الإعلامي"
            title="مكتبة الصور والفيديو والإصدارات"
            subtitle="استعرض أرشيف النقابة من الفعاليات والمطبوعات الرسمية.">
            <x-slot:action>
                {{-- segmented control --}}
                <div class="inline-flex rounded-sm bg-brand-50 p-1 ring-1 ring-cloud">
                    <button @click="tab='gallery'" :class="tab==='gallery' ? 'bg-brand-800 text-white' : 'text-slate-600'"
                            class="rounded-sm px-4 py-2 text-sm font-bold transition-colors">المعرض</button>
                    <button @click="tab='pubs'" :class="tab==='pubs' ? 'bg-brand-800 text-white' : 'text-slate-600'"
                            class="rounded-sm px-4 py-2 text-sm font-bold transition-colors">الإصدارات</button>
                </div>
            </x-slot:action>
        </x-section-heading>
    </div>

    {{-- ===== Gallery grid ===== --}}
    <div x-show="tab==='gallery'" x-transition class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($gallery as $i => $g)
            <a href="#" x-data="reveal()" x-intersect.once="show()"
               class="reveal group card card-hover relative overflow-hidden"
               style="transition-delay: {{ $i * 70 }}ms">
                <x-cover :tone="['brand','sky','green','ink'][$i % 4]"
                         :icon="$g['kind'] === 'video' ? 'video' : 'image'"
                         class="aspect-[4/5]" />
                {{-- overlay --}}
                <div class="absolute inset-0 bg-gradient-to-t from-brand-900/85 via-brand-900/20 to-transparent"></div>
                @if($g['kind'] === 'video')
                    <span class="absolute inset-0 grid place-items-center">
                        <span class="grid size-14 place-items-center rounded-sm bg-white text-brand-800 ring-1 ring-white/40 transition-transform group-hover:scale-105">
                            <x-icon name="play" class="size-6 translate-x-0.5 fill-current" />
                        </span>
                    </span>
                @endif
                <div class="absolute inset-x-0 bottom-0 p-4">
                    <span class="inline-flex items-center gap-1.5 rounded-sm bg-brand-900/70 px-2 py-0.5 text-xs font-semibold text-white ring-1 ring-inset ring-white/20">
                        <x-icon :name="$g['kind'] === 'video' ? 'video' : 'image'" class="size-3" />
                        {{ $g['kind'] === 'video' ? 'فيديو' : 'صور' }} · {{ $g['count'] }}
                    </span>
                    <h3 class="mt-2 font-bold text-white text-balance">{{ $g['title'] }}</h3>
                </div>
            </a>
        @endforeach
    </div>

    {{-- ===== Publications list ===== --}}
    <div x-show="tab==='pubs'" x-transition class="mt-12 grid gap-4 lg:grid-cols-3" style="display:none;">
        @foreach($publications as $i => $p)
            <a href="#" x-data="reveal()" x-intersect.once="show()"
               class="reveal group card card-hover flex items-center gap-4 p-5"
               style="transition-delay: {{ $i * 70 }}ms">
                <span class="grid size-14 shrink-0 place-items-center rounded-xl bg-rose-50 text-rose-600">
                    <x-icon name="file" class="size-7" />
                </span>
                <div class="min-w-0 flex-1">
                    <h3 class="truncate font-bold text-brand-900 transition group-hover:text-brand-700">{{ $p['title'] }}</h3>
                    <p class="mt-1 flex items-center gap-2 text-xs text-slate-400">
                        <span class="rounded bg-slate-100 px-1.5 py-0.5 font-bold text-slate-500">{{ $p['type'] }}</span>
                        {{ $p['size'] }} · {{ $p['date'] }}
                    </p>
                </div>
                <span class="grid size-10 shrink-0 place-items-center rounded-xl text-slate-400 ring-1 ring-cloud transition group-hover:bg-brand-700 group-hover:text-white group-hover:ring-brand-700">
                    <x-icon name="download" class="size-5" />
                </span>
            </a>
        @endforeach
    </div>
</section>
