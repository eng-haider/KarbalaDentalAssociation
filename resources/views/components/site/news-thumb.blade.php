@props(['item'])

@if ($item->image || $item->hasVideo())
    <div class="news-thumb @unless($item->image) news-thumb--video @endunless">
        @if ($item->image)
            <img src="{{ Storage::url($item->image) }}" alt="{{ $item->title }}">
        @endif
        @if ($item->hasVideo())
            <span class="news-play" aria-hidden="true"><i class="bi bi-play-fill"></i></span>
        @endif
    </div>
@endif
