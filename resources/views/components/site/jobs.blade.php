@props(['items', 'heading' => true])

@if ($items->isNotEmpty())
    <section class="section jobs-section" id="jobs">
        <div class="container">
            @if ($heading)
                <div class="text-center mb-5 reveal">
                    <span class="eyebrow">فرص العمل</span>
                    <h2 class="section-title">فرص عمل لأطباء الأسنان</h2>
                    <p class="section-subtitle">شواغر معلنة من عيادات ومراكز طبية لأعضاء نقابة أطباء الأسنان في كربلاء المقدسة.</p>
                </div>
            @endif

            <div class="row g-4">
                @foreach ($items as $job)
                    <div class="col-md-6 col-lg-4 reveal">
                        <x-site.job-card :job="$job" />
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-5">
                <a href="{{ route('jobs.index') }}" class="btn btn-gov">
                    تصفّح جميع فرص العمل <i class="bi bi-arrow-left" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </section>
@endif
