@php($links = array_filter([
    ['url' => setting('facebook_url'),  'icon' => 'bi-facebook',  'label' => 'فيسبوك'],
    ['url' => setting('instagram_url'), 'icon' => 'bi-instagram', 'label' => 'انستغرام'],
    ['url' => setting('telegram_url'),  'icon' => 'bi-telegram',  'label' => 'تلغرام'],
    ['url' => setting('youtube_url'),   'icon' => 'bi-youtube',   'label' => 'يوتيوب'],
    ['url' => setting('whatsapp_url'),  'icon' => 'bi-whatsapp',  'label' => 'واتساب'],
], fn ($l) => filled($l['url'])))

@foreach ($links as $l)
    <a href="{{ $l['url'] }}" target="_blank" rel="noopener" aria-label="{{ $l['label'] }}"><i class="bi {{ $l['icon'] }}"></i></a>
@endforeach
