@props(['job', 'detailed' => false])

@php($requirements = $detailed ? $job->requirementLines() : [])

<article class="card job-card hover-lift h-100 @if ($job->is_featured) job-card--featured @endif">
    @if ($job->is_featured)
        <span class="job-flag"><i class="bi bi-star-fill" aria-hidden="true"></i> فرصة مميزة</span>
    @endif

    <div class="job-head">
        <span class="job-logo">
            @if ($job->logo)
                <img src="{{ Storage::url($job->logo) }}" alt="{{ $job->employer }}" loading="lazy">
            @else
                <i class="bi {{ $job->typeIcon() }}" aria-hidden="true"></i>
            @endif
        </span>
        <span class="job-head-text">
            <h3 class="job-title">{{ $job->title }}</h3>
            <span class="job-employer"><i class="bi bi-building" aria-hidden="true"></i> {{ $job->employer }}</span>
        </span>
    </div>

    <div class="job-chips">
        <span class="job-chip job-chip--type"><i class="bi {{ $job->typeIcon() }}" aria-hidden="true"></i> {{ $job->typeLabel() }}</span>
        @if ($job->specialty)
            <span class="job-chip"><i class="bi bi-clipboard2-pulse" aria-hidden="true"></i> {{ $job->specialty }}</span>
        @endif
        @if ($job->city)
            <span class="job-chip"><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ $job->city }}</span>
        @endif
    </div>

    <div class="job-body">
        <p class="job-desc">{{ $detailed ? $job->description : Str::limit($job->description, 150) }}</p>

        @if ($detailed && $requirements)
            <div class="job-reqs">
                <h4>الشروط والمتطلبات</h4>
                <ul>
                    @foreach ($requirements as $line)
                        <li><i class="bi bi-check2" aria-hidden="true"></i> {{ $line }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <ul class="job-meta">
            <li><i class="bi bi-cash-coin" aria-hidden="true"></i> {{ $job->salaryLabel() }}</li>
            @if ($job->closes_at)
                <li class="@if ($job->isClosingSoon()) job-meta--urgent @endif">
                    <i class="bi bi-calendar-event" aria-hidden="true"></i>
                    آخر موعد للتقديم: {{ $job->closes_at->translatedFormat('j F Y') }}
                </li>
            @endif
            @if ($detailed && $job->contact_name)
                <li><i class="bi bi-person" aria-hidden="true"></i> {{ $job->contact_name }}</li>
            @endif
        </ul>
    </div>

    {{-- Whatever the employer left is what members can act on: link, phone, e-mail. --}}
    @if ($job->apply_link || $job->contact_phone || $job->contact_email)
        <div class="job-foot">
            @if ($job->apply_link)
                <a href="{{ $job->apply_link }}" target="_blank" rel="noopener" class="btn btn-sm btn-gov">
                    <i class="bi bi-box-arrow-up-left" aria-hidden="true"></i> قدّم الآن
                </a>
            @endif
            @if ($job->contact_phone)
                <a href="tel:{{ preg_replace('/\s+/', '', $job->contact_phone) }}" class="btn btn-sm btn-outline-gov">
                    <i class="bi bi-telephone" aria-hidden="true"></i>
                    <span dir="ltr">{{ $job->contact_phone }}</span>
                </a>
            @endif
            @if ($job->contact_email)
                <a href="mailto:{{ $job->contact_email }}" class="btn btn-sm btn-outline-gov">
                    <i class="bi bi-envelope" aria-hidden="true"></i> {{ $job->contact_email }}
                </a>
            @endif
        </div>
    @endif
</article>
