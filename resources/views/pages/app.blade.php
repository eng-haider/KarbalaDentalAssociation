@php($features = [
    ['icon' => 'bi-search', 'title' => 'متابعة المعاملات', 'text' => 'تابع حالة معاملتك أولاً بأول من دون مراجعة مقر النقابة.'],
    ['icon' => 'bi-person-badge', 'title' => 'التجديد والانتماء', 'text' => 'قدّم طلب التجديد أو الانتماء وارفع مستمسكاتك إلكترونياً.'],
    ['icon' => 'bi-bell', 'title' => 'التعاميم والإشعارات', 'text' => 'إشعارات فورية بكل تعميم أو قرار رسمي يصدر عن النقابة.'],
    ['icon' => 'bi-mortarboard', 'title' => 'الدورات والفعاليات', 'text' => 'تصفّح الدورات التدريبية والفعاليات وسجّل حضورك مباشرة.'],
    ['icon' => 'bi-star', 'title' => 'خصومات الأعضاء', 'text' => 'اطّلع على الامتيازات والخصومات الحصرية للأعضاء المجدّدين.'],
    ['icon' => 'bi-chat-left-text', 'title' => 'الشكاوى والدعم', 'text' => 'أرسل شكواك أو استفسارك وتابع الرد عليه من داخل التطبيق.'],
])

<x-layouts.site title="تطبيق النقابة | {{ config('app.name') }}">
    <x-site.page-header
        title="تطبيق النقابة"
        eyebrow="التطبيق الرسمي"
        subtitle="التطبيق الرسمي لنقابة أطباء الأسنان – فرع كربلاء المقدسة، لإنجاز معاملاتك ومتابعتها من هاتفك." />

    <div class="page-body">
        <section class="section" id="app">
            <div class="container">
                <div class="row g-4 g-lg-5 align-items-center">
                    <div class="col-lg-5 order-1 order-lg-2 reveal">
                        <div class="apply-cta app-download">
                            <span class="apply-cta-icon"><i class="bi bi-phone" aria-hidden="true"></i></span>
                            <h3>حمّل التطبيق الآن</h3>
                            <p>متوفر مجاناً لأجهزة أندرويد و iOS.</p>

                            <x-site.app-stores />

                            <p class="apply-note">
                                <i class="bi bi-shield-check" aria-hidden="true"></i>
                                احرص على تحميل النسخة الرسمية الصادرة عن النقابة حصراً.
                            </p>
                        </div>
                    </div>

                    <div class="col-lg-7 order-2 order-lg-1 reveal">
                        <span class="eyebrow">عن التطبيق</span>
                        <h2 class="section-title mt-2">نقابتك في جيبك</h2>
                        <p class="app-intro">
                            {{ setting('app_intro', 'التطبيق الرسمي المعتمد من نقابة أطباء الأسنان – فرع كربلاء المقدسة، يتيح لك إنجاز معاملاتك ومتابعة حالتها ومواكبة تعاميم النقابة ودوراتها من هاتفك.') }}
                        </p>

                        <ul class="app-points">
                            <li><i class="bi bi-patch-check-fill" aria-hidden="true"></i> نسخة رسمية معتمدة من النقابة</li>
                            <li><i class="bi bi-translate" aria-hidden="true"></i> واجهة عربية بالكامل وسهلة الاستخدام</li>
                            <li><i class="bi bi-lock-fill" aria-hidden="true"></i> بياناتك محفوظة ومحمية</li>
                            <li><i class="bi bi-download" aria-hidden="true"></i> مجاني لجميع أعضاء النقابة</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <section class="section bg-light-2" id="app-features">
            <div class="container">
                <div class="text-center mb-5 reveal">
                    <span class="eyebrow">مزايا التطبيق</span>
                    <h2 class="section-title">ماذا يمكنك أن تنجز عبر التطبيق؟</h2>
                </div>

                <div class="row g-4">
                    @foreach ($features as $feature)
                        <div class="col-md-6 col-lg-4 reveal">
                            <div class="card service-card hover-lift">
                                <span class="service-icon"><i class="bi {{ $feature['icon'] }}" aria-hidden="true"></i></span>
                                <h3>{{ $feature['title'] }}</h3>
                                <p class="mb-0">{{ $feature['text'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section" id="app-steps">
            <div class="container">
                <div class="text-center mb-5 reveal">
                    <span class="eyebrow">خطوات البدء</span>
                    <h2 class="section-title">كيف تبدأ باستخدام التطبيق؟</h2>
                </div>

                <div class="row g-4 align-items-center">
                    <div class="col-lg-7 reveal">
                        <ol class="apply-steps">
                            <li class="apply-step">
                                <span class="apply-num">٠١</span>
                                <div>
                                    <h3>تحميل التطبيق</h3>
                                    <p>ثبّت النسخة الرسمية من Google Play أو App Store.</p>
                                </div>
                            </li>
                            <li class="apply-step">
                                <span class="apply-num">٠٢</span>
                                <div>
                                    <h3>إنشاء الحساب</h3>
                                    <p>سجّل بياناتك المهنية ورقم هاتفك لتفعيل الحساب.</p>
                                </div>
                            </li>
                            <li class="apply-step">
                                <span class="apply-num">٠٣</span>
                                <div>
                                    <h3>تقديم المعاملة</h3>
                                    <p>اختر نوع المعاملة وارفع المستمسكات المطلوبة إلكترونياً.</p>
                                </div>
                            </li>
                            <li class="apply-step">
                                <span class="apply-num">٠٤</span>
                                <div>
                                    <h3>متابعة الحالة</h3>
                                    <p>تابع مراحل التدقيق والإنجاز واستلم إشعاراً بالنتيجة.</p>
                                </div>
                            </li>
                        </ol>
                    </div>

                    <div class="col-lg-5 reveal">
                        <div class="card app-help">
                            <h3><i class="bi bi-question-circle" aria-hidden="true"></i> تحتاج مساعدة؟</h3>
                            <p class="text-muted-2">إن واجهت مشكلة في التحميل أو التسجيل، تواصل مع النقابة وسنساعدك.</p>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('contact') }}" class="btn btn-gov btn-sm">
                                    <i class="bi bi-envelope-paper" aria-hidden="true"></i> اتصل بنا
                                </a>
                                <a href="{{ route('apply') }}" class="btn btn-outline-gov btn-sm">
                                    <i class="bi bi-file-earmark-text" aria-hidden="true"></i> شروط التجديد والانتماء
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.site>
