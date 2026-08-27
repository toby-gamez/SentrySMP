@php
$currentScope  = old('scope', $item?->scope ?? 'All');
$currentCat    = old('scope_category', $item?->scope_category ?? '');
$currentItemId = old('scope_item_id', $item?->scope_item_id ?? '');
@endphp

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

    <div class="form-group" style="grid-column:1/-1;">
        <label>Voucher Code * <span style="color:#666;font-weight:400;">(will be uppercased)</span></label>
        <input type="text" name="code" value="{{ old('code', $item?->code) }}" required maxlength="50"
               placeholder="SUMMER2025" style="text-transform:uppercase;">
    </div>

    <div class="form-group" style="grid-column:1/-1;">
        <label>Description</label>
        <input type="text" name="description" value="{{ old('description', $item?->description) }}" maxlength="500">
    </div>

    <div class="form-group">
        <label>Start Date *</label>
        <input type="date" name="start_date"
               value="{{ old('start_date', $item?->start_date?->format('Y-m-d')) }}" required>
    </div>

    <div class="form-group">
        <label>Expiration Date *</label>
        <input type="date" name="expiration_date"
               value="{{ old('expiration_date', $item?->expiration_date?->format('Y-m-d')) }}" required>
    </div>

    <div class="form-group">
        <label>Discount % *</label>
        <input type="number" name="discount_percent"
               value="{{ old('discount_percent', $item?->discount_percent ?? 10) }}"
               min="1" max="100" step="0.01" required>
    </div>

    <div class="form-group">
        <label>Max Uses <span style="color:#666;font-weight:400;">(blank = unlimited)</span></label>
        <input type="number" name="max_uses" value="{{ old('max_uses', $item?->max_uses) }}" min="1">
    </div>

    <div class="form-group">
        <label>Scope</label>
        <select name="scope" id="vf-scope" onchange="vfUpdateScope(this.value)">
            @foreach(['All', 'Category', 'Product'] as $s)
                <option value="{{ $s }}" {{ $currentScope === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label>Active</label>
        <select name="is_active">
            <option value="1" {{ (old('is_active', $item?->is_active ?? true)) ? 'selected' : '' }}>Yes</option>
            <option value="0" {{ (old('is_active', $item?->is_active ?? true)) ? '' : 'selected' }}>No</option>
        </select>
    </div>

    {{-- Scope Category — visible for Category and Product scopes --}}
    <div class="form-group" id="vf-cat-wrap" style="display:none;">
        <label>Category *</label>
        <select name="scope_category" id="vf-scope-category" onchange="vfUpdateProductList(this.value)">
            <option value="">— Select category —</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->slug }}" {{ $currentCat === $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Product picker — visible only for Product scope --}}
    <div class="form-group" id="vf-product-wrap" style="display:none;">
        <label>Product</label>
        <select name="scope_item_id" id="vf-product-select">
            <option value="">— Select category first —</option>
        </select>
    </div>

    {{-- Product data grouped by category slug for JS --}}
    <script id="vf-products-data" type="application/json">
    {!! json_encode(
        $products->groupBy(fn($p) => $p->category?->slug ?? '')->map(fn($col) =>
            $col->map(fn($p) => ['id' => $p->id, 'name' => $p->name])->values()->all()
        ),
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ) !!}
    </script>

</div>

<script>
(function () {
    var products      = JSON.parse(document.getElementById('vf-products-data').textContent);
    var currentItemId = {{ $currentItemId ? (int)$currentItemId : 'null' }};

    function vfUpdateScope(scope) {
        var catWrap     = document.getElementById('vf-cat-wrap');
        var productWrap = document.getElementById('vf-product-wrap');

        if (scope === 'All') {
            catWrap.style.display     = 'none';
            productWrap.style.display = 'none';
        } else if (scope === 'Category') {
            catWrap.style.display     = '';
            productWrap.style.display = 'none';
        } else {
            catWrap.style.display     = '';
            productWrap.style.display = '';
            vfUpdateProductList(document.getElementById('vf-scope-category').value);
        }
    }

    window.vfUpdateScope = vfUpdateScope;

    window.vfUpdateProductList = function (slug) {
        var sel   = document.getElementById('vf-product-select');
        var scope = document.getElementById('vf-scope').value;
        if (scope !== 'Product') return;

        sel.innerHTML = '';
        var list = products[slug] || [];

        if (!list.length) {
            sel.innerHTML = '<option value="">— No products in this category —</option>';
            return;
        }

        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = '— Select product —';
        sel.appendChild(placeholder);

        list.forEach(function (p) {
            var opt = document.createElement('option');
            opt.value = p.id;
            opt.textContent = p.name;
            if (currentItemId && p.id == currentItemId) opt.selected = true;
            sel.appendChild(opt);
        });
    };

    vfUpdateScope('{{ $currentScope }}');
    @if($currentScope === 'Product' && $currentCat)
    vfUpdateProductList('{{ $currentCat }}');
    @endif
})();
</script>
