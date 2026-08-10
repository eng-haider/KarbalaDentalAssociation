{{-- Section order and visibility come from the dashboard (لوحة التحكم → أقسام
     الصفحة الرئيسية). Each case below matches a key in HomeSection::defaults(). --}}
<x-layouts.site>
    @foreach ($sections as $section)
        @switch($section->key)
            @case('hero')
                <x-site.hero :slides="$heroSlides" />
                @break
            @case('transaction-search')
                <x-site.transaction-search />
                @break
            @case('clinic-analytics')
                <x-site.clinic-analytics :analytics="$transactionAnalytics" />
                @break
            @case('featured-event')
                <x-site.featured-event :event="$featuredEvent" />
                @break
            @case('about')
                <x-site.about />
                @break
            @case('news')
                <x-site.news :items="$news" />
                @break
            @case('board')
                <x-site.board :members="$boardMembers" />
                @break
            @case('statistics')
                <x-site.statistics />
                @break
            @case('courses')
                <x-site.courses :items="$courses" />
                @break
            @case('regulations')
                <x-site.regulations :types="$regulationTypes" />
                @break
            @case('apply')
                <x-site.apply />
                @break
            @case('discounts')
                <x-site.discounts :items="$discounts" />
                @break
            @case('marketplace')
                <x-site.marketplace :items="$listings" />
                @break
            @case('partners')
                <x-site.partners />
                @break
            @case('social')
                <x-site.social />
                @break
            @case('complaint')
                <x-site.complaint />
                @break
            @case('contact')
                <x-site.contact />
                @break
        @endswitch
    @endforeach
</x-layouts.site>
