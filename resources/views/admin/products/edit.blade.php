{{--
    Edit Product
    Variables: $product (Product), $categories (Collection<Category>),
               $commands (Collection<Command> belonging to this product)
    PUTs to admin.products.update.
    Includes the commands partial below the form so admins can manage
    delivery commands without leaving the edit page.
--}}
@extends('layouts.admin')
@section('title', 'Edit Product')
@section('back_url', route('admin.products.index'))
@section('content')
{{-- ── Page Guide ─────────────────────────────────────────────────────── --}}
<div style="background:#0b1929;border:1px solid #1e3a5f;border-left:3px solid #3b82f6;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:13px;">
    <div onclick="var n=this.nextElementSibling;n.hidden=!n.hidden;this.querySelector('.pg-ch').style.transform=n.hidden?'':'rotate(180deg)'"
         style="display:flex;align-items:center;gap:8px;cursor:pointer;user-select:none;">
        <i class="bi bi-book" style="color:#60a5fa;font-size:14px;"></i>
        <strong style="color:#93c5fd;">Edit Product Guide</strong>
        <i class="bi bi-chevron-down pg-ch" style="color:#60a5fa;margin-left:auto;font-size:12px;transition:transform .2s;"></i>
    </div>
    <div hidden style="margin-top:12px;color:#94a3b8;line-height:1.75;">
        <p style="margin:0 0 8px;"><strong style="color:#c4d4e8;">Product Details</strong> (top form) — update name, price, sale, image, etc., then click <em>Update Product</em>.</p>
        <p style="margin:0 0 6px;"><strong style="color:#c4d4e8;">Delivery Commands</strong> (bottom section) — commands run on the game server when this product is purchased:</p>
        <ul style="margin:0;padding-left:18px;">
            <li>Use <code style="color:#888;">%player%</code> as a placeholder for the buyer's Minecraft username (e.g. <code style="color:#888;">give %player% diamond 64</code>).</li>
            <li>You can add multiple commands — they all run in order after a successful payment.</li>
            <li>Edit an existing command and click <em>Save</em>, or click the trash icon to remove it.</li>
            <li>Commands are delivered via RCON to the configured game server. Check the <strong style="color:#c4d4e8;">Command Queue</strong> if delivery fails.</li>
        </ul>
    </div>
</div>
<div class="admin-form-card">
    <form method="POST" action="{{ route('admin.products.update', $product) }}">
        @csrf @method('PUT')
        @include('admin._partials.product-form', ['item' => $product, 'categories' => $categories])
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Update Product</button>
            <a href="{{ route('admin.products.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>

@include('admin._partials.commands', ['productId' => $product->id, 'commands' => $commands])
@endsection
