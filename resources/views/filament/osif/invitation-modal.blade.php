@php
    /** @var \App\Models\EventRegistration $record */
    $payload = \App\Support\OsifInvitation::payload($record);
@endphp

<div x-data="osifInvitation(@js($payload))" class="osif-invite">
    <style>
        .osif-invite { display: grid; gap: 1.25rem; }
        @media (min-width: 640px) { .osif-invite { grid-template-columns: minmax(0, 15rem) minmax(0, 1fr); align-items: start; } }
        .osif-invite__preview { display: grid; place-items: center; justify-self: center; width: min(100%, 13rem); aspect-ratio: 4 / 5; overflow: hidden; border: 1px solid var(--gray-200); border-radius: .75rem; background: var(--gray-50); }
        @media (min-width: 640px) { .osif-invite__preview { width: 100%; } }
        .osif-invite__preview img { display: block; width: 100%; height: 100%; object-fit: contain; }
        .osif-invite__state { display: grid; justify-items: center; gap: .5rem; padding: 1rem; color: var(--gray-500); font-size: .875rem; text-align: center; }
        .osif-invite__steps { display: grid; gap: .75rem; margin: 0; padding: 0; list-style: none; }
        .osif-invite__step { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: .35rem .75rem; padding: .875rem; border: 1px solid var(--gray-200); border-radius: .75rem; }
        .osif-invite__num { display: grid; place-items: center; width: 1.75rem; height: 1.75rem; border-radius: 999px; color: #fff; background: var(--primary-600); font-size: .8rem; font-weight: 700; }
        .osif-invite__step strong { align-self: center; color: var(--gray-950); font-size: .9rem; }
        .osif-invite__step p { grid-column: 2; margin: 0; color: var(--gray-500); font-size: .8rem; line-height: 1.6; }
        .osif-invite__step > .fi-btn, .osif-invite__step > div { grid-column: 2; justify-self: start; }
        .osif-invite__meta { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem 1rem; color: var(--gray-500); font-size: .8rem; }
        .osif-invite__meta button { color: var(--primary-600); font-weight: 600; text-decoration: underline; text-underline-offset: 3px; }
        .osif-invite__notice { margin: 0; padding: .75rem .875rem; border-radius: .75rem; color: var(--gray-700); background: var(--gray-50); font-size: .8rem; }
        .dark .osif-invite__preview, .dark .osif-invite__step { border-color: var(--gray-700); }
        .dark .osif-invite__preview, .dark .osif-invite__notice { background: var(--gray-900); }
        .dark .osif-invite__step strong { color: #fff; }
        .dark .osif-invite__notice { color: var(--gray-300); }
    </style>

    <div class="osif-invite__preview">
        <img x-show="status === 'ready'" x-cloak x-bind:src="previewUrl" alt="بطاقة دعوة {{ $record->name }}">
        <div x-show="status === 'loading'" class="osif-invite__state">
            <x-filament::loading-indicator class="h-6 w-6" />
            <span>جارٍ تجهيز بطاقة الدعوة…</span>
        </div>
        <div x-show="status === 'error'" x-cloak class="osif-invite__state">
            <span>تعذّر تجهيز البطاقة. أغلق النافذة وحاول مرة أخرى.</span>
        </div>
    </div>

    <div class="space-y-3">
        @if ($payload['chatUrl'])
            {{-- Phones: open the chat first so it is at the top of WhatsApp's share list. --}}
            <ol class="osif-invite__steps" x-show="onPhone" x-cloak>
                <li class="osif-invite__step">
                    <span class="osif-invite__num">1</span>
                    <strong>افتح محادثة {{ $record->name }}</strong>
                    <p>تُفتح واتساب برسالة تأكيد جاهزة، أرسلها ثم عُد إلى هنا.</p>
                    <x-filament::button tag="a" :href="$payload['chatUrl']" target="_blank" color="success" icon="heroicon-m-chat-bubble-left-right">
                        فتح المحادثة
                    </x-filament::button>
                </li>
                <li class="osif-invite__step">
                    <span class="osif-invite__num">2</span>
                    <strong>أرسل صورة الدعوة</strong>
                    <p>اختر واتساب ثم المحادثة نفسها من أعلى القائمة.</p>
                    <x-filament::button type="button" icon="heroicon-m-share" x-on:click="share()" x-bind:disabled="status !== 'ready'">
                        مشاركة الصورة
                    </x-filament::button>
                </li>
            </ol>

            {{-- Computers: copy the card, then paste it into the open chat. --}}
            <ol class="osif-invite__steps" x-show="! onPhone">
                <li class="osif-invite__step">
                    <span class="osif-invite__num">1</span>
                    <strong x-text="canCopy ? 'انسخ صورة الدعوة' : 'نزّل صورة الدعوة'">انسخ صورة الدعوة</strong>
                    <p x-show="canCopy">تُنسخ البطاقة لتلصقها مباشرة في المحادثة.</p>
                    <div x-show="canCopy">
                        <x-filament::button type="button" icon="heroicon-m-clipboard-document" x-on:click="copy()" x-bind:disabled="status !== 'ready'">
                            <span x-text="copied ? 'تم النسخ ✓' : 'نسخ الصورة'">نسخ الصورة</span>
                        </x-filament::button>
                    </div>
                    <div x-show="! canCopy" x-cloak>
                        <x-filament::button type="button" icon="heroicon-m-arrow-down-tray" x-on:click="download()" x-bind:disabled="status !== 'ready'">
                            تنزيل الصورة
                        </x-filament::button>
                    </div>
                </li>
                <li class="osif-invite__step">
                    <span class="osif-invite__num">2</span>
                    <strong>افتح المحادثة وأرسل الصورة</strong>
                    <p x-text="! canCopy ? 'أرفق الصورة التي نزّلتها في المحادثة ثم أرسل.' : (touch ? 'المس مطوّلاً حقل الكتابة واختر «لصق» ثم أرسل.' : 'الصق الصورة داخل المحادثة بـ Ctrl+V (أو ⌘V على ماك) ثم أرسل.')">الصق الصورة داخل المحادثة ثم أرسل.</p>
                    <x-filament::button tag="a" :href="$payload['chatUrl']" target="_blank" color="success" icon="heroicon-m-chat-bubble-left-right">
                        فتح واتساب
                    </x-filament::button>
                </li>
            </ol>
        @else
            <p class="osif-invite__notice">لا يوجد رقم صالح لهذا المسجّل، يمكنك تنزيل البطاقة وإرسالها يدوياً.</p>
        @endif

        <div class="osif-invite__meta">
            <span>{{ $payload['code'] }} · {{ $record->phone }}</span>
            <button type="button" x-on:click="download()" x-bind:disabled="status !== 'ready'">تنزيل الصورة</button>
        </div>

        @if ($record->invitation_sent_at)
            <p class="osif-invite__notice">أُرسلت الدعوة سابقاً في {{ $record->invitation_sent_at->translatedFormat('j F Y، g:i A') }}.</p>
        @endif
    </div>
</div>
