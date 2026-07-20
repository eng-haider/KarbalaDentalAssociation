@props(['types', 'heading' => true])

@if ($types->isNotEmpty())
    <section class="section" id="regulations">
        <div class="container">
            @if ($heading)
            <div class="text-center mb-5 reveal">
                <span class="eyebrow">دليل المراجعين</span>
                <h2 class="section-title">الضوابط والشروط</h2>
                <p class="section-subtitle">ضوابط المعاملات المهنية — اختر نوع المعاملة لعرض الشروط المطلوبة.</p>
            </div>
            @endif

            <div class="row g-4" id="regRoot">
                <div class="col-lg-4">
                    <div class="card reg-nav">
                        <div class="reg-nav-head">
                            <i class="bi bi-list-check" aria-hidden="true"></i> أنواع المعاملات
                            <span class="reg-nav-hint d-lg-none">اضغط لعرض الشروط</span>
                        </div>
                        <div class="reg-items" id="regItems">
                            @foreach ($types as $type)
                                <div class="reg-item">
                                    <button class="reg-head" type="button" id="reg-head-{{ $loop->index }}"
                                            data-reg-target="reg-body-{{ $loop->index }}"
                                            aria-expanded="false" aria-controls="reg-body-{{ $loop->index }}">
                                        <span class="reg-ico"><i class="bi {{ $type->icon }}" aria-hidden="true"></i></span>
                                        <span class="reg-head-title">{{ $type->title }}</span>
                                        <span class="reg-count">{{ count($type->conditions ?? []) }}</span>
                                        <i class="bi bi-chevron-down reg-chev" aria-hidden="true"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-lg-8" id="regPanelsCol">
                    <div id="regPanels">
                        @foreach ($types as $type)
                            <div class="reg-body" id="reg-body-{{ $loop->index }}" role="region"
                                 aria-labelledby="reg-head-{{ $loop->index }}" hidden>
                                <div class="reg-panel">
                                    <div class="reg-panel-head">
                                        <span class="reg-panel-icon"><i class="bi {{ $type->icon }}" aria-hidden="true"></i></span>
                                        <div>
                                            <h3>{{ $type->title }}</h3>
                                            <small>{{ count($type->conditions ?? []) }} شرطاً مطلوباً</small>
                                        </div>
                                    </div>
                                    <div class="reg-panel-body">
                                        @if ($type->note)
                                            <div class="reg-note">
                                                <i class="bi bi-info-circle-fill" aria-hidden="true"></i>
                                                <p>{{ $type->note }}</p>
                                            </div>
                                        @endif
                                        @if ($type->preamble)
                                            <p class="reg-preamble">{{ $type->preamble }}</p>
                                        @endif
                                        <h4 class="reg-sub">الشروط المطلوبة</h4>
                                        <ol class="reg-list">
                                            @foreach ($type->conditions ?? [] as $condition)
                                                <li>{{ $condition }}</li>
                                            @endforeach
                                        </ol>
                                        <div class="reg-alert">
                                            <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                                            <p>تنبيه: يرجى التأكد من استيفاء كافة الشروط قبل طلب الكشف الميداني لتجنب رفض المعاملة.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
