@props(['title', 'subtitle' => null, 'eyebrow' => null])

<section class="page-header">
    <div class="container">
        <nav aria-label="breadcrumb" class="page-crumbs">
            <a href="{{ url('/') }}"><i class="bi bi-house-door"></i> الرئيسية</a>
            <i class="bi bi-chevron-left" aria-hidden="true"></i>
            <span aria-current="page">{{ $title }}</span>
        </nav>
        @if ($eyebrow)
            <span class="page-eyebrow">{{ $eyebrow }}</span>
        @endif
        <h1 class="page-title">{{ $title }}</h1>
        @if ($subtitle)
            <p class="page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
</section>
