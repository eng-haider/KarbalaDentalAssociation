@props(['heading' => true])
<section class="section bg-light-2" id="complaint">
    <div class="container">
        @if ($heading)
        <div class="text-center mb-5 reveal">
            <span class="eyebrow">صوت العضو</span>
            <h2 class="section-title">إرسال شكوى</h2>
            <p class="section-subtitle">اكتب شكواك أو ملاحظتك وسيتم عرضها على الجهة المختصة في النقابة.</p>
        </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-lg-8 reveal">
                <div class="card complaint-card">
                    <form id="complaintForm" method="POST" action="{{ route('complaints.store') }}" novalidate>
                        @csrf
                        <label for="complaintText" class="form-label complaint-label">
                            <i class="bi bi-chat-square-text" aria-hidden="true"></i> نص الشكوى
                        </label>
                        <textarea class="form-control complaint-textarea @error('body') is-invalid @enderror"
                                  id="complaintText" name="body" rows="7" required minlength="10"
                                  placeholder="اكتب تفاصيل الشكوى هنا...">{{ old('body') }}</textarea>
                        <div class="invalid-feedback">@error('body'){{ $message }}@else يرجى كتابة نص الشكوى (١٠ أحرف على الأقل).@enderror</div>

                        <div class="complaint-foot">
                            <span class="complaint-hint">
                                <i class="bi bi-shield-lock" aria-hidden="true"></i>
                                تُعامل الشكاوى بسرّية تامة.
                            </span>
                            <button type="submit" class="btn btn-gov btn-lg">
                                <i class="bi bi-send" aria-hidden="true"></i> إرسال الشكوى
                            </button>
                        </div>

                        <div class="alert alert-success @unless(session('complaint_ok')) d-none @endunless mt-3 mb-0" id="complaintSuccess" role="status">
                            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                            تم استلام شكواك. شكراً لك، سيتم عرضها على الجهة المختصة.
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
