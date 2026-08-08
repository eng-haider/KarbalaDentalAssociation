<x-layouts.site title="الدورات التدريبية | {{ config('app.name') }}">
    <x-site.page-header
        title="الدورات التدريبية"
        eyebrow="التعليم الطبي المستمر"
        subtitle="دورات وورش عمل معتمدة لرفع كفاءة أطباء الأسنان." />

    <section class="section">
        <div class="container">
            @if ($categories->isNotEmpty())
                <nav class="course-tabs reveal" aria-label="تصنيفات الدورات">
                    <a href="{{ route('courses.index') }}"
                       class="course-tab @if (! $activeCategory) active @endif"
                       @if (! $activeCategory) aria-current="page" @endif>
                        <i class="bi bi-grid"></i>
                        الكل
                        <span class="course-tab-count">{{ $totalCount }}</span>
                    </a>
                    @foreach ($categories as $category)
                        <a href="{{ route('courses.index', ['category' => $category->slug]) }}"
                           class="course-tab @if ($activeCategory?->is($category)) active @endif"
                           @if ($activeCategory?->is($category)) aria-current="page" @endif>
                            @if ($category->image)
                                <img src="{{ Storage::url($category->image) }}" alt="" class="course-tab-img">
                            @else
                                <i class="bi bi-bookmark"></i>
                            @endif
                            {{ $category->name }}
                            <span class="course-tab-count">{{ $category->courses_count }}</span>
                        </a>
                    @endforeach
                </nav>
            @endif

            @if ($items->isEmpty())
                <p class="text-center text-muted-2 py-5">
                    @if ($activeCategory)
                        لا توجد دورات منشورة ضمن تصنيف "{{ $activeCategory->name }}" حالياً.
                    @else
                        لا توجد دورات منشورة حالياً.
                    @endif
                </p>
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
                                    @if ($category = $course->visibleCategory())
                                        <span class="course-tag">{{ $category->name }}</span>
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
