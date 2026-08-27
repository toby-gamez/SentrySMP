{{--
    Create Category
    Renders the category-form partial with $item = null (blank state).
    POSTs to admin.categories.store.
--}}
@extends('layouts.admin')
@section('title', 'Add Category')
@section('back_url', route('admin.categories.index'))
@section('content')
{{-- ── Page Guide ─────────────────────────────────────────────────────── --}}
<div style="background:#0b1929;border:1px solid #1e3a5f;border-left:3px solid #3b82f6;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:13px;">
    <div onclick="var n=this.nextElementSibling;n.hidden=!n.hidden;this.querySelector('.pg-ch').style.transform=n.hidden?'':'rotate(180deg)'"
         style="display:flex;align-items:center;gap:8px;cursor:pointer;user-select:none;">
        <i class="bi bi-book" style="color:#60a5fa;font-size:14px;"></i>
        <strong style="color:#93c5fd;">Add Category Guide</strong>
        <i class="bi bi-chevron-down pg-ch" style="color:#60a5fa;margin-left:auto;font-size:12px;transition:transform .2s;"></i>
    </div>
    <div hidden style="margin-top:12px;color:#94a3b8;line-height:1.75;">
        <ul style="margin:0;padding-left:18px;">
            <li><strong style="color:#c4d4e8;">Name</strong> — displayed as the category heading in the shop.</li>
            <li><strong style="color:#c4d4e8;">Slug</strong> — URL-safe identifier auto-generated from the name. Edit manually only if you need a specific URL path (e.g. <code style="color:#888;">battle-passes</code>). Also used as the image subfolder name.</li>
            <li><strong style="color:#c4d4e8;">Colour</strong> — shown as a colour accent on the category card in the shop. Use the colour picker or type a hex code.</li>
            <li><strong style="color:#c4d4e8;">Category Image</strong> — browse the image library or upload a new file directly. Recommended size: 500 × 500 px.</li>
        </ul>
    </div>
</div>
<div class="admin-form-card">
    <form method="POST" action="{{ route('admin.categories.store') }}">
        @csrf
        @include('admin._partials.category-form', ['item' => null])
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Save Category</button>
            <a href="{{ route('admin.categories.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
