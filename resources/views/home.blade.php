@php($org = \App\Support\SiteData::org())

<x-layouts.app :title="$org['name']" :description="$org['tagline']">

    {{-- 1. Hero slider --}}
    <x-sections.hero :org="$org" :stats="$stats" :slides="$heroSlides" />

    {{-- 2. Latest News --}}
    <x-sections.news :news="$news" />

    {{-- 3. Announcements --}}
    <x-sections.announcements :announcements="$announcements" />

    {{-- 4. Educational Center --}}
    <x-sections.education :categories="$eduCategories" :playlists="$playlists" />

    {{-- 5. Digital Services --}}
    <x-sections.services :services="$services" />

    {{-- 6. Upcoming Events --}}
    <x-sections.events :events="$events" />

    {{-- 7. Media Center --}}
    <x-sections.media :gallery="$gallery" :publications="$publications" />

    {{-- 8. Statistics --}}
    <x-sections.statistics :stats="$stats" />

    {{-- 9. About --}}
    <x-sections.about :about="$about" />

    {{-- 10. Contact --}}
    <x-sections.contact :org="$org" />

</x-layouts.app>
