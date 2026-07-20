@props(['heading' => true])
<section class="section" id="social">
    <div class="container">
        @if ($heading)
        <div class="text-center mb-5 reveal">
            <span class="eyebrow">تابعنا</span>
            <h2 class="section-title">وسائل التواصل الاجتماعي</h2>
            <p class="section-subtitle">ابقَ على اطّلاع بآخر نشاطات النقابة وفعالياتها عبر منصاتنا الرسمية.</p>
        </div>
        @endif

        <div class="row g-4">
            <div class="col-sm-6 col-lg-3 reveal">
                <div class="card social-card social-facebook hover-lift">
                    <i class="bi bi-facebook brand-ico"></i>
                    <h3>فيسبوك</h3>
                    <div class="count">٤٥٬٠٠٠</div>
                    <small>متابع</small>
                    <a href="#" class="btn btn-sm w-100">زيارة الصفحة</a>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 reveal delay-1">
                <div class="card social-card social-instagram hover-lift">
                    <i class="bi bi-instagram brand-ico"></i>
                    <h3>انستغرام</h3>
                    <div class="count">٣٢٬٠٠٠</div>
                    <small>متابع</small>
                    <a href="#" class="btn btn-sm w-100">زيارة الحساب</a>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 reveal delay-2">
                <div class="card social-card social-telegram hover-lift">
                    <i class="bi bi-telegram brand-ico"></i>
                    <h3>تلغرام</h3>
                    <div class="count">١٨٬٠٠٠</div>
                    <small>مشترك</small>
                    <a href="#" class="btn btn-sm w-100">انضم للقناة</a>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 reveal delay-3">
                <div class="card social-card social-youtube hover-lift">
                    <i class="bi bi-youtube brand-ico"></i>
                    <h3>يوتيوب</h3>
                    <div class="count">١٢٬٠٠٠</div>
                    <small>مشترك</small>
                    <a href="#" class="btn btn-sm w-100">زيارة القناة</a>
                </div>
            </div>
        </div>
    </div>
</section>
