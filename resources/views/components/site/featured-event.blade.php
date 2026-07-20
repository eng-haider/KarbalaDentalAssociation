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
                            <button type="button" class="btn btn-gold btn-lg" data-bs-toggle="modal" data-bs-target="#eventRegModal">
                                <i class="bi bi-pencil-square" aria-hidden="true"></i> سجّل الآن
                            </button>
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
                            التسجيل مفتوح لأعضاء النقابة المجدّدين
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($event->registration_open)
        <div class="modal fade" id="eventRegModal" tabindex="-1" aria-labelledby="eventRegTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content fe-modal">
                    <div class="modal-header">
                        <h5 class="modal-title" id="eventRegTitle">التسجيل في الفعالية</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <form id="eventRegForm" method="POST" action="{{ route('events.register', $event) }}" novalidate>
                        @csrf
                        <div class="modal-body">
                            <p class="fe-modal-event">
                                <i class="bi bi-calendar-event" aria-hidden="true"></i>
                                {{ $event->title }} — {{ $event->starts_at->translatedFormat('l j F Y') }}
                            </p>

                            <div class="mb-3">
                                <label class="form-label" for="regName">الاسم الثلاثي</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="regName" name="name" value="{{ old('name') }}" required minlength="5">
                                <div class="invalid-feedback">@error('name'){{ $message }}@else يرجى كتابة الاسم الثلاثي.@enderror</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="regPhone">رقم الهاتف</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                       id="regPhone" name="phone" value="{{ old('phone') }}" required
                                       pattern="[0-9+\s\-]{10,}" inputmode="tel" dir="ltr">
                                <div class="invalid-feedback">@error('phone'){{ $message }}@else يرجى كتابة رقم هاتف صحيح.@enderror</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="regNumber">رقم العضوية في النقابة</label>
                                <input type="text" class="form-control @error('membership_number') is-invalid @enderror"
                                       id="regNumber" name="membership_number" value="{{ old('membership_number') }}" required>
                                <div class="invalid-feedback">@error('membership_number'){{ $message }}@else يرجى كتابة رقم العضوية.@enderror</div>
                            </div>

                            <div class="alert alert-success @unless(session('registration_ok')) d-none @endunless mb-0" id="regSuccess" role="status">
                                <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                                تم استلام طلب التسجيل. سيتواصل معك موظف النقابة لتأكيد الحجز.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-gov" data-bs-dismiss="modal">إغلاق</button>
                            <button type="submit" class="btn btn-gov"><i class="bi bi-send"></i> إرسال الطلب</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($event->registration_open)
        @if ($errors->any() || session('registration_ok'))
            @push('scripts')
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        var el = document.getElementById('eventRegModal');
                        if (el && window.bootstrap) new bootstrap.Modal(el).show();
                    });
                </script>
            @endpush
        @endif
    @endif
@endif
