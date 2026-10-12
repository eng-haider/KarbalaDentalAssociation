@props(['event'])

@if ($event)
    <section class="featured-event" id="featured-event"
             data-event-date="{{ $event->starts_at->toIso8601String() }}"
             data-event-title="{{ $event->title }}"
             data-event-location="{{ $event->location }}"
             data-event-end="{{ ($event->ends_at ?? $event->starts_at)->toIso8601String() }}">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-lg-7 reveal">
                    <span class="fe-eyebrow">
                        <span class="fe-pulse" aria-hidden="true"></span> الفعالية القادمة
                    </span>

                    <h2 class="fe-title">{{ $event->title }}</h2>

                    @if ($event->description)
                        <p class="fe-desc">{{ $event->description }}</p>
                    @endif

                    <ul class="fe-meta">
                        <li><i class="bi bi-calendar-event" aria-hidden="true"></i> {{ $event->starts_at->translatedFormat('l j F Y') }}</li>
                        <li><i class="bi bi-clock" aria-hidden="true"></i> {{ $event->starts_at->translatedFormat('g:i A') }}</li>
                        @if ($event->location)
                            <li><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ $event->location }}</li>
                        @endif
                    </ul>

                    <div class="fe-actions">
                        @if ($event->registration_open)
                            {{-- A page, not a dialog: OSIF has its own form; other events register on their page. --}}
                            <a href="{{ $event->isOsif() ? route('osif.register') : route('events.show', $event).'#registration' }}" class="btn btn-gold btn-lg">
                                <i class="bi bi-pencil-square" aria-hidden="true"></i> سجّل الآن
                            </a>
                        @endif
                        <button type="button" class="btn btn-outline-white btn-lg" id="eventIcs">
                            <i class="bi bi-calendar-plus" aria-hidden="true"></i> أضف إلى التقويم
                        </button>
                    </div>
                </div>

                <div class="col-lg-5 reveal delay-1">
                    <div class="fe-countdown-card">
                        <span class="fe-cd-label">يبدأ خلال</span>
                        <div class="fe-countdown" id="eventCountdown" role="timer" aria-live="off">
                            <div class="fe-cd"><strong data-cd="days">--</strong><small>يوم</small></div>
                            <div class="fe-cd"><strong data-cd="hours">--</strong><small>ساعة</small></div>
                            <div class="fe-cd"><strong data-cd="minutes">--</strong><small>دقيقة</small></div>
                            <div class="fe-cd"><strong data-cd="seconds">--</strong><small>ثانية</small></div>
                        </div>
                        <p class="fe-cd-note" id="eventCdNote" hidden></p>
                        <div class="fe-seats">
                            <i class="bi bi-people-fill" aria-hidden="true"></i>
                            {{ $event->isOsif() ? 'التسجيل مجاني بالكامل' : 'التسجيل مفتوح لأعضاء النقابة المجدّدين' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
