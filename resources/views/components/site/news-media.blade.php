@props(['item'])

@php($embed = $item->videoEmbed())

@if ($embed)
    <div class="article-video">
        @if ($embed['type'] === 'iframe')
            <iframe src="{{ $embed['url'] }}" title="{{ $item->title }}"
                    loading="lazy" allowfullscreen
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
        @else
            <video controls preload="metadata"
                   @if ($item->image) poster="{{ Storage::url($item->image) }}" @endif>
                <source src="{{ $embed['url'] }}">
            </video>
        @endif
    </div>
@elseif ($item->image)
    <img src="{{ Storage::url($item->image) }}" alt="{{ $item->title }}" class="article-hero">
@endif
