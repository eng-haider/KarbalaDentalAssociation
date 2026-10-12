@php
    /** @var \App\Models\EventRegistration $registration */
    $guest = \App\Support\OsifInvitation::guestName($registration);
    $date = \App\Support\OsifInvitation::date($registration);
    $summary = collect([$event->title, $date])->filter()->implode(' · ');
@endphp

<x-layouts.osif :title="'دعوة حضور — '.$guest" :description="$summary">
    <x-slot:head>
        <meta name="robots" content="noindex, nofollow">
        <meta property="og:url" content="{{ request()->fullUrl() }}">
        <meta property="og:description" content="{{ $summary }}">
        <meta property="og:image" content="{{ $preview }}">
        <meta property="og:image:type" content="image/jpeg">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt" content="بطاقة دعوة {{ $guest }}">
        <meta name="twitter:card" content="summary_large_image">
    </x-slot:head>

    <main id="main" class="osif-pass-page">
        <div class="osif-container osif-card-page">
            <div class="osif-card-page-heading">
                <p class="osif-pass-kicker">بطاقة الدعوة</p>
                <h1>{{ $guest }}</h1>
                <p>{{ $event->title }}</p>
            </div>

            <figure class="osif-pass-card">
                <img src="{{ $card }}" alt="بطاقة دعوة {{ $guest }}" width="1080" height="1350">
            </figure>

            <div class="osif-pass-actions">
                @if ($cardDownload)
                    <a class="osif-pass-button" href="{{ $cardDownload }}">
                        <i class="bi bi-download" aria-hidden="true"></i> تنزيل الصورة
                    </a>
                @endif
                <a class="osif-pass-button is-ghost" href="{{ $registration->invitationUrl() }}">تفاصيل الدعوة</a>
            </div>
        </div>
    </main>
</x-layouts.osif>
