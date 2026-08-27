{{--
    Create Product
    Variables: $categories (Collection<Category>)
    Renders the product-form partial with $item = null (blank state).
    POSTs to admin.products.store.
--}}
@extends('layouts.admin')
@section('title', 'Add Product')
@section('back_url', route('admin.products.index'))
@section('content')
{{-- ── Page Guide ─────────────────────────────────────────────────────── --}}
<div style="background:#0b1929;border:1px solid #1e3a5f;border-left:3px solid #3b82f6;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:13px;">
    <div onclick="var n=this.nextElementSibling;n.hidden=!n.hidden;this.querySelector('.pg-ch').style.transform=n.hidden?'':'rotate(180deg)'"
         style="display:flex;align-items:center;gap:8px;cursor:pointer;user-select:none;">
        <i class="bi bi-book" style="color:#60a5fa;font-size:14px;"></i>
        <strong style="color:#93c5fd;">Add Product Guide</strong>
        <i class="bi bi-chevron-down pg-ch" style="color:#60a5fa;margin-left:auto;font-size:12px;transition:transform .2s;"></i>
    </div>
    <div hidden style="margin-top:12px;color:#94a3b8;line-height:1.75;">
        <ul style="margin:0;padding-left:18px;">
            <li><strong style="color:#c4d4e8;">Category</strong> — which section of the shop this product appears in.</li>
            <li><strong style="color:#c4d4e8;">Price</strong> — base price in euros (e.g. <code style="color:#888;">9.99</code>).</li>
            <li><strong style="color:#c4d4e8;">Sale %</strong> — if non-zero, the shop shows the discounted price: <em>price × (1 − sale / 100)</em>. Example: €10.00 at 20% sale = €8.00.</li>
            <li><strong style="color:#c4d4e8;">Max orders per user</strong> — leave blank for unlimited purchases per player.</li>
            <li><strong style="color:#c4d4e8;">Product Image</strong> — browse the image library (filtered to the selected category's subfolder) or upload a new file directly.</li>
            <li>After saving, open the product's <strong style="color:#c4d4e8;">edit page</strong> to add the delivery commands that run on the game server when someone buys it.</li>
        </ul>
    </div>
</div>
<div class="admin-form-card">
    <form method="POST" action="{{ route('admin.products.store') }}">
        @csrf
        @include('admin._partials.product-form', ['item' => null, 'categories' => $categories])
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Save Product</button>
            <a href="{{ route('admin.products.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
