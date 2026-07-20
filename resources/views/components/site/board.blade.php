@props(['members', 'heading' => true])

@if ($members->isNotEmpty())
    <section class="section" id="board">
        <div class="container">
            @if ($heading)
                <div class="text-center mb-5 reveal">
                    <span class="eyebrow">الهيكل الإداري</span>
                    <h2 class="section-title">مجلس النقابة</h2>
                    <p class="section-subtitle">أعضاء مجلس نقابة أطباء الأسنان – فرع كربلاء المقدسة.</p>
                </div>
            @endif

            <div class="row g-4 justify-content-center">
                @foreach ($members as $member)
                    <div class="col-6 col-md-4 col-lg-3 reveal @if($loop->index) delay-{{ min($loop->index, 3) }} @endif">
                        <div class="card member-card hover-lift">
                            <div class="member-photo">
                                <img src="{{ $member->photo ? Storage::url($member->photo) : asset('images/avatar.svg') }}"
                                     alt="{{ $member->name }}">
                            </div>
                            <div class="member-body">
                                <h3>{{ $member->name }}</h3>
                                <span class="member-role">{{ $member->position }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
