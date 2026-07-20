@props(['heading' => true])
<section class="section bg-light-2" id="apply">
    <div class="container">
        @if ($heading)
        <div class="text-center mb-5 reveal">
            <span class="eyebrow">التطبيق الرسمي</span>
            <h2 class="section-title">للتقديم على التجديد والانتماء</h2>
            <p class="section-subtitle">تُقدَّم طلبات التجديد والانتماء إلكترونياً عبر تطبيق النقابة الرسمي.</p>
        </div>
        @endif

        <div class="row g-4 align-items-center">
            
            <div class="col-lg-5 order-1 order-lg-2 reveal">
                <div class="apply-cta">
                    <span class="apply-cta-icon"><i class="bi bi-phone" aria-hidden="true"></i></span>
                    <h3>حمّل تطبيق النقابة</h3>
                    <p>التطبيق الرسمي المعتمد — متوفر لأجهزة أندرويد و iOS.</p>

                    <div class="apply-stores">
                        <a class="store-btn"
                           href="https://play.google.com/store/apps/details?id=dev.flutter.dental.dental_app&amp;pcampaignid=web_share"
                           target="_blank" rel="noopener">
                            <i class="bi bi-google-play" aria-hidden="true"></i>
                            <span><small>احصل عليه من</small><strong>Google Play</strong></span>
                        </a>
                        <a class="store-btn"
                           href="https://apps.apple.com/app/id6756843335"
                           target="_blank" rel="noopener">
                            <i class="bi bi-apple" aria-hidden="true"></i>
                            <span><small>حمّله من</small><strong>App Store</strong></span>
                        </a>
                    </div>

                    <p class="apply-note">
                        <i class="bi bi-shield-check" aria-hidden="true"></i>
                        احرص على تحميل النسخة الرسمية الصادرة عن النقابة حصراً.
                    </p>
                </div>
            </div>

            
            <div class="col-lg-7 order-2 order-lg-1 reveal">
                <ol class="apply-steps">
                    <li class="apply-step">
                        <span class="apply-num">٠١</span>
                        <div>
                            <h3>تحميل التطبيق الرسمي</h3>
                            <p>تثبيت النسخة المعتمدة لنقابة أطباء الأسنان عبر المتاجر الإلكترونية.</p>
                        </div>
                    </li>
                    <li class="apply-step">
                        <span class="apply-num">٠٢</span>
                        <div>
                            <h3>تسجيل البيانات</h3>
                            <p>إنشاء حساب شخصي وإدخال المعلومات المهنية المطلوبة بدقة عالية.</p>
                        </div>
                    </li>
                    <li class="apply-step">
                        <span class="apply-num">٠٣</span>
                        <div>
                            <h3>مراجعة وتدقيق</h3>
                            <p>تخضع البيانات المرسلة للتدقيق من قبل القسم المختص لتفعيل الحساب.</p>
                        </div>
                    </li>
                    <li class="apply-step">
                        <span class="apply-num">٠٤</span>
                        <div>
                            <h3>إنجاز المعاملات</h3>
                            <p>تقديم الطلبات ورفع المرفقات إلكترونياً ومتابعة حالة المعاملة.</p>
                        </div>
                    </li>
                </ol>
            </div>

        </div>
    </div>
</section>
