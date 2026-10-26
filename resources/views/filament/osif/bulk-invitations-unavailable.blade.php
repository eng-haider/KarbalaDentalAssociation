@php
    /** @var list<string> $reasons */
@endphp

<div style="display: grid; gap: .75rem;">
    <p style="margin: 0; font-weight: 600;">الإرسال التلقائي غير متاح لحسابك الآن:</p>
    <ul style="display: grid; gap: .5rem; margin: 0; padding-inline-start: 1.1rem; list-style: disc; font-size: .875rem; line-height: 1.8;">
        @foreach ($reasons as $reason)
            <li>{{ $reason }}</li>
        @endforeach
    </ul>
    <p style="margin: 0; color: var(--gray-500); font-size: .8rem; line-height: 1.7;">
        يُغيَّر الدور من «المستخدمون» في لوحة الإدارة، باختياره من قائمة الدور.
    </p>
</div>
