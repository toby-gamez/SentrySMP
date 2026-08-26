@php
$rawScope = old('Scope', $item?->Scope ?? 'All');
$currentScope = $rawScope === 'Item' ? 'Product' : $rawScope; // normalise legacy DB value
$currentCat   = old('ScopeCategory', $item?->ScopeCategory ?? '');
$currentItemId = old('ScopeItemId', $item?->ScopeItemId ?? '');
@endphp

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

    <div class="form-group" style="grid-column:1/-1;">
        <label>Voucher Code * <span style="color:#666;font-weight:400;">(will be uppercased)</span></label>
        <input type="text" name="Code" value="{{ old('Code', $item?->Code) }}" required maxlength="50"
               placeholder="SUMMER2025" style="text-transform:uppercase;">
    </div>

    <div class="form-group" style="grid-column:1/-1;">
        <label>Description</label>
        <input type="text" name="Description" value="{{ old('Description', $item?->Description) }}" maxlength="500">
    </div>

    <div class="form-group">
        <label>Start Date *</label>
        <input type="date" name="StartDate"
               value="{{ old('StartDate', $item?->StartDate?->format('Y-m-d')) }}" required>
    </div>

    <div class="form-group">
        <label>Expiration Date *</label>
        <input type="date" name="ExpirationDate"
               value="{{ old('ExpirationDate', $item?->ExpirationDate?->format('Y-m-d')) }}" required>
    </div>

    <div class="form-group">
        <label>Discount % *</label>
        <input type="number" name="DiscountPercent"
               value="{{ old('DiscountPercent', $item?->DiscountPercent ?? 10) }}"
               min="1" max="100" step="0.01" required>
    </div>

    <div class="form-group">
        <label>Max Uses <span style="color:#666;font-weight:400;">(blank = unlimited)</span></label>
        <input type="number" name="MaxUses" value="{{ old('MaxUses', $item?->MaxUses) }}" min="1">
    </div>

    <div class="form-group">
        <label>Scope</label>
        <select name="Scope" id="vf-scope" onchange="vfUpdateScope(this.value)">
            @foreach(['All', 'Category', 'Product'] as $s)
                <option value="{{ $s }}" {{ $currentScope === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label>Active</label>
        <select name="IsActive">
            <option value="1" {{ (old('IsActive', $item?->IsActive ?? true) ? true : false) ? 'selected' : '' }}>Yes</option>
            <option value="0" {{ (old('IsActive', $item?->IsActive ?? true) ? true : false) ? '' : 'selected' }}>No</option>
        </select>
    </div>

    {{-- Scope Category — visible for Category and Product scopes --}}
    <div class="form-group" id="vf-cat-wrap" style="display:none;">
        <label id="vf-cat-label">Product Type</label>
        <select name="ScopeCategory" id="vf-scope-category" onchange="vfUpdateProductList(this.value)">
            <option value="">— Select type —</option>
            @foreach(['Key','Coin','Bundle','Rank','BattlePass','Other'] as $c)
                <option value="{{ $c }}" {{ $currentCat === $c ? 'selected' : '' }}>{{ $c }}</option>
            @endforeach
        </select>
    </div>

    {{-- Product picker — visible only for Product scope --}}
    <div class="form-group" id="vf-product-wrap" style="display:none;">
        <label>Product</label>
        <select name="ScopeItemId" id="vf-product-select">
            <option value="">— Select product type first —</option>
        </select>
    </div>

    {{-- Hidden product data for JS --}}
    <script id="vf-products-data" type="application/json">
    {!! json_encode(array_map(fn($col) => $col->map(fn($p) => ['id' => $p->Id, 'name' => $p->Name])->values()->all(), $products ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
    </script>

</div>

<script>
(function () {
    var products = JSON.parse(document.getElementById('vf-products-data').textContent);
    var currentItemId = {{ $currentItemId ? (int)$currentItemId : 'null' }};

    function vfUpdateScope(scope) {
        var catWrap     = document.getElementById('vf-cat-wrap');
        var productWrap = document.getElementById('vf-product-wrap');
        var catSel      = document.getElementById('vf-scope-category');
        var catLabel    = document.getElementById('vf-cat-label');

        if (scope === 'All') {
            catWrap.style.display     = 'none';
            productWrap.style.display = 'none';
        } else if (scope === 'Category') {
            catWrap.style.display     = '';
            productWrap.style.display = 'none';
            catLabel.textContent      = 'Product Type *';
        } else { // Product
            catWrap.style.display     = '';
            productWrap.style.display = '';
            catLabel.textContent      = 'Product Type *';
            vfUpdateProductList(catSel.value);
        }
    }

    window.vfUpdateScope = vfUpdateScope;

    window.vfUpdateProductList = function (cat) {
        var sel   = document.getElementById('vf-product-select');
        var scope = document.getElementById('vf-scope').value;
        if (scope !== 'Product') return;

        sel.innerHTML = '';

        var list = (products[cat] || []);
        if (!list.length) {
            sel.innerHTML = '<option value="">— No products in this type —</option>';
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

    // Initialize on load
    vfUpdateScope('{{ $currentScope }}');
    // If editing with a product scope, populate the product list
    @if($currentScope === 'Product' && $currentCat)
    vfUpdateProductList('{{ $currentCat }}');
    @endif
})();
</script>
