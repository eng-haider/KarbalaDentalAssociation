<x-layouts.site :title="$course->title . ' | ' . config('app.name')" :description="$course->description">
    <x-site.page-header title="{{ $course->title }}" eyebrow="دورة تدريبية" />

    <article class="article-page">
        <div class="container">
            <div class="row g-4 justify-content-center">
                <div class="col-lg-8">
                    @if ($course->image)
                        <img src="{{ Storage::url($course->image) }}" alt="{{ $course->title }}" class="article-hero">
                    @endif
                    <div class="article-body">
                        {!! nl2br(e($course->description)) !!}
                    </div>
                    <a href="{{ route('courses.index') }}" class="article-back">
                        <i class="bi bi-arrow-right"></i> العودة إلى الدورات
                    </a>
                </div>

                <div class="col-lg-4">
                    <div class="card p-4">
                        <h3 class="mb-3" style="font-size:1.1rem">تفاصيل الدورة</h3>
                        <ul class="course-meta">
                            @if ($course->instructor)
                                <li><i class="bi bi-person-video3"></i> المحاضر: {{ $course->instructor }}</li>
                            @endif
                            @if ($course->starts_at)
                                <li><i class="bi bi-calendar-event"></i> {{ $course->starts_at->translatedFormat('l j F Y') }}</li>
                            @endif
                            @if ($course->duration)
                                <li><i class="bi bi-clock"></i> المدة: {{ $course->duration }}</li>
                            @endif
                            @if ($course->seats)
                                <li><i class="bi bi-people"></i> {{ $course->seats }} مقعداً</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </article>
</x-layouts.site>
