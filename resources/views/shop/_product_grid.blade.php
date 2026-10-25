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
                $imgJson     = $imgSrc ? json_encode($imgSrc, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) : 'null';
                $itemId      = $item->id;
                $itemName    = $item->name;
                $categoryName = $category->name ?? '';
                $isRank      = ($category->slug ?? '') === 'ranks';
            @endphp
            <div class="product" data-item-id="{{ $itemId }}">
                @if($imgSrc)
                    <img src="{{ $imgSrc }}" class="product-image" alt="{{ $itemName }}">
                @else
                    <div class="product-image" style="background:#1a1a1a;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-box" style="font-size:2rem;color:#555;"></i>
                    </div>
                @endif

                <h3>{{ $itemName }}</h3>

                <div class="price-container">
                    @if($salePercent > 0)
                        <p class="original-price" title="Original price">€{{ number_format($price, 2) }}</p>
                        <p class="discounted-price" title="Sale price">€{{ number_format($ep, 2) }}</p>
                        <span class="discount-badge">{{ $salePercent }}% OFF</span>
                    @else
                        <strong>€{{ number_format($price, 2) }}</strong>
                    @endif
                </div>

                <div class="product-actions">
                    <button type="button" class="product-more-btn" onclick="openProductModal({{ $itemId }})">
                        <i class="bi bi-info-circle"></i> More
                    </button>

                    <button class="great-button buy-button" onclick='addToCartPreset({{ $itemId }}, {{ json_encode($itemName, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}, {{ json_encode($categoryName, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}, {{ $price }}, {{ $salePercent }}, {{ $imgJson }}, {{ $isRank ? 'true' : 'false' }})'>
                        BUY
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    @foreach($items as $item)
        @include('shop._product_modal', [
            'item' => $item,
            'category' => $category,
            'fallbackImage' => $fallbackImage,
        ])
    @endforeach

    <script>
        (function () {
            document.addEventListener('keydown', function (event) {
                if (event.key !== 'Escape') return;
                var open = document.querySelector('.product-modal.show');
                if (open) closeProductModal(open.id.replace('product-modal-', ''));
            });
        })();

        function openProductModal(itemId) {
            var modal = document.getElementById('product-modal-' + itemId);
            if (!modal) return;

            var qty = (typeof _selectedQty !== 'undefined' && _selectedQty['item_' + itemId]) || 1;
            modal.querySelectorAll('.qty-preset-btn').forEach(function (btn) {
                btn.classList.toggle('selected', parseInt(btn.getAttribute('data-qty'), 10) === qty);
            });

            modal.classList.add('show');
            document.body.classList.add('product-modal-open');
        }

        function closeProductModal(itemId) {
            var modal = document.getElementById('product-modal-' + itemId);
            if (!modal) return;

            modal.classList.remove('show');
            if (!document.querySelector('.product-modal.show')) {
                document.body.classList.remove('product-modal-open');
            }
        }
    </script>
@endif
