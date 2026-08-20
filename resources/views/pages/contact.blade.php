<x-layouts.site title="اتصل بنا | {{ config('app.name') }}">
    <x-site.page-header
        title="اتصل بنا"
        eyebrow="التواصل"
        subtitle="للتواصل مع نقابة أطباء الأسنان – فرع كربلاء المقدسة." />

    <div class="page-body">
        <x-site.contact :heading="false" :form="false" />
        <x-site.social :heading="false" />
    </div>
</x-layouts.site>
