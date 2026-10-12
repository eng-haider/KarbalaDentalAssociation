<x-layouts.site :title="$event->title . ' | ' . config('app.name')" :description="$event->description">
    <x-site.page-header title="{{ $event->title }}" eyebrow="فعالية" />

    {{-- Reuses the homepage banner component (countdown, calendar file); its
         «سجّل الآن» button leads to the registration section below. --}}
    <div class="page-body">
        <x-site.featured-event :event="$event" />
    </div>

    @unless ($event->isOsif())
        <x-site.event-registration :event="$event" />
    @endunless

    <a href="{{ route('events.index') }}" class="article-back container d-block mb-5">
        <i class="bi bi-arrow-right"></i> العودة إلى الفعاليات
    </a>
</x-layouts.site>
