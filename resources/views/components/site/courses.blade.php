@props(['items', 'heading' => true])

@if ($items->isNotEmpty())
    <section class="section" id="courses">
        <div class="container">
            @if ($heading)
            <div class="text-center mb-5 reveal">
                <span class="eyebrow">التعليم الطبي المستمر</span>
                <h2 class="section-title">الدورات التدريبية</h2>
                <p class="section-subtitle">دورات وورش عمل معتمدة لرفع كفاءة أطباء الأسنان.</p>
            </div>
            @endif

            <div class="row g-4">
                @foreach ($items as $course)
                    <div class="col-md-6 col-lg-4 reveal @if($loop->index) delay-{{ min($loop->index, 3) }} @endif">
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
                                <p>{{ $course->description }}</p>
                                <ul class="course-meta">
                                    @if ($course->instructor)
                                        <li><i class="bi bi-person-video3"></i> {{ $course->instructor }}</li>
                                    @endif
                                    @if ($course->starts_at)
                                        <li><i class="bi bi-calendar-event"></i> {{ $course->starts_at->translatedFormat('j F Y') }}</li>
                                    @endif
                                    @if ($course->duration)
                                        <li><i class="bi bi-clock"></i> {{ $course->duration }}</li>
                                    @endif
                                    @if ($course->seats)
                                        <li><i class="bi bi-people"></i> {{ $course->seats }} مقعداً</li>
                                    @endif
                                </ul>
                                <a href="{{ route('courses.show', $course) }}" class="service-link">التفاصيل <i class="bi bi-arrow-left"></i></a>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>

            @if ($heading)
                <div class="text-center mt-5 reveal">
                    <a href="{{ route('courses.index') }}" class="btn btn-outline-gov">
                        كل الدورات <i class="bi bi-arrow-left"></i>
                    </a>
                </div>
            @endif
        </div>
    </section>
@endif
