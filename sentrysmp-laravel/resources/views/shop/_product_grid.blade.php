{{-- Shared product grid partial. Variables: $items (collection), $type (string), $fallbackImage --}}
@if($items->isEmpty())
    <div class="alert alert-info"><i class="bi bi-info-circle-fill"></i> No items available at the moment.</div>
@else
    <div class="product-grid">
        @foreach($items as $item)
            @php
                $salePercent = (int) ($item->Sale ?? 0);
                $price       = (float) ($item->Price ?? 0);
                $ep          = $salePercent > 0 ? round($price * (1 - $salePercent / 100), 2) : $price;
                $imgSrc      = !empty($item->Image) ? $item->Image : null;
                $fallback    = asset($fallbackImage ?? 'images/gray-bundle.png');
                $itemName    = $item->Name ?? '';
                $itemDesc    = $item->Description ?? '';
                $serverName  = $item->server?->Name ?? '';
                $itemId      = $item->Id ?? 0;
            @endphp
            <div class="product">
                @if($imgSrc)
                    <img src="{{ $imgSrc }}" class="product-image" alt="{{ $itemName }}">
                @else
                    <img src="{{ $fallback }}" class="product-image" alt="{{ $itemName }}">
                @endif

                <h3>{{ $itemName }}</h3>
                @if($serverName)
                    <small class="text-muted">{{ $serverName }}</small>
                @endif
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

                <button class="great-button" onclick='addToCartPreset({{ $itemId }}, {{ json_encode($itemName) }}, {{ json_encode($type) }}, {{ json_encode($serverName) }}, {{ $price }}, {{ $salePercent }})'>
                    <i class="bi bi-cart-plus"></i> Add to Cart
                </button>
            </div>
        @endforeach
    </div>
@endif
