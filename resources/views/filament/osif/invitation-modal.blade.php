@php
    /** @var \App\Models\EventRegistration $record */
    $payload = \App\Support\OsifInvitation::payload($record);
@endphp

<div x-data="osifInvitation(@js($payload))" class="osif-invite">
    <style>
        .osif-invite { display: grid; gap: 1.25rem; }
        @media (min-width: 640px) { .osif-invite { grid-template-columns: minmax(0, 15rem) minmax(0, 1fr); align-items: start; } }
        .osif-invite__preview { display: grid; place-items: center; justify-self: center; width: min(100%, 13rem); aspect-ratio: 4 / 5; overflow: hidden; border: 1px solid var(--gray-200); border-radius: .75rem; background: #130a2d; }
        @media (min-width: 640px) { .osif-invite__preview { width: 100%; } }
        .osif-invite__preview img { display: block; width: 100%; height: 100%; object-fit: contain; }
        .osif-invite__state { display: grid; justify-items: center; gap: .5rem; padding: 1rem; color: #fff; font-size: .875rem; text-align: center; }
        .osif-invite__panel { display: grid; gap: .875rem; padding: 1rem; border: 1px solid var(--gray-200); border-radius: .75rem; }
        .osif-invite__panel strong { color: var(--gray-950); font-size: .95rem; }
        .osif-invite__panel p { margin: 0; color: var(--gray-500); font-size: .8rem; line-height: 1.7; }
        .osif-invite__status { display: flex; align-items: center; gap: .5rem; color: var(--gray-600); font-size: .8rem; font-weight: 600; }
        .osif-invite__status.is-ready { color: rgb(22 163 74); }
        .osif-invite__status.is-warning { color: rgb(217 119 6); }
        .osif-invite__steps { display: grid; gap: .75rem; margin: 0; padding: 0; list-style: none; }
        .osif-invite__step { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: .75rem; align-items: start; }
        .osif-invite__num { display: grid; place-items: center; width: 1.75rem; height: 1.75rem; border-radius: 999px; color: #fff; background: var(--primary-600); font-size: .8rem; font-weight: 700; }
        .osif-invite__step > div { display: grid; gap: .5rem; justify-items: start; }
        .osif-invite__alt { display: grid; gap: .5rem; justify-items: start; padding: .875rem 1rem; border: 1px dashed var(--gray-300); border-radius: .75rem; }
        .osif-invite__alt p { margin: 0; color: var(--gray-600); font-size: .8rem; line-height: 1.7; }
        .dark .osif-invite__alt { border-color: var(--gray-700); }
        .dark .osif-invite__alt p { color: var(--gray-400); }
        .osif-invite__links { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem 1rem; color: var(--gray-500); font-size: .8rem; }
        .osif-invite__links a, .osif-invite__links button { color: var(--primary-600); font-weight: 600; text-decoration: underline; text-underline-offset: 3px; }
        .osif-invite__notice { margin: 0; padding: .75rem .875rem; border-radius: .75rem; color: var(--gray-700); background: var(--gray-50); font-size: .8rem; }
        .osif-invite__type { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .5rem .75rem; }
        .osif-invite__type > strong { color: var(--gray-950); font-size: .9rem; }
        .osif-invite__type .fi-tabs-item:disabled { cursor: wait; opacity: .6; }
        .dark .osif-invite__type > strong { color: #fff; }
        .dark .osif-invite__preview, .dark .osif-invite__panel { border-color: var(--gray-700); }
        .dark .osif-invite__panel strong { color: #fff; }
        .dark .osif-invite__notice { color: var(--gray-300); background: var(--gray-900); }
    </style>

    <div class="osif-invite__preview">
        <img x-show="previewUrl" x-cloak x-bind:src="previewUrl" alt="بطاقة دعوة {{ $record->name }}">
        <div x-show="status === 'loading'" class="osif-invite__state">
            <x-filament::loading-indicator class="h-6 w-6" />
            <span>جارٍ تجهيز بطاقة الدعوة…</span>
        </div>
        <div x-show="status === 'error'" x-cloak class="osif-invite__state">
            <span>تعذّر تجهيز البطاقة. أغلق النافذة وحاول مرة أخرى.</span>
        </div>
    </div>

    <div style="display: grid; gap: .75rem;">
        {{-- Switching redraws the card and saves the type with it. --}}
        <div class="osif-invite__type">
            <strong>نوع الدعوة</strong>
            <x-filament::tabs contained label="نوع الدعوة">
                <x-filament::tabs.item alpine-active="! vip" icon="heroicon-m-envelope" x-on:click="setVip(false)" x-bind:disabled="busy">
                    عادية
                </x-filament::tabs.item>
                <x-filament::tabs.item alpine-active="vip" icon="heroicon-m-star" x-on:click="setVip(true)" x-bind:disabled="busy">
                    VIP
                </x-filament::tabs.item>
            </x-filament::tabs>
        </div>

        @if ($payload['chatUrl'])
            <div class="osif-invite__panel">
                <strong>إرسال رابط الدعوة إلى {{ $record->name }}</strong>
                <p>يفتح واتساب بالنص ورابط الدعوة، ثم يعرض البطاقة كصورة معاينة داخل الرسالة ويسجّل الإرسال تلقائياً.</p>
                <div class="osif-invite__status" x-bind:class="{ 'is-ready': status === 'ready' && ! uploadFailed, 'is-warning': uploadFailed }">
                    <span x-show="status === 'loading' || status === 'uploading'">جارٍ تجهيز معاينة البطاقة…</span>
                    <span x-show="status === 'ready' && ! uploadFailed" x-cloak x-text="vip ? '✓ بطاقة VIP جاهزة للمعاينة في واتساب' : '✓ البطاقة جاهزة للمعاينة في واتساب'"></span>
                    <span x-show="uploadFailed" x-cloak>تعذّر رفع البطاقة؛ أعد فتح النافذة وحاول مرة أخرى.</span>
                </div>
                <div x-bind:style="status === 'ready' && ! uploadFailed ? '' : 'pointer-events: none; opacity: .5'">
                    <x-filament::button tag="a" :href="$payload['chatUrl']" x-bind:href="chatUrl" target="_blank" color="success" icon="heroicon-m-link" x-on:click.once="$wire.callMountedAction()">
                        إرسال الرابط عبر واتساب
                    </x-filament::button>
                </div>
            </div>
        @else
            <p class="osif-invite__notice">لا يوجد رقم صالح لهذا المسجّل، يمكنك نسخ رابط صفحة الدعوة وإرساله يدوياً.</p>
        @endif

        <div class="osif-invite__links">
            <span>{{ $payload['code'] }} · {{ $record->phone }}</span>
            <a href="{{ $payload['url'] }}" target="_blank">صفحة الدعوة</a>
            <button type="button" x-on:click="download()" x-bind:disabled="! previewUrl">تنزيل الصورة</button>
        </div>

        @if ($record->invitation_sent_at)
            <p class="osif-invite__notice">أُرسلت الدعوة سابقاً في {{ $record->invitation_sent_at->translatedFormat('j F Y، g:i A') }}.</p>
        @endif
    </div>
</div>
