@props(['event'])

<div class="osif-registration-card">
    @if (session('registration_ok'))
        <div class="osif-success" role="status">
            <i class="bi bi-check-circle-fill"></i>
            <div><strong>تم استلام طلبك بنجاح.</strong><span>سيصلك تأكيد التسجيل وبطاقة الدعوة على رقمك في واتساب.</span></div>
        </div>
    @endif

    @if ($event->registration_open)
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
                <label class="osif-field">
                    <span>الصفة المهنية أو الأكاديمية <b>*</b></span>
                    <select name="professional_title" required class="@error('professional_title') is-invalid @enderror">
                        <option value="" disabled @selected(blank(old('professional_title')))>اختر صفتك المهنية أو الأكاديمية</option>
                        @foreach ($event->professionalTitleOptions() as $title)
                            <option value="{{ $title }}" @selected(old('professional_title') === $title)>{{ $title }}</option>
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
