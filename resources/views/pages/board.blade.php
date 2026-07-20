<x-layouts.site title="مجلس النقابة | {{ config('app.name') }}">
    <x-site.page-header
        title="مجلس النقابة"
        eyebrow="الهيكل الإداري"
        subtitle="أعضاء مجلس نقابة أطباء الأسنان – فرع كربلاء المقدسة." />

    <div class="page-body">
        <x-site.board :members="$members" :heading="false" />
    </div>
</x-layouts.site>
