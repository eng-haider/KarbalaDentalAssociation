@props(['item'])

<article class="card listing-card hover-lift h-100 listing-{{ $item->type }}">
    <div class="listing-thumb">
        @if ($item->image)
            <img src="{{ Storage::url($item->image) }}" alt="{{ $item->title }}" class="listing-img">
        @else
            <div class="listing-img listing-img-placeholder">
                <i class="bi bi-box-seam" aria-hidden="true"></i>
            </div>
        @endif
        <span class="listing-type">{{ $item->typeLabel() }}</span>
        @if ($label = $item->categoryLabel())
            <span class="listing-tag">{{ $label }}</span>
        @endif
    </div>

    <div class="listing-body">
        <h3>{{ $item->title }}</h3>
        <p>{{ Str::limit($item->description, 120) }}</p>

        <div class="listing-price">
            <i class="bi bi-cash-coin" aria-hidden="true"></i> {{ $item->priceLabel() }}
        </div>

        <ul class="listing-meta">
            <li><i class="bi bi-person" aria-hidden="true"></i> {{ $item->contact_name }}</li>
            @if ($item->city)
                <li><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ $item->city }}</li>
            @endif
            <li><i class="bi bi-clock" aria-hidden="true"></i> {{ $item->created_at->translatedFormat('j F Y') }}</li>
        </ul>

        <a href="tel:{{ preg_replace('/\s+/', '', $item->contact_phone) }}" class="btn btn-sm btn-outline-gov mt-2">
            <i class="bi bi-telephone" aria-hidden="true"></i>
            <span dir="ltr">{{ $item->contact_phone }}</span>
        </a>
    </div>
</article>
