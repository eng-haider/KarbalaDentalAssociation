<x-layouts.site>
    <x-site.hero :slides="$heroSlides" />
    <x-site.transaction-search />
    <x-site.clinic-analytics :analytics="$transactionAnalytics" />
    <x-site.featured-event :event="$featuredEvent" />
    <x-site.about />
    <x-site.news :items="$news" />
    <x-site.board :members="$boardMembers" />
    <x-site.statistics />
    <x-site.courses :items="$courses" />
    <x-site.regulations :types="$regulationTypes" />
    <x-site.apply />
    <x-site.discounts :items="$discounts" />
    <x-site.marketplace :items="$listings" />
    <x-site.partners />
    <x-site.social />
    <x-site.complaint />
    <x-site.contact />
</x-layouts.site>
