@php
    /** @var array<int, array{name: string, payload: string}> $queue */
    $total = count($queue);
@endphp

{{-- wire:ignore keeps the run going while the list behind the dialog refreshes. --}}
<div wire:ignore x-data="osifBulkInvitations(@js(['queue' => $queue]))" class="osif-bulk">
    <style>
        .osif-bulk { display: grid; gap: 1rem; }
        .osif-bulk__panel { display: grid; gap: .75rem; padding: 1rem; border: 1px solid var(--gray-200); border-radius: .75rem; }
        .osif-bulk__panel strong { color: var(--gray-950); font-size: .95rem; }
        .osif-bulk__panel p, .osif-bulk__skips li { margin: 0; color: var(--gray-500); font-size: .8rem; line-height: 1.7; }
        .osif-bulk__skips { display: grid; gap: .25rem; margin: 0; padding-inline-start: 1.1rem; list-style: disc; }
        .osif-bulk__bar { height: .5rem; overflow: hidden; border-radius: 999px; background: var(--gray-100); }
        .osif-bulk__bar span { display: block; height: 100%; border-radius: inherit; background: rgb(22 163 74); transition: width .3s; }
        .osif-bulk__count { color: var(--gray-700); font-size: .85rem; font-weight: 600; }
        .osif-bulk__ok { color: rgb(22 163 74); font-size: .85rem; font-weight: 600; }
        .osif-bulk__warn { margin: 0; padding: .75rem .875rem; border-radius: .75rem; color: rgb(146 64 14); background: rgb(254 243 199); font-size: .8rem; line-height: 1.7; }
        .osif-bulk__failures { display: grid; gap: .375rem; max-height: 12rem; overflow-y: auto; margin: 0; padding: 0; list-style: none; font-size: .8rem; }
        .osif-bulk__failures li { color: var(--gray-600); }
        .osif-bulk__failures b { color: rgb(220 38 38); font-weight: 600; }
        .dark .osif-bulk__panel { border-color: var(--gray-700); }
        .dark .osif-bulk__panel strong { color: #fff; }
        .dark .osif-bulk__bar { background: var(--gray-800); }
        .dark .osif-bulk__count { color: var(--gray-200); }
        .dark .osif-bulk__warn { color: rgb(253 230 138); background: rgb(120 53 15 / .4); }
    </style>

    <div class="osif-bulk__panel">
        @if ($total)
            <strong>ستُرسل الدعوة إلى {{ $total }} مسجّل لم تُرسل لهم بعد</strong>
        @else
            <strong>لا يوجد من ينتظر دعوة الحضور.</strong>
        @endif

        @if ($vip || $noNumber)
            <ul class="osif-bulk__skips">
                @if ($vip)
                    <li>{{ $vip }} بدعوة VIP، تُرسل من زر «إرسال الدعوة» لكل منهم.</li>
                @endif
                @if ($noNumber)
                    <li>{{ $noNumber }} بلا رقم واتساب صالح.</li>
                @endif
            </ul>
        @endif

        @if ($total)
            <div x-show="status !== 'idle'" x-cloak style="display: grid; gap: .5rem;">
                <div class="osif-bulk__bar"><span x-bind:style="`width: ${percent}%`"></span></div>
                <span class="osif-bulk__count" x-text="`تمت معالجة ${next} من ${queue.length} · أُرسلت ${sent}` + (skipped ? ` · سبق إرسال ${skipped}` : '') + (failures.length ? ` · تعذّر ${failures.length}` : '')"></span>
                <p x-show="current" x-text="`جارٍ إرسال دعوة ${current}…`"></p>
            </div>

            <p x-show="status === 'running'" x-cloak class="osif-bulk__warn">أبقِ هذه النافذة مفتوحة حتى ينتهي الإرسال؛ تُجهَّز بطاقة كل مدعو على هذا الجهاز قبل إرسالها.</p>
            <span x-show="status === 'done'" x-cloak class="osif-bulk__ok" x-text="`✓ اكتمل الإرسال: ${sent} دعوة`"></span>
            <p x-show="status === 'stopped'" x-cloak class="osif-bulk__warn">
                توقف الإرسال<span x-show="stopReason" x-text="`: ${stopReason}`"></span>. يمكنك المتابعة الآن أو لاحقاً، ولن تُعاد الدعوات المرسلة.
            </p>

            <div style="display: flex; flex-wrap: wrap; gap: .5rem;">
                <x-filament::button x-show="status === 'idle'" color="success" icon="heroicon-m-paper-airplane" x-on:click="start()">
                    ابدأ الإرسال
                </x-filament::button>
                <x-filament::button x-show="status === 'stopped'" x-cloak color="success" icon="heroicon-m-play" x-on:click="start()">
                    متابعة الإرسال
                </x-filament::button>
                <x-filament::button x-show="status === 'running'" x-cloak color="gray" icon="heroicon-m-pause" x-on:click="stop()">
                    إيقاف
                </x-filament::button>
            </div>
        @endif
    </div>

    <div x-show="failures.length" x-cloak class="osif-bulk__panel">
        <strong>لم تُرسل</strong>
        <p>يبقى هؤلاء في «بانتظار الدعوة»، فتُعاد محاولتهم عند الإرسال للجميع مرة أخرى.</p>
        <ul class="osif-bulk__failures">
            <template x-for="failure in failures">
                <li><span x-text="failure.name"></span> — <b x-text="failure.message"></b></li>
            </template>
        </ul>
    </div>
</div>
