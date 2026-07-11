@props(['eyebrow' => null, 'title', 'subtitle' => null, 'center' => false, 'action' => null])

<div class="flex flex-col gap-5 {{ $center ? 'items-center text-center' : 'sm:flex-row sm:items-end sm:justify-between' }}">
    <div class="{{ $center ? 'max-w-2xl' : 'max-w-2xl' }}">
        @if($eyebrow)
            <span class="eyebrow">
                <span class="size-2 bg-gov-gold"></span>
                {{ $eyebrow }}
            </span>
        @endif
        <h2 class="mt-4 text-2xl font-extrabold tracking-tight text-brand-900 sm:text-3xl lg:text-[2.1rem] text-balance">
            {{ $title }}
        </h2>
        @if($subtitle)
            <p class="mt-3 text-base leading-relaxed text-slate-500 text-pretty">{{ $subtitle }}</p>
        @endif
        <div class="mt-5 accent-bar {{ $center ? 'mx-auto' : '' }}"></div>
    </div>

    @if($action)
        <div class="shrink-0">{{ $action }}</div>
    @endif
</div>
