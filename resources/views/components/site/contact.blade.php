@props(['heading' => true])
<section class="section bg-light-2" id="contact">
    <div class="container">
        @if ($heading)
        <div class="text-center mb-5 reveal">
            <span class="eyebrow">تواصل معنا</span>
            <h2 class="section-title">اتصل بالنقابة</h2>
            <p class="section-subtitle">نسعد بتواصلكم واستفساراتكم خلال أوقات الدوام الرسمي.</p>
        </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-5 reveal">
                <div class="card p-4 h-100">
                    <div class="contact-info-item">
                        <div class="ico"><i class="bi bi-geo-alt-fill"></i></div>
                        <div><h4>العنوان</h4><p>{{ setting('address', 'كربلاء المقدسة – حي الحسين') }}</p></div>
                    </div>
                    <div class="contact-info-item">
                        <div class="ico"><i class="bi bi-telephone-fill"></i></div>
                        <div><h4>الهاتف</h4><p dir="ltr">{{ setting('phone', '+964 780 123 4567') }}</p></div>
                    </div>
                    <div class="contact-info-item">
                        <div class="ico"><i class="bi bi-envelope-fill"></i></div>
                        <div><h4>البريد الإلكتروني</h4><p>{{ setting('email', 'info@karbala-dental.iq') }}</p></div>
                    </div>
                    <div class="contact-info-item">
                        <div class="ico"><i class="bi bi-clock-fill"></i></div>
                        <div><h4>أوقات الدوام</h4><p>الأحد – الخميس: ٩:٠٠ ص – ٣:٠٠ م</p></div>
                    </div>
                    <div class="map-embed mt-3">
                        <div class="map-placeholder">
                            <div>
                                <i class="bi bi-geo-alt-fill fs-1"></i>
                                <p class="mb-0 fw-bold mt-2">موقع النقابة على الخريطة</p>
                                <small class="text-muted-2">{{ setting('address', 'كربلاء المقدسة – حي الحسين') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 reveal delay-1">
                <div class="card p-4 p-md-5 h-100">
                    <h3 class="mb-4">أرسل رسالة</h3>
                    <form id="contactForm" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="cName" class="form-label">الاسم الكامل</label>
                                <input type="text" class="form-control" id="cName" name="name" placeholder="الاسم الثلاثي" required>
                                <div class="invalid-feedback">يرجى إدخال الاسم.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="cEmail" class="form-label">البريد الإلكتروني</label>
                                <input type="email" class="form-control" id="cEmail" name="email" placeholder="name@example.com" required>
                                <div class="invalid-feedback">يرجى إدخال بريد إلكتروني صحيح.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="cPhone" class="form-label">رقم الهاتف</label>
                                <input type="tel" class="form-control" id="cPhone" name="phone" placeholder="07XX XXX XXXX" dir="ltr">
                            </div>
                            <div class="col-md-6">
                                <label for="cSubject" class="form-label">الموضوع</label>
                                <input type="text" class="form-control" id="cSubject" name="subject" placeholder="موضوع الرسالة" required>
                                <div class="invalid-feedback">يرجى إدخال الموضوع.</div>
                            </div>
                            <div class="col-12">
                                <label for="cMessage" class="form-label">الرسالة</label>
                                <textarea class="form-control" id="cMessage" name="message" rows="5" placeholder="اكتب رسالتك هنا..." required></textarea>
                                <div class="invalid-feedback">يرجى كتابة الرسالة.</div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-gov btn-lg"><i class="bi bi-send"></i> إرسال الرسالة</button>
                                <div class="alert alert-success mt-3 mb-0 d-none" id="formSuccess" role="alert">
                                    <i class="bi bi-check-circle-fill"></i> تم إرسال رسالتك بنجاح، وسنقوم بالتواصل معك قريباً.
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
