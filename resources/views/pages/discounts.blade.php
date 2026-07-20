<x-layouts.site title="خصومات الأعضاء | {{ config('app.name') }}">
    <x-site.page-header
        title="خصومات الأعضاء"
        eyebrow="امتيازات العضوية"
        subtitle="امتيازات وخصومات حصرية لأعضاء النقابة المجدّدين." />

    <div class="page-body">
        <x-site.discounts :items="$items" :heading="false" />
    </div>
</x-layouts.site>
