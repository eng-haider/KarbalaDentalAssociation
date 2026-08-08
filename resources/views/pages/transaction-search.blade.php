<x-layouts.site title="البحث عن معاملة | {{ config('app.name') }}">
    <x-site.page-header
        title="البحث عن معاملة"
        eyebrow="الخدمات الإلكترونية"
        subtitle="تحقّق من حالة معاملتك لدى النقابة بكتابة اسمك." />

    <div class="page-body">
        <x-site.transaction-search :heading="false" />
        <x-site.clinic-analytics :analytics="$analytics" />
    </div>
</x-layouts.site>
