<x-layouts.site :title="$course->title . ' | ' . config('app.name')" :description="$course->description">
    <x-site.page-header title="{{ $course->title }}" eyebrow="دورة تدريبية" />

    <article class="article-page">
        <div class="container">
            <div class="row g-4 justify-content-center">
                <div class="col-lg-8">
                    @if ($course->image)
                        <img src="{{ Storage::url($course->image) }}" alt="{{ $course->title }}" class="article-hero">
                    @endif

                    @if ($category = $course->visibleCategory())
                        <p class="mb-3">
                            <a href="{{ route('courses.index', ['category' => $category->slug]) }}" class="badge-gov d-inline-flex align-items-center gap-2 text-decoration-none">
                                @if ($category->image)
                                    <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="cat-chip-img">
                                @else
                                    <i class="bi bi-bookmark"></i>
                                @endif
                                {{ $category->name }}
                            </a>
                        </p>
                    @endif

                    <div class="article-body">
                        {!! nl2br(e($course->description)) !!}
                    </div>

                    @if ($course->publishedLessons->isNotEmpty())
                        <section class="lesson-list">
                            <h2 class="lesson-list-title">
                                <i class="bi bi-collection-play"></i>
                                محتوى الدورة
                                <span>{{ $course->publishedLessons->count() }} درساً</span>
                            </h2>

                            @foreach ($course->publishedLessons as $lesson)
                                <a href="{{ $lesson->url }}" target="_blank" rel="noopener noreferrer" class="lesson-item">
                                    <span class="lesson-num">{{ $loop->iteration }}</span>

                                    @if ($lesson->image)
                                        <img src="{{ Storage::url($lesson->image) }}" alt="{{ $lesson->title }}" class="lesson-thumb">
                                    @endif

                                    <span class="lesson-info">
                                        <strong>{{ $lesson->title }}</strong>
                                        @if ($lesson->description)
                                            <small>{{ Str::limit($lesson->description, 90) }}</small>
                                        @endif
                                    </span>

                                    @if ($lesson->duration)
                                        <span class="lesson-duration"><i class="bi bi-clock"></i> {{ $lesson->duration }}</span>
                                    @endif

                                    <span class="lesson-play" aria-hidden="true"><i class="bi bi-play-fill"></i></span>
                                    <span class="visually-hidden">تشغيل الدرس</span>
                                </a>
                            @endforeach
                        </section>
                    @endif

                    <a href="{{ route('courses.index') }}" class="article-back">
                        <i class="bi bi-arrow-right"></i> العودة إلى الدورات
                    </a>
                </div>

                <div class="col-lg-4">
                    <div class="card p-4">
                        <h3 class="mb-3" style="font-size:1.1rem">تفاصيل الدورة</h3>
                        <ul class="course-meta">
                            @if ($category = $course->visibleCategory())
                                <li><i class="bi bi-bookmark"></i> التصنيف: {{ $category->name }}</li>
                            @endif
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
                            @if ($course->publishedLessons->isNotEmpty())
                                <li><i class="bi bi-play-btn"></i> {{ $course->publishedLessons->count() }} درساً</li>
                            @endif
                        </ul>

                        @if ($first = $course->publishedLessons->first())
                            <a href="{{ $first->url }}" target="_blank" rel="noopener noreferrer" class="btn btn-gov w-100 mt-4">
                                <i class="bi bi-play-fill"></i> ابدأ الدورة
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </article>
</x-layouts.site>
