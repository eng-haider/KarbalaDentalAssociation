@props(['heading' => true])
@php($platforms = array_values(array_filter([
    ['key' => 'facebook',  'url' => setting('facebook_url'),  'icon' => 'bi-facebook',  'name' => 'فيسبوك',   'count' => '٤٥٬٠٠٠', 'unit' => 'متابع', 'cta' => 'زيارة الصفحة'],
    ['key' => 'instagram', 'url' => setting('instagram_url'), 'icon' => 'bi-instagram', 'name' => 'انستغرام', 'count' => '٣٢٬٠٠٠', 'unit' => 'متابع', 'cta' => 'زيارة الحساب'],
    ['key' => 'telegram',  'url' => setting('telegram_url'),  'icon' => 'bi-telegram',  'name' => 'تلغرام',   'count' => '١٨٬٠٠٠', 'unit' => 'مشترك', 'cta' => 'انضم للقناة'],
    ['key' => 'youtube',   'url' => setting('youtube_url'),   'icon' => 'bi-youtube',   'name' => 'يوتيوب',   'count' => '١٢٬٠٠٠', 'unit' => 'مشترك', 'cta' => 'زيارة القناة'],
    ['key' => 'whatsapp',  'url' => setting('whatsapp_url'),  'icon' => 'bi-whatsapp',  'name' => 'واتساب',   'count' => null,     'unit' => null,    'cta' => 'تواصل معنا'],
], fn ($p) => filled($p['url']))))

@if (count($platforms))
<section class="section" id="social">
    <div class="container">
        @if ($heading)
        <div class="text-center mb-5 reveal">
            <span class="eyebrow">تابعنا</span>
            <h2 class="section-title">وسائل التواصل الاجتماعي</h2>
            <p class="section-subtitle">ابقَ على اطّلاع بآخر نشاطات النقابة وفعالياتها عبر منصاتنا الرسمية.</p>
        </div>
        @endif

        <div class="row g-4 justify-content-center">
            @foreach ($platforms as $i => $p)
            <div class="col-sm-6 col-lg-3 reveal {{ $i ? 'delay-'.min($i, 3) : '' }}">
                <a href="{{ $p['url'] }}" target="_blank" rel="noopener" aria-label="{{ $p['name'] }}"
                   class="card social-card social-{{ $p['key'] }} hover-lift text-decoration-none d-block">
                    <i class="bi {{ $p['icon'] }} brand-ico"></i>
                    <h3>{{ $p['name'] }}</h3>
                    @if ($p['count'])
                        <div class="count">{{ $p['count'] }}</div>
                        <small>{{ $p['unit'] }}</small>
                    @endif
                    <span class="btn btn-sm w-100">{{ $p['cta'] }}</span>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
