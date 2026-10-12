@props(['event'])

{{-- Registration for association events, on the event's own page (OSIF has
     its own registration page). EventRegistrationController redirects back
     to #registration, where the confirmation is shown. --}}
@if ($event->registration_open)
    <section class="section bg-light-2" id="registration">
        <div class="container">
            <div class="text-center mb-5 reveal">
                <span class="eyebrow">التسجيل</span>
                <h2 class="section-title">سجّل في الفعالية</h2>
                <p class="section-subtitle">{{ $event->title }} — {{ $event->starts_at->translatedFormat('l j F Y') }}</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-7 reveal">
                    <div class="card fe-form">
                        @if (session('registration_ok'))
                            <div class="alert alert-success" role="status">
                                <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                                تم استلام طلب التسجيل. سيتواصل معك موظف النقابة لتأكيد الحجز.
                            </div>
                        @endif

                        <form id="eventRegForm" method="POST" action="{{ route('events.register', $event) }}" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="regName">الاسم الثلاثي</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="regName" name="name" value="{{ old('name') }}" required minlength="5" autocomplete="name">
                                <div class="invalid-feedback">@error('name'){{ $message }}@else يرجى كتابة الاسم الثلاثي.@enderror</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="regPhone">رقم الهاتف</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                       id="regPhone" name="phone" value="{{ old('phone') }}" required
                                       pattern="[0-9+\s\-]{10,}" inputmode="tel" autocomplete="tel" dir="ltr">
                                <div class="invalid-feedback">@error('phone'){{ $message }}@else يرجى كتابة رقم هاتف صحيح.@enderror</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="regNumber">رقم العضوية في النقابة</label>
                                <input type="text" class="form-control @error('membership_number') is-invalid @enderror"
                                       id="regNumber" name="membership_number" value="{{ old('membership_number') }}" required>
                                <div class="invalid-feedback">@error('membership_number'){{ $message }}@else يرجى كتابة رقم العضوية.@enderror</div>
                            </div>

                            <div class="fe-form-foot">
                                <button type="submit" class="btn btn-gov"><i class="bi bi-send" aria-hidden="true"></i> إرسال الطلب</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
