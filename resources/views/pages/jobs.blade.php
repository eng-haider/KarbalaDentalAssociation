@php($types = \App\Models\JobOpening::types())
@php($icons = \App\Models\JobOpening::typeIcons())

<x-layouts.site title="فرص العمل | {{ config('app.name') }}">
    <x-site.page-header
        title="فرص العمل"
        eyebrow="بوابة التوظيف"
        subtitle="الشواغر المعلنة من العيادات والمراكز الطبية لأعضاء النقابة، مع تفاصيل التقديم وطريقة التواصل." />

    <div class="page-body">
        <section class="section" id="jobs">
            <div class="container">
                <nav class="course-tabs reveal" aria-label="تصنيفات فرص العمل">
                    <a href="{{ route('jobs.index') }}"
                       class="course-tab @if (! $activeType) active @endif"
                       @if (! $activeType) aria-current="page" @endif>
                        <i class="bi bi-grid" aria-hidden="true"></i>
                        الكل
                        <span class="course-tab-count">{{ $totalCount }}</span>
                    </a>
                    @foreach ($types as $value => $label)
                        <a href="{{ route('jobs.index', ['type' => $value]) }}"
                           class="course-tab @if ($activeType === $value) active @endif"
                           @if ($activeType === $value) aria-current="page" @endif>
                            <i class="bi {{ $icons[$value] }}" aria-hidden="true"></i>
                            {{ $label }}
                            <span class="course-tab-count">{{ $counts[$value] ?? 0 }}</span>
                        </a>
                    @endforeach
                </nav>

                @if ($items->isEmpty())
                    <div class="listing-empty text-center py-5">
                        <i class="bi bi-briefcase" aria-hidden="true"></i>
                        <p class="text-muted-2 mb-0">
                            @if ($activeType)
                                لا توجد فرص عمل ضمن "{{ $types[$activeType] }}" حالياً.
                            @else
                                لا توجد فرص عمل معلنة حالياً. تابع الصفحة ليصلك كل جديد.
                            @endif
                        </p>
                    </div>
                @else
                    <div class="row g-4">
                        @foreach ($items as $job)
                            <div class="col-md-6 reveal">
                                <x-site.job-card :job="$job" :detailed="true" />
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-center mt-5">
                        {{ $items->links() }}
                    </div>
                @endif

                <div class="jobs-cta card reveal">
                    <div>
                        <h3>لديك شاغر في عيادتك؟</h3>
                        <p class="mb-0 text-muted-2">راسل النقابة لنشر الشاغر في هذه الصفحة مجاناً لأعضاء النقابة.</p>
                    </div>
                    <a href="{{ route('contact') }}" class="btn btn-gov">
                        <i class="bi bi-envelope-paper" aria-hidden="true"></i> راسل النقابة
                    </a>
                </div>
            </div>
        </section>
    </div>
</x-layouts.site>
