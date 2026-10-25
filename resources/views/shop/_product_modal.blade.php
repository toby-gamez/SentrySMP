{{-- Variables: $item (Product), $category (Category), $fallbackImage (string|null) --}}
@php
    $salePercent = (int) $item->sale;
    $price       = (float) $item->price;
    $ep          = $salePercent > 0 ? round($price * (1 - $salePercent / 100), 2) : $price;
    $imgSrc      = $item->image ?: ($fallbackImage ? asset($fallbackImage) : null);
    $imgJson     = $imgSrc ? json_encode($imgSrc, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) : 'null';
    $itemId      = $item->id;
    $itemName    = $item->name;
    $itemDesc    = $item->description_html;
    $categoryName = $category->name ?? '';
    $isRank      = ($category->slug ?? '') === 'ranks';
@endphp
<div class="modal product-modal" id="product-modal-{{ $itemId }}" role="dialog" aria-modal="true"
     aria-labelledby="product-modal-title-{{ $itemId }}"
     onclick="if (event.target === this) closeProductModal({{ $itemId }})">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="product-modal-title-{{ $itemId }}">{{ $itemName }}</h2>
                <button type="button" class="btn-close" onclick="closeProductModal({{ $itemId }})" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 product-modal-media">
                        @if($imgSrc)
                            <img src="{{ $imgSrc }}" alt="{{ $itemName }}">
                        @else
                            <div class="product-modal-placeholder">
                                <i class="bi bi-box"></i>
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6 product-modal-info">
                        <div class="price-container">
                            @if($salePercent > 0)
                                <p class="original-price" title="Original price">€{{ number_format($price, 2) }}</p>
                                <p class="discounted-price" title="Sale price">€{{ number_format($ep, 2) }}</p>
                                <span class="discount-badge">{{ $salePercent }}% OFF</span>
                            @else
                                <strong>€{{ number_format($price, 2) }}</strong>
                            @endif
                        </div>

                        @unless($isRank)
                            <div class="product-modal-qty-label">Quantity</div>
                            <div class="qty-presets product-modal-qty">
                                @foreach([1, 5, 10, 20] as $qty)
                                    <button type="button" class="qty-preset-btn {{ $qty === 1 ? 'selected' : '' }}"
                                            data-item-id="{{ $itemId }}" data-qty="{{ $qty }}"
                                            onclick="selectQtyPreset(this, {{ $itemId }}, {{ $qty }})">{{ $qty }}×</button>
                                @endforeach
                            </div>
                        @endunless

                        <div class="product-modal-description">
                            @if(trim($itemDesc) !== '')
                                {!! $itemDesc !!}
                            @else
                                <p class="product-modal-description-empty">No description provided.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="secondary" onclick="closeProductModal({{ $itemId }})">Close</button>
                <button type="button" class="great-button buy-button"
                        onclick='addToCartPreset({{ $itemId }}, {{ json_encode($itemName, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}, {{ json_encode($categoryName, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}, {{ $price }}, {{ $salePercent }}, {{ $imgJson }}, {{ $isRank ? 'true' : 'false' }}); closeProductModal({{ $itemId }})'>
                    BUY
                </button>
            </div>
        </div>
    </div>
</div>
