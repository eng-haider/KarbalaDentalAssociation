@php
    /** @var \App\Models\EventRegistration $registration */
    $date = \App\Support\OsifInvitation::date($registration);
    $time = \App\Support\OsifInvitation::time($registration);
    $summary = collect([$event->title, $date])->filter()->implode(' · ');
    $guest = \App\Support\OsifInvitation::guestName($registration);
@endphp

<x-layouts.osif :title="'دعوة حضور — '.$guest" :description="$summary">
    <x-slot:head>
        <meta name="robots" content="noindex, nofollow">
        {{-- The shared ?share=2 URL lets WhatsApp fetch the new card preview. --}}
        <meta property="og:url" content="{{ request()->fullUrl() }}">
        <meta property="og:description" content="{{ $summary }}">
        @if ($ogImage)
            {{-- WhatsApp uses the actual QR invitation card, not a separate banner. --}}
            <meta property="og:image" content="{{ $ogImage['url'] }}">
            <meta property="og:image:type" content="{{ $ogImage['type'] }}">
            <meta property="og:image:width" content="{{ $ogImage['width'] }}">
            <meta property="og:image:height" content="{{ $ogImage['height'] }}">
            <meta property="og:image:alt" content="بطاقة دعوة {{ $guest }}">
            <meta name="twitter:card" content="summary_large_image">
        @endif
    </x-slot:head>

    <main id="main" class="osif-pass-page">
        <div class="osif-container osif-pass-grid">
            <section class="osif-pass-info" aria-labelledby="osif-pass-name">
                <p class="osif-pass-valid"><i class="bi bi-patch-check-fill" aria-hidden="true"></i> دعوة صالحة</p>
                <p class="osif-pass-kicker">دعوة حضور · {{ $registration->invitationCode() }}</p>
                <h1 id="osif-pass-name">{{ $guest }}</h1>
                @if ($registration->professional_title)
                    <p class="osif-pass-title">{{ $registration->professional_title }}</p>
                @endif

                <dl class="osif-pass-details">
                    <div><dt>الملتقى</dt><dd>{{ $event->title }}</dd></div>
                    @if ($date)
                        <div><dt>الموعد</dt><dd>{{ $date }} – {{ $time }}</dd></div>
                    @endif
                    @if ($event->location)
                        <div><dt>المكان</dt><dd>{{ $event->location }}</dd></div>
                    @endif
                </dl>

                <div class="osif-pass-actions">
                    @if ($pdfDownload)
                        <a class="osif-pass-button" href="{{ $pdfDownload }}">
                            <i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> تنزيل الدعوة (PDF)
                        </a>
                    @endif
                    @if ($cardDownload)
                        <a @class(['osif-pass-button', 'is-ghost' => $pdfDownload]) href="{{ $cardDownload }}">
                            <i class="bi bi-image" aria-hidden="true"></i> تنزيل كصورة
                        </a>
                    @endif
                    <a class="osif-pass-button is-ghost" href="{{ route('osif') }}">عن الملتقى</a>
                </div>
            </section>

            @if ($card)
                <figure class="osif-pass-card">
                    <img src="{{ $card }}" alt="بطاقة دعوة {{ $guest }}" width="1080" height="1350">
                </figure>
            @endif
        </div>
    </main>
</x-layouts.osif>
