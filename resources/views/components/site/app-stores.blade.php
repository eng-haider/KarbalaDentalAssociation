{{-- Store links live in لوحة التحكم → إعدادات الموقع → تطبيق النقابة.
     A link that was cleared simply drops out instead of leading nowhere. --}}
@php($android = setting('app_android_url'))
@php($ios = setting('app_ios_url'))

@if ($android || $ios)
    <div class="apply-stores">
        @if ($android)
            <a class="store-btn" href="{{ $android }}" target="_blank" rel="noopener">
                <i class="bi bi-google-play" aria-hidden="true"></i>
                <span><small>احصل عليه من</small><strong>Google Play</strong></span>
            </a>
        @endif
        @if ($ios)
            <a class="store-btn" href="{{ $ios }}" target="_blank" rel="noopener">
                <i class="bi bi-apple" aria-hidden="true"></i>
                <span><small>حمّله من</small><strong>App Store</strong></span>
            </a>
        @endif
    </div>
@else
    <p class="apply-note mb-0"><i class="bi bi-hourglass-split" aria-hidden="true"></i> روابط التحميل ستُعلن قريباً.</p>
@endif
