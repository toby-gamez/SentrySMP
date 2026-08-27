{{--
    Create Team Category (fallback form — team/index handles adds inline)
    SortOrder controls display order on the public team page.
    POSTs to admin.team.categories.store.
--}}
@extends('layouts.admin')
@section('title', 'Add Team Category')
@section('content')
{{-- ── Page Guide ─────────────────────────────────────────────────────── --}}
<div style="background:#0b1929;border:1px solid #1e3a5f;border-left:3px solid #3b82f6;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:13px;">
    <div onclick="var n=this.nextElementSibling;n.hidden=!n.hidden;this.querySelector('.pg-ch').style.transform=n.hidden?'':'rotate(180deg)'"
         style="display:flex;align-items:center;gap:8px;cursor:pointer;user-select:none;">
        <i class="bi bi-book" style="color:#60a5fa;font-size:14px;"></i>
        <strong style="color:#93c5fd;">Add Team Category Guide</strong>
        <i class="bi bi-chevron-down pg-ch" style="color:#60a5fa;margin-left:auto;font-size:12px;transition:transform .2s;"></i>
    </div>
    <div hidden style="margin-top:12px;color:#94a3b8;line-height:1.75;">
        <p style="margin:0 0 6px;">This is a fallback form — adding categories inline on the <a href="{{ route('admin.team.index') }}" style="color:#60a5fa;">Team Management page</a> is faster.</p>
        <ul style="margin:0;padding-left:18px;">
            <li><strong style="color:#c4d4e8;">Category Name</strong> — the heading shown on the public team page (e.g. <em>Management</em>, <em>Builders</em>).</li>
            <li><strong style="color:#c4d4e8;">Sort Order</strong> — lower numbers appear higher on the page. You can also reorder categories using the ▲/▼ buttons on the Team Management page.</li>
        </ul>
    </div>
</div>
<div class="admin-form-card" style="max-width:500px;">
    <form method="POST" action="{{ route('admin.team.categories.store') }}">
        @csrf
        <div class="form-group">
            <label>Category Name *</label>
            <input type="text" name="Name" value="{{ old('Name') }}" required maxlength="100" placeholder="e.g. Management">
        </div>
        <div class="form-group">
            <label>Sort Order</label>
            <input type="number" name="SortOrder" value="{{ old('SortOrder', 0) }}" min="0">
        </div>
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Save Category</button>
            <a href="{{ route('admin.team.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
