@props(['heading' => true])
<section class="stats section" id="statistics">
    <div class="container">
        @if ($heading)
        <div class="text-center mb-5 reveal">
            <span class="eyebrow" style="color:#fff">النقابة بالأرقام</span>
            <h2 class="section-title" style="color:#fff">إنجازاتٌ نفخر بها</h2>
        </div>
        @endif
        <div class="row g-4 justify-content-center">
            <div class="col-6 col-md-4 col-lg-2 col-divider reveal">
                <div class="stat-item">
                    <i class="bi bi-people-fill"></i>
                    <div class="stat-num" data-counter data-target="1250" data-suffix="+">0</div>
                    <div class="stat-label">الأعضاء المسجّلون</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 col-divider reveal delay-1">
                <div class="stat-item">
                    <i class="bi bi-hospital-fill"></i>
                    <div class="stat-num" data-counter data-target="480">0</div>
                    <div class="stat-label">العيادات المرخّصة</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 col-divider reveal delay-2">
                <div class="stat-item">
                    <i class="bi bi-mortarboard-fill"></i>
                    <div class="stat-num" data-counter data-target="96">0</div>
                    <div class="stat-label">الدورات التعليمية</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 col-divider reveal">
                <div class="stat-item">
                    <i class="bi bi-calendar2-event-fill"></i>
                    <div class="stat-num" data-counter data-target="145">0</div>
                    <div class="stat-label">الفعاليات</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 col-divider reveal delay-1">
                <div class="stat-item">
                    <i class="bi bi-laptop-fill"></i>
                    <div class="stat-num" data-counter data-target="24">0</div>
                    <div class="stat-label">الخدمات الإلكترونية</div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2 reveal delay-2">
                <div class="stat-item">
                    <i class="bi bi-heart-fill"></i>
                    <div class="stat-num" data-counter data-target="320" data-suffix="+">0</div>
                    <div class="stat-label">المتطوعون</div>
                </div>
            </div>
        </div>
    </div>
</section>
