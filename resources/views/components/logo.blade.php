@props(['class' => 'w-12 h-12', 'mono' => false])

{{-- Crest-style emblem: a stylized tooth inside a shield — institutional yet
     specific to a dental association. Pure SVG, scales crisply at any size. --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 48 48" fill="none"
     role="img" aria-label="شعار نقابة أطباء الأسنان – كربلاء المقدسة">
    {{-- Shield (solid institutional navy with an official gold outline) --}}
    <path d="M24 3 7 9v13c0 11 7.6 18.4 17 23 9.4-4.6 17-12 17-23V9L24 3Z"
          fill="{{ $mono ? '#ffffff' : '#12264F' }}" opacity="{{ $mono ? '0.18' : '1' }}"/>
    <path d="M24 3 7 9v13c0 11 7.6 18.4 17 23 9.4-4.6 17-12 17-23V9L24 3Z"
          fill="none" stroke="{{ $mono ? '#ffffff' : '#B58A2E' }}" stroke-width="1.4"/>
    {{-- Tooth --}}
    <path d="M24 14c-3.4 0-5.2-2.2-8-2.2-3 0-4.7 2.5-4.7 6 0 2.6 1 4.7 1.8 7.6.6 2.2.7 5.4 2.3 5.4 1.7 0 1.5-3.2 2.4-5.4.6-1.5 1.3-2.4 2.2-2.4s1.6.9 2.2 2.4c.9 2.2.7 5.4 2.4 5.4 1.6 0 1.7-3.2 2.3-5.4.8-2.9 1.8-5 1.8-7.6 0-3.5-1.7-6-4.7-6-2.8 0-4.6 2.2-8 2.2Z"
          fill="#ffffff"/>
</svg>
