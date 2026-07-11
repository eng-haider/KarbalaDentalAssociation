@props(['name' => 'circle', 'class' => 'w-6 h-6'])

{{-- Shared inline-SVG icon set. Stroke-based (Lucide-style) for a clean,
     institutional feel with zero external icon dependencies. --}}
@php
    $paths = [
        // UI / nav
        'menu'        => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'close'       => '<path d="M6 6l12 12M6 18L18 6"/>',
        'search'      => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
        'arrow-left'  => '<path d="M19 12H5M12 19l-7-7 7-7"/>',
        'arrow-right' => '<path d="M5 12h14M12 5l7 7-7 7"/>',
        'chevron-down'=> '<path d="m6 9 6 6 6-6"/>',
        'chevron-left'=> '<path d="m15 18-6-6 6-6"/>',
        'external'    => '<path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
        'play'        => '<polygon points="6 4 20 12 6 20 6 4"/>',
        'sun'         => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        'moon'        => '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>',
        'clock'       => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'pin'         => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        'phone'       => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8.1 9.8a16 16 0 0 0 6 6l1.3-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2Z"/>',
        'mail'        => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/>',
        'send'        => '<path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4Z"/>',
        'check'       => '<path d="M20 6 9 17l-5-5"/>',
        'star'        => '<polygon points="12 2 15 9 22 9.5 17 14.5 18.5 22 12 18 5.5 22 7 14.5 2 9.5 9 9"/>',
        // Stats / about
        'users'       => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9"/><path d="M16 3.1a4 4 0 0 1 0 7.8"/>',
        'building'    => '<rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4M9 6h.01M15 6h.01M9 10h.01M15 10h.01M9 14h.01M15 14h.01"/>',
        'academic'    => '<path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/>',
        'flag'        => '<path d="M4 22V4a1 1 0 0 1 .5-.9 4 4 0 0 1 4 .2 4 4 0 0 0 4 .2 4 4 0 0 1 4 .2 4 4 0 0 0 3 .6V14a4 4 0 0 1-3-.6 4 4 0 0 0-4-.2 4 4 0 0 1-4-.2 4 4 0 0 0-4-.2A1 1 0 0 1 4 14"/>',
        'target'      => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>',
        'eye'         => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'heart'       => '<path d="M19 14c1.5-1.5 3-3.3 3-5.5A4.5 4.5 0 0 0 12 5.5 4.5 4.5 0 0 0 2 8.5c0 2.2 1.5 4 3 5.5l7 7Z"/>',
        // Announcement types
        'document'    => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M9 13h6M9 17h6"/>',
        'ballot'      => '<rect x="3" y="4" width="18" height="14" rx="2"/><path d="m7 11 3 3 6-6M3 18h18"/>',
        'alert'       => '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01"/>',
        'calendar'    => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        // Services
        'verify'      => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>',
        'renew'       => '<path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/><path d="M3 21v-5h5"/>',
        'request'     => '<path d="M9 11H4a1 1 0 0 0-1 1v8h6Z"/><path d="M9 7h6M9 11h6a1 1 0 0 1 1 1v8H9Z"/><path d="M16 20h4a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1h-4"/>',
        'download'    => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5M12 15V3"/>',
        'certificate' => '<circle cx="12" cy="9" r="6"/><path d="m9 14-1 7 4-2 4 2-1-7"/>',
        // Media / publications
        'image'       => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-5-5L5 21"/>',
        'video'       => '<rect x="2" y="5" width="14" height="14" rx="2"/><path d="m22 8-6 4 6 4V8Z"/>',
        'file'        => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/>',
        'list'        => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
        'sparkle'     => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2 2M16 16l2 2M18 6l-2 2M8 16l-2 2"/>',
        // Socials
        'facebook'    => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3Z"/>',
        'instagram'   => '<rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/>',
        'telegram'    => '<path d="m22 3-20 8 6 2 2 6 4-5 5 4Z"/>',
        'youtube'     => '<rect x="2" y="5" width="20" height="14" rx="4"/><path d="m10 9 5 3-5 3Z"/>',
        'whatsapp'    => '<path d="M21 11.5a8.5 8.5 0 0 1-12.7 7.4L3 21l2.1-5.3A8.5 8.5 0 1 1 21 11.5Z"/>',
        'circle'      => '<circle cx="12" cy="12" r="9"/>',
    ];
    $d = $paths[$name] ?? $paths['circle'];
@endphp

<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none"
     stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
     aria-hidden="true" focusable="false">
    {!! $d !!}
</svg>
