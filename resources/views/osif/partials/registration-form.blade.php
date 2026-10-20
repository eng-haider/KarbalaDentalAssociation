@props(['event'])

@php
    $titles = $event->professionalTitleOptions();
    $fullTitles = $event->fullProfessionalTitles();
@endphp

<div class="osif-registration-card" id="osif-form">
    @if (session('registration_ok'))
        <div class="osif-done" role="status">
            <span class="osif-done-icon" aria-hidden="true"><i class="bi bi-check-lg"></i></span>
            <h1>تم التسجيل بنجاح</h1>
            <p>سوف نقوم بمراجعة البيانات وإرسال دعوة الحضور إليك عبر واتساب أو البريد الإلكتروني.</p>
            <a href="{{ route('osif.register') }}#osif-form">تسجيل شخص آخر</a>
        </div>
    @elseif ($event->registration_open && $event->isFullyBooked())
        <div class="osif-closed">
            <i class="bi bi-people"></i>
            <h1>اكتمل العدد</h1>
            <p>نعتذر، اكتمل العدد المخصص للتسجيل في الملتقى. تابع صفحة OSIF على إنستغرام لمعرفة آخر التحديثات.</p>
        </div>
    @elseif ($event->registration_open)
        <div class="osif-form-head">
            <span>OSIF 2026</span>
            <h1>سجّل حضورك</h1>
            <p>الحقول المعلّمة بـ * مطلوبة.</p>
        </div>
        <form method="POST" action="{{ route('events.register', $event) }}" novalidate>
            @csrf
            <div class="osif-form-grid">
                <label class="osif-field osif-full">
                    <span>الاسم الثلاثي <b>*</b></span>
                    <input type="text" name="name" value="{{ old('name') }}" autocomplete="name" required minlength="5" class="@error('name') is-invalid @enderror" placeholder="اكتب اسمك الكامل">
                    @error('name')<small class="osif-error">{{ $message }}</small>@enderror
                </label>
                <label class="osif-field osif-full">
                    <span>رقم الموبايل <small>(رقم يحتوي على واتساب)</small> <b>*</b></span>
                    <input type="tel" name="phone" value="{{ old('phone') }}" dir="ltr" inputmode="tel" autocomplete="tel" required class="@error('phone') is-invalid @enderror" placeholder="07XXXXXXXXX">
                    @error('phone')<small class="osif-error">{{ $message }}</small>@enderror
                </label>
                <label class="osif-field osif-full">
                    <span>البريد الإلكتروني <b>*</b></span>
                    <input type="email" name="email" value="{{ old('email') }}" dir="ltr" inputmode="email" autocomplete="email" required class="@error('email') is-invalid @enderror" placeholder="name@example.com">
                    @error('email')<small class="osif-error">{{ $message }}</small>@enderror
                </label>
                <label class="osif-field">
                    <span>الصفة المهنية أو الأكاديمية <b>*</b></span>
                    <select name="professional_title" required class="@error('professional_title') is-invalid @enderror">
                        <option value="" disabled @selected(blank(old('professional_title')))>اختر صفتك المهنية أو الأكاديمية</option>
                        @foreach ($titles as $title)
                            @if (in_array($title, $fullTitles, true))
                                <option value="{{ $title }}" disabled>{{ $title }} — اكتمل العدد</option>
                            @else
                                <option value="{{ $title }}" @selected(old('professional_title') === $title)>{{ $title }}</option>
                            @endif
                        @endforeach
                    </select>
                    @error('professional_title')<small class="osif-error">{{ $message }}</small>@enderror
                </label>
                <fieldset class="osif-field osif-choice @error('owns_clinic') is-invalid @enderror">
                    <legend>هل تمتلك عيادة؟ <b>*</b></legend>
                    <div class="osif-choice-options">
                        <label><input type="radio" name="owns_clinic" value="1" required @checked(old('owns_clinic') === '1')><span>نعم</span></label>
                        <label><input type="radio" name="owns_clinic" value="0" @checked(old('owns_clinic') === '0')><span>لا</span></label>
                    </div>
                    @error('owns_clinic')<small class="osif-error">{{ $message }}</small>@enderror
                </fieldset>
            </div>
            <p class="osif-declaration">
                <i class="bi bi-shield-exclamation" aria-hidden="true"></i>
                <strong>يتحمل المتقدم صحة كافة البيانات، وبخلاف ذلك يتحمل المسؤولية القانونية.</strong>
            </p>
            <button type="submit" class="osif-button osif-button-primary osif-submit">إرسال طلب التسجيل <i class="bi bi-arrow-down-left"></i></button>
        </form>
    @else
        <div class="osif-closed">
            <i class="bi bi-lock"></i>
            <h1>التسجيل مغلق حالياً</h1>
            <p>تابع صفحة OSIF على إنستغرام لمعرفة آخر التحديثات.</p>
        </div>
    @endif
</div>
