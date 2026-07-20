<x-layouts.site title="إرسال شكوى | {{ config('app.name') }}">
    <x-site.page-header
        title="إرسال شكوى"
        eyebrow="صوت العضو"
        subtitle="اكتب شكواك أو ملاحظتك وسيتم عرضها على الجهة المختصة." />

    <div class="page-body">
        <x-site.complaint :heading="false" />
    </div>
</x-layouts.site>
