@php
    /**
     * @var string $templateName
     * @var string|null $body
     * @var bool $needsImage
     * @var string|null $problem
     * @var int $total
     * @var int $noNumber
     * @var int $duplicates
     * @var \App\Models\OsifBroadcast|null $latest
     * @var array{queue: list<array{name: string, url: string}>, sent: int, total: int}|null $resume
     */
@endphp

{{-- wire:ignore keeps the run going while the list behind the dialog refreshes. --}}
<div wire:ignore
     x-data="osifBroadcast(@js(['storeUrl' => $storeUrl, 'total' => $total, 'needsImage' => $needsImage, 'resume' => $resume['queue'] ?? []]))"
     class="osif-cast">
    <style>
        .osif-cast { display: grid; gap: 1rem; }
        .osif-cast__panel { display: grid; gap: .75rem; padding: 1rem; border: 1px solid var(--gray-200); border-radius: .75rem; }
        .osif-cast__panel strong { color: var(--gray-950); font-size: .95rem; }
        .osif-cast__panel p, .osif-cast__skips li { margin: 0; color: var(--gray-500); font-size: .8rem; line-height: 1.7; }
        .osif-cast__skips { display: grid; gap: .25rem; margin: 0; padding-inline-start: 1.1rem; list-style: disc; }
        .osif-cast__message { display: grid; gap: .5rem; max-width: 22rem; padding: .5rem; border-radius: .75rem; background: #e7fbe0; }
        .osif-cast__message img { width: 100%; max-height: 16rem; object-fit: cover; border-radius: .5rem; }
        .osif-cast__message pre { margin: 0; padding: 0 .25rem .25rem; white-space: pre-wrap; font: inherit; font-size: .82rem; line-height: 1.7; color: #111b21; }
        .osif-cast__photo { display: grid; place-items: center; min-height: 7rem; border: 2px dashed rgb(134 239 172); border-radius: .5rem; color: rgb(21 128 61); font-size: .85rem; font-weight: 600; cursor: pointer; text-align: center; padding: .75rem; }
        .osif-cast__change { justify-self: start; color: rgb(21 128 61); font-size: .8rem; font-weight: 600; cursor: pointer; }
        .osif-cast__bar { height: .5rem; overflow: hidden; border-radius: 999px; background: var(--gray-100); }
        .osif-cast__bar span { display: block; height: 100%; border-radius: inherit; background: rgb(22 163 74); transition: width .3s; }
        .osif-cast__count { color: var(--gray-700); font-size: .85rem; font-weight: 600; }
        .osif-cast__ok { color: rgb(22 163 74); font-size: .85rem; font-weight: 600; }
        .osif-cast__warn { margin: 0; padding: .75rem .875rem; border-radius: .75rem; color: rgb(146 64 14); background: rgb(254 243 199); font-size: .8rem; line-height: 1.7; }
        .osif-cast__countdown { display: grid; justify-items: center; gap: .5rem; padding: 1rem; border-radius: .75rem; text-align: center; background: rgb(254 242 242); }
        .osif-cast__countdown strong { color: rgb(153 27 27); font-size: 1.05rem; }
        .dark .osif-cast__countdown { background: rgb(127 29 29 / .3); }
        .dark .osif-cast__countdown strong { color: rgb(254 202 202); }
        .osif-cast__error { margin: 0; padding: .75rem .875rem; border-radius: .75rem; color: rgb(153 27 27); background: rgb(254 226 226); font-size: .8rem; line-height: 1.7; }
        .osif-cast__failures { display: grid; gap: .375rem; max-height: 12rem; overflow-y: auto; margin: 0; padding: 0; list-style: none; font-size: .8rem; }
        .osif-cast__failures li { color: var(--gray-600); }
        .osif-cast__failures b { color: rgb(220 38 38); font-weight: 600; }
        .dark .osif-cast__panel { border-color: var(--gray-700); }
        .dark .osif-cast__panel strong { color: #fff; }
        .dark .osif-cast__bar { background: var(--gray-800); }
        .dark .osif-cast__count { color: var(--gray-200); }
        .dark .osif-cast__warn { color: rgb(253 230 138); background: rgb(120 53 15 / .4); }
        .dark .osif-cast__error { color: rgb(254 202 202); background: rgb(127 29 29 / .4); }
    </style>

    @if ($problem)
        <p class="osif-cast__error">{{ $problem }}</p>
    @else
        {{-- The message as WhatsApp shows it. --}}
        <div class="osif-cast__panel">
            <strong>الرسالة (قالب {{ $templateName }})</strong>
            <div class="osif-cast__message">
                @if ($needsImage)
                    <input type="file" accept="image/jpeg,image/png" x-ref="image" x-on:change="pick($event)" hidden>
                    <template x-if="preview">
                        <img x-bind:src="preview" alt="">
                    </template>
                    <label x-show="! preview" class="osif-cast__photo" x-on:click="$refs.image.click()">
                        اضغط هنا لاختيار الصورة التي تظهر أعلى الرسالة<br>(JPG أو PNG، حتى 5 ميغابايت)
                    </label>
                @endif
                <pre>{{ $body }}</pre>
            </div>
            @if ($needsImage)
                <span x-show="preview && status === 'idle'" x-cloak class="osif-cast__change" x-on:click="$refs.image.click()">تغيير الصورة</span>
            @endif
        </div>

        <div class="osif-cast__panel">
            <strong>ستُرسل إلى {{ $total }} رقم واتساب</strong>

            @if ($noNumber || $duplicates)
                <ul class="osif-cast__skips">
                    @if ($noNumber)
                        <li>{{ $noNumber }} مسجّل بلا رقم واتساب صالح.</li>
                    @endif
                    @if ($duplicates)
                        <li>{{ $duplicates }} تسجيل برقم مكرر، تصله الرسالة مرة واحدة.</li>
                    @endif
                </ul>
            @endif

            @if ($latest && ! $resume)
                <p class="osif-cast__warn">
                    سبق إرسال رسالة (قالب {{ $latest->template }}) إلى {{ $latest->sent_count }} رقم
                    في {{ $latest->created_at->timezone('Asia/Baghdad')->translatedFormat('j F، g:i A') }}.
                    «ابدأ الإرسال» يرسلها للجميع من جديد.
                </p>
            @endif

            @if ($resume)
                <p class="osif-cast__warn" x-show="status === 'idle'">
                    إرسال سابق لم يكتمل: وصل إلى {{ $resume['sent'] }} من {{ $resume['total'] }} رقم.
                    «متابعة الإرسال» يكمل الباقين ({{ count($resume['queue']) }}) دون تكرار.
                </p>
            @endif

            <div x-show="['running', 'done', 'stopped'].includes(status)" x-cloak style="display: grid; gap: .5rem;">
                <div class="osif-cast__bar"><span x-bind:style="`width: ${percent}%`"></span></div>
                <span class="osif-cast__count" x-text="`تمت معالجة ${done} من ${queue.length} · أُرسلت ${sent}` + (skipped ? ` · سبق إرسال ${skipped}` : '') + (failures.length ? ` · تعذّر ${failures.length}` : '')"></span>
            </div>

            {{-- The undo window: nothing has been uploaded or sent yet. --}}
            <div x-show="status === 'countdown'" x-cloak class="osif-cast__countdown">
                <strong x-text="`سيبدأ الإرسال إلى ${countdownFor} رقم بعد ${countdown} ثانية`"></strong>
                <p>لم يُرسل شيء بعد. اضغط «تراجع» لإلغاء الإرسال.</p>
                <x-filament::button color="danger" size="lg" icon="heroicon-m-arrow-uturn-right" x-on:click="undo()">
                    تراجع
                </x-filament::button>
            </div>

            <p x-show="status === 'preparing'" x-cloak>جارٍ رفع الصورة وتجهيز القائمة…</p>
            <p x-show="error" x-cloak class="osif-cast__error" x-text="error"></p>
            <p x-show="status === 'running'" x-cloak class="osif-cast__warn">أبقِ هذه النافذة مفتوحة حتى ينتهي الإرسال. يمكنك إيقافه في أي وقت بزر «إيقاف الإرسال»، فلا تُرسل الرسائل الباقية؛ أما الرسائل التي وصلت فلا يمكن سحبها من واتساب.</p>
            <span x-show="status === 'done'" x-cloak class="osif-cast__ok" x-text="`✓ اكتمل الإرسال: ${sent} رسالة`"></span>
            <p x-show="status === 'stopped'" x-cloak class="osif-cast__warn">
                توقف الإرسال<span x-show="stopReason" x-text="`: ${stopReason}`"></span>. يمكنك المتابعة الآن أو لاحقاً، ولن تُعاد الرسائل المرسلة.
            </p>

            <div style="display: flex; flex-wrap: wrap; gap: .5rem;">
                @if ($resume)
                    <x-filament::button x-show="status === 'idle'" color="success" icon="heroicon-m-play" x-on:click="carryOn()">
                        متابعة الإرسال
                    </x-filament::button>
                @endif
                @if ($total)
                    <x-filament::button x-show="status === 'idle'" :color="$resume ? 'gray' : 'success'" icon="heroicon-m-paper-airplane" x-on:click="start()">
                        {{ $resume ? 'إرسال جديد للجميع' : 'ابدأ الإرسال' }}
                    </x-filament::button>
                @endif
                <x-filament::button x-show="status === 'stopped'" x-cloak color="success" icon="heroicon-m-play" x-on:click="run()">
                    متابعة الإرسال
                </x-filament::button>
                <x-filament::button x-show="status === 'running'" x-cloak color="danger" icon="heroicon-m-pause" x-on:click="stop()">
                    إيقاف الإرسال
                </x-filament::button>
            </div>
        </div>

        <div x-show="failures.length" x-cloak class="osif-cast__panel">
            <strong>لم تُرسل</strong>
            <p>تبقى هذه الأرقام في الانتظار، فتُعاد محاولتها عند «متابعة الإرسال».</p>
            <ul class="osif-cast__failures">
                <template x-for="failure in failures">
                    <li><span x-text="failure.name"></span> — <b x-text="failure.message"></b></li>
                </template>
            </ul>
        </div>
    @endif
</div>
