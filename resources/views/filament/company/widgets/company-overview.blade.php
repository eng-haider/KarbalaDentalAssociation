<x-filament-widgets::widget>
    <style>
        .co-card { display: grid; gap: 1.25rem; }
        .co-head { display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; }
        .co-logo { display: grid; place-items: center; flex: 0 0 auto; width: 4rem; height: 4rem; overflow: hidden; border: 1px solid var(--gray-200); border-radius: 1rem; background: #fff; color: var(--primary-600); font-size: 1.25rem; font-weight: 800; }
        .co-logo img { width: 100%; height: 100%; object-fit: contain; padding: .35rem; }
        .co-title { flex: 1 1 14rem; min-width: 0; }
        .co-title h2 { margin: 0; color: var(--gray-950); font-size: 1.25rem; font-weight: 800; }
        .co-status { display: inline-flex; align-items: center; gap: .35rem; margin-top: .25rem; font-size: .8rem; font-weight: 600; }
        .co-status::before { content: ''; width: .5rem; height: .5rem; border-radius: 999px; background: currentColor; }
        .co-status.is-live { color: rgb(22 163 74); }
        .co-status.is-hidden { color: rgb(217 119 6); }
        .co-link { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem .75rem; padding: .75rem 1rem; border: 1px dashed var(--gray-300); border-radius: .75rem; background: var(--gray-50); }
        .co-link code { flex: 1 1 16rem; min-width: 0; overflow-wrap: anywhere; color: var(--gray-700); font-size: .85rem; direction: ltr; text-align: left; }
        .co-qr { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 1rem 1.25rem; align-items: center; padding: 1rem; border: 1px solid var(--gray-200); border-radius: .75rem; }
        .co-qr-code { width: 9rem; height: 9rem; padding: .35rem; border-radius: .5rem; background: #fff; }
        .co-qr-code svg { display: block; width: 100%; height: 100%; }
        .co-qr h3 { margin: 0; color: var(--gray-950); font-size: 1rem; font-weight: 800; }
        .co-qr p { margin: .25rem 0 .75rem; color: var(--gray-500); font-size: .85rem; line-height: 1.7; }
        .co-qr-actions { display: flex; flex-wrap: wrap; gap: .5rem; }
        .co-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .75rem; }
        .co-stat { padding: .875rem 1rem; border: 1px solid var(--gray-200); border-radius: .75rem; }
        .co-stat strong { display: block; color: var(--gray-950); font-size: 1.6rem; line-height: 1.2; }
        .co-stat span { color: var(--gray-500); font-size: .8rem; }
        .co-steps { display: grid; gap: .5rem; margin: 0; padding: 0; list-style: none; }
        .co-steps li { display: flex; align-items: center; gap: .75rem; font-size: .9rem; color: var(--gray-700); }
        .co-steps a { color: var(--primary-600); font-weight: 700; }
        .co-check { display: grid; place-items: center; flex: 0 0 auto; width: 1.5rem; height: 1.5rem; border-radius: 999px; background: var(--gray-100); color: var(--gray-500); font-size: .75rem; font-weight: 800; }
        .co-check.is-done { background: rgb(220 252 231); color: rgb(22 163 74); }
        .dark .co-logo, .dark .co-stat { border-color: var(--gray-700); }
        .dark .co-title h2, .dark .co-stat strong { color: #fff; }
        .dark .co-link { border-color: var(--gray-700); background: var(--gray-900); }
        .dark .co-link code, .dark .co-steps li { color: var(--gray-300); }
        .dark .co-qr { border-color: var(--gray-700); }
        .dark .co-qr h3 { color: #fff; }
        @media (max-width: 480px) { .co-stats { grid-template-columns: 1fr 1fr; } .co-qr { grid-template-columns: minmax(0, 1fr); justify-items: center; text-align: center; } .co-qr-actions { justify-content: center; } }
    </style>

    <x-filament::section>
        <div class="co-card" x-data="{ copied: false }">
            <div class="co-head">
                <div class="co-logo">
                    @if ($company->logoUrl())
                        <img src="{{ $company->logoUrl() }}" alt="">
                    @else
                        {{ $company->initials() }}
                    @endif
                </div>
                <div class="co-title">
                    <h2>{{ $company->name }}</h2>
                    @if ($company->is_active)
                        <span class="co-status is-live">صفحتك ظاهرة للأطباء</span>
                    @else
                        <span class="co-status is-hidden">صفحتك مخفية حالياً — تواصل مع منظمي الملتقى</span>
                    @endif
                </div>
                <x-filament::button tag="a" :href="$productsUrl" icon="heroicon-m-plus" size="lg">
                    إضافة منتج أو عرض
                </x-filament::button>
            </div>

            <div class="co-link">
                <code>{{ $pageUrl }}</code>
                <x-filament::button tag="a" :href="$pageUrl" target="_blank" color="gray" size="sm" icon="heroicon-m-arrow-top-right-on-square">
                    فتح صفحتي
                </x-filament::button>
                <x-filament::button color="gray" size="sm" icon="heroicon-m-clipboard-document"
                    x-on:click="navigator.clipboard.writeText(@js($pageUrl)).then(() => { copied = true; setTimeout(() => copied = false, 2000) })">
                    <span x-text="copied ? 'تم النسخ ✓' : 'نسخ الرابط'">نسخ الرابط</span>
                </x-filament::button>
            </div>

            <div class="co-qr">
                <div class="co-qr-code" role="img" aria-label="رمز QR لصفحة {{ $company->name }}">{!! $qr !!}</div>
                <div>
                    <h3>رمز QR لصفحتك</h3>
                    <p>اطبعه وضعه في جناحك أو على منشوراتك؛ يمسحه الطبيب بكاميرا هاتفه فتُفتح صفحتك ويطلب مباشرة.</p>
                    <div class="co-qr-actions">
                        <x-filament::button tag="a" :href="$posterUrl" target="_blank" icon="heroicon-m-printer">
                            طباعة ملصق للجناح
                        </x-filament::button>
                        <x-filament::button tag="a" :href="$qrDownloadUrl" color="gray" icon="heroicon-m-arrow-down-tray">
                            تنزيل رمز QR
                        </x-filament::button>
                    </div>
                </div>
            </div>

            <div class="co-stats">
                <div class="co-stat"><strong>{{ $shown }}</strong><span>منتج ظاهر</span></div>
                <div class="co-stat"><strong>{{ $offers }}</strong><span>عليها عرض</span></div>
                <div class="co-stat"><strong>{{ $hidden }}</strong><span>مخفي</span></div>
            </div>

            <ol class="co-steps">
                <li><span @class(['co-check', 'is-done' => $profileDone])>{{ $profileDone ? '✓' : '1' }}</span> <span>أكمل <a href="{{ $profileUrl }}">بيانات صفحتك</a>: الشعار، النبذة، ورقم واتساب الطلبات.</span></li>
                <li><span @class(['co-check', 'is-done' => $shown > 0])>{{ $shown > 0 ? '✓' : '2' }}</span> <span>أضف <a href="{{ $productsUrl }}">منتجاتك وعروضك</a> مع صورها وأسعارها.</span></li>
                <li><span class="co-check">3</span> <span>شارك رابط صفحتك؛ تصلك طلبات الأطباء على واتساب مباشرة.</span></li>
            </ol>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
