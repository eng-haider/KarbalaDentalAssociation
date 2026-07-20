<x-layouts.site title="الضوابط والشروط | {{ config('app.name') }}">
    <x-site.page-header
        title="الضوابط والشروط"
        eyebrow="دليل المراجعين"
        subtitle="ضوابط المعاملات المهنية والشروط المطلوبة لكل معاملة." />

    <div class="page-body">
        <x-site.regulations :types="$types" :heading="false" />
    </div>
</x-layouts.site>
