{{-- Variables: $items (Collection of Product), $category (Category), $fallbackImage (string|null) --}}
@if($items->isEmpty())
    <div class="alert alert-info"><i class="bi bi-info-circle-fill"></i> No items available at the moment.</div>
@else
    <div class="product-grid">
        @foreach($items as $item)
            @php
                $salePercent = (int) $item->sale;
                $price       = (float) $item->price;
                $ep          = $salePercent > 0 ? round($price * (1 - $salePercent / 100), 2) : $price;
                $imgSrc      = $item->image ?: ($fallbackImage ? asset($fallbackImage) : null);
                $itemId      = $item->id;
                $itemName    = $item->name;
                $itemDesc    = $item->description;
                $categoryName = $category->name ?? '';
            @endphp
            <div class="product">
                @if($imgSrc)
                    <img src="{{ $imgSrc }}" class="product-image" alt="{{ $itemName }}">
                @else
                    <div class="product-image" style="background:#1a1a1a;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-box" style="font-size:2rem;color:#555;"></i>
                    </div>
                @endif

                <h3>{{ $itemName }}</h3>
                @if($itemDesc)
                    <p style="font-size:13px;color:#888;margin:6px 0;">{{ $itemDesc }}</p>
                @endif

                <div class="price-container">
                    @if($salePercent > 0)
                        <p class="original-price" title="Original price">€{{ number_format($price, 2) }}</p>
                        <p class="discounted-price" title="Sale price">€{{ number_format($ep, 2) }}</p>
                        <span class="discount-badge">{{ $salePercent }}% OFF</span>
                    @else
                        <strong>€{{ number_format($price, 2) }}</strong>
                    @endif
                </div>

                <div class="qty-presets">
                    @foreach([1, 5, 10, 20] as $qty)
                        <button class="qty-preset-btn" data-item-id="{{ $itemId }}" data-qty="{{ $qty }}"
                                onclick="selectQtyPreset(this, {{ $itemId }}, {{ $qty }})">{{ $qty }}×</button>
                    @endforeach
                </div>

                <button class="great-button" onclick='addToCartPreset({{ $itemId }}, {{ json_encode($itemName) }}, {{ json_encode($categoryName) }}, {{ $price }}, {{ $salePercent }})'>
                    <i class="bi bi-cart-plus"></i> Add to Cart
                </button>
            </div>
        @endforeach
    </div>
@endif
