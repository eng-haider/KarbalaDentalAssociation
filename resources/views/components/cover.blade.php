@props(['tone' => 'brand', 'icon' => 'image', 'label' => null, 'class' => 'aspect-[16/10]'])

{{-- Generated cover art: flat institutional block + faint pattern + glyph.
     A clean, official placeholder so cards look finished without real photos.
     Swap the inner content for an <img> when real media is wired in. --}}
@php
    $tones = [
        'brand' => 'bg-brand-700',
        'sky'   => 'bg-brand-600',
        'green' => 'bg-green-accent',
        'amber' => 'bg-gov-gold',
        'ink'   => 'bg-brand-900',
    ];
    $bg = $tones[$tone] ?? $tones['brand'];
@endphp

<div class="relative overflow-hidden {{ $class }} {{ $bg }}">
    <div class="absolute inset-0 bg-dots opacity-20 mix-blend-overlay"></div>
    <div class="absolute inset-x-0 top-0 h-0.5 bg-gov-gold/60"></div>
    <div class="absolute inset-0 grid place-items-center">
        <x-icon :name="$icon" class="size-14 text-white/85" stroke-width="1.4" />
    </div>
    @if($label)
        <span class="absolute bottom-3 right-3 rounded-sm bg-brand-900/70 px-2.5 py-1 text-xs font-semibold text-white">{{ $label }}</span>
    @endif
    {{ $slot ?? '' }}
</div>
