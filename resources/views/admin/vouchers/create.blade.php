{{--
    Create Voucher
    Renders the voucher-form partial with $item = null (blank state).
    POSTs to admin.vouchers.store.
--}}
@extends('layouts.admin')
@section('title', 'Add Voucher')
@section('back_url', route('admin.vouchers.index'))
@section('content')
{{-- ── Page Guide ─────────────────────────────────────────────────────── --}}
<div style="background:#0b1929;border:1px solid #1e3a5f;border-left:3px solid #3b82f6;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:13px;">
    <div onclick="var n=this.nextElementSibling;n.hidden=!n.hidden;this.querySelector('.pg-ch').style.transform=n.hidden?'':'rotate(180deg)'"
         style="display:flex;align-items:center;gap:8px;cursor:pointer;user-select:none;">
        <i class="bi bi-book" style="color:#60a5fa;font-size:14px;"></i>
        <strong style="color:#93c5fd;">Add Voucher Guide</strong>
        <i class="bi bi-chevron-down pg-ch" style="color:#60a5fa;margin-left:auto;font-size:12px;transition:transform .2s;"></i>
    </div>
    <div hidden style="margin-top:12px;color:#94a3b8;line-height:1.75;">
        <ul style="margin:0;padding-left:18px;">
            <li><strong style="color:#c4d4e8;">Code</strong> — the text players type at checkout. Stored and matched in uppercase (e.g. <code style="color:#888;">SUMMER25</code>).</li>
            <li><strong style="color:#c4d4e8;">Discount %</strong> — percentage off the applicable items.</li>
            <li><strong style="color:#c4d4e8;">Start / Expiry</strong> — the code is only valid between these dates (inclusive).</li>
            <li><strong style="color:#c4d4e8;">Max Uses</strong> — leave blank for unlimited uses. Once the limit is reached the code is automatically rejected.</li>
            <li><strong style="color:#c4d4e8;">Scope</strong> — choose <em>All</em> to apply to any product, <em>Category</em> to restrict to a category, or <em>Product</em> to restrict to one specific item. Additional dropdowns appear when a narrower scope is selected.</li>
            <li><strong style="color:#c4d4e8;">Active</strong> — set to <em>No</em> to pre-create a voucher without activating it yet.</li>
        </ul>
    </div>
</div>
<div class="admin-form-card" style="max-width:700px;">
    <form method="POST" action="{{ route('admin.vouchers.store') }}">
        @csrf
        @include('admin._partials.voucher-form', ['item' => null])
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Save Voucher</button>
            <a href="{{ route('admin.vouchers.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
