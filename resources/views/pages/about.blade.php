<x-layouts.site title="عن النقابة | {{ config('app.name') }}">
    <x-site.page-header
        title="عن النقابة"
        eyebrow="التعريف"
        subtitle="مؤسسة مهنية رسمية في خدمة أطباء الأسنان في كربلاء المقدسة." />

    <div class="page-body">
        <x-site.about :heading="false" />
        <x-site.statistics :heading="false" />
    </div>
</x-layouts.site>
