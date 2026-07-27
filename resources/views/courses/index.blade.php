<x-layouts.site title="الدورات التدريبية | {{ config('app.name') }}">
    <x-site.page-header
        title="الدورات التدريبية"
        eyebrow="التعليم الطبي المستمر"
        subtitle="دورات وورش عمل معتمدة لرفع كفاءة أطباء الأسنان." />

    <section class="section">
        <div class="container">
            @if ($categories->isNotEmpty())
                <div class="filter-bar cat-bar mb-5 reveal">
                    <a href="{{ route('courses.index') }}" class="filter-btn cat-chip @if (! $activeCategory) active @endif">
                        <i class="bi bi-grid"></i> جميع التصنيفات
                    </a>
                    @foreach ($categories as $category)
                        <a href="{{ route('courses.index', ['category' => $category->slug]) }}"
                           class="filter-btn cat-chip @if ($activeCategory?->is($category)) active @endif">
                            @if ($category->image)
                                <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="cat-chip-img">
                            @else
                                <i class="bi bi-bookmark"></i>
                            @endif
                            {{ $category->name }}
                            <span class="cat-chip-count">{{ $category->courses_count }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($items->isEmpty())
                <p class="text-center text-muted-2 py-5">لا توجد دورات منشورة حالياً.</p>
            @else
                <div class="row g-4">
                    @foreach ($items as $course)
                        <div class="col-md-6 col-lg-4 reveal">
                            <article class="card course-card hover-lift h-100">
                                <div class="course-thumb">
                                    @if ($course->image)
                                        <img src="{{ Storage::url($course->image) }}" alt="{{ $course->title }}" class="course-img">
                                    @else
                                        <div class="course-img course-img-placeholder">
                                            <i class="bi bi-mortarboard"></i>
                                        </div>
                                    @endif
                                    @if ($course->category)
                                        <span class="course-tag">{{ $course->category->name }}</span>
                                    @endif
                                    @if ($course->published_lessons_count)
                                        <span class="course-lessons-badge">
                                            <i class="bi bi-play-btn"></i> {{ $course->published_lessons_count }} درساً
                                        </span>
                                    @endif
                                </div>
                                <div class="course-body">
                                    <h3>{{ $course->title }}</h3>
                                    <p>{{ Str::limit($course->description, 120) }}</p>
                                    <ul class="course-meta">
                                        @if ($course->instructor)
                                            <li><i class="bi bi-person-video3"></i> {{ $course->instructor }}</li>
                                        @endif
                                        @if ($course->starts_at)
                                            <li><i class="bi bi-calendar-event"></i> {{ $course->starts_at->translatedFormat('j F Y') }}</li>
                                        @endif
                                    </ul>
                                    <a href="{{ route('courses.show', $course) }}" class="btn btn-sm btn-outline-gov mt-2">
                                        التفاصيل <i class="bi bi-arrow-left"></i>
                                    </a>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-center mt-5">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </section>
</x-layouts.site>
