{{--
    Edit Voucher
    Variables: $voucher (Voucher)
    Renders the voucher-form partial pre-filled with the existing voucher.
    PUTs to admin.vouchers.update.
--}}
@extends('layouts.admin')
@section('title', 'Edit Voucher')
@section('back_url', route('admin.vouchers.index'))
@section('content')
{{-- ── Page Guide ─────────────────────────────────────────────────────── --}}
<div style="background:#0b1929;border:1px solid #1e3a5f;border-left:3px solid #3b82f6;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:13px;">
    <div onclick="var n=this.nextElementSibling;n.hidden=!n.hidden;this.querySelector('.pg-ch').style.transform=n.hidden?'':'rotate(180deg)'"
         style="display:flex;align-items:center;gap:8px;cursor:pointer;user-select:none;">
        <i class="bi bi-book" style="color:#60a5fa;font-size:14px;"></i>
        <strong style="color:#93c5fd;">Edit Voucher Guide</strong>
        <i class="bi bi-chevron-down pg-ch" style="color:#60a5fa;margin-left:auto;font-size:12px;transition:transform .2s;"></i>
    </div>
    <div hidden style="margin-top:12px;color:#94a3b8;line-height:1.75;">
        <ul style="margin:0;padding-left:18px;">
            <li>All fields work the same as when creating — see the <em>Add Voucher</em> guide for details.</li>
            <li><strong style="color:#c4d4e8;">Changing the code</strong> invalidates any links or notes players have saved with the old code.</li>
            <li>Set <strong style="color:#c4d4e8;">Active</strong> to <em>No</em> to disable the code without deleting it (e.g. when a promotion ends).</li>
            <li>Current usage count is shown on the voucher list — it is not reset when you edit the voucher.</li>
        </ul>
    </div>
</div>
<div class="admin-form-card" style="max-width:700px;">
    <form method="POST" action="{{ route('admin.vouchers.update', $voucher) }}">
        @csrf @method('PUT')
        @include('admin._partials.voucher-form', ['item' => $voucher])
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Update Voucher</button>
            <a href="{{ route('admin.vouchers.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
