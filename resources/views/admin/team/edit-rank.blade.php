{{--
    Edit Team Rank (fallback form — team/index handles edits inline via JS)
    Variables: $rank (TeamRank)
    PUTs to admin.team.rank.update.
--}}
@extends('layouts.admin')
@section('title', 'Edit Team Rank')
@section('content')
{{-- ── Page Guide ─────────────────────────────────────────────────────── --}}
<div style="background:#0b1929;border:1px solid #1e3a5f;border-left:3px solid #3b82f6;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:13px;">
    <div onclick="var n=this.nextElementSibling;n.hidden=!n.hidden;this.querySelector('.pg-ch').style.transform=n.hidden?'':'rotate(180deg)'"
         style="display:flex;align-items:center;gap:8px;cursor:pointer;user-select:none;">
        <i class="bi bi-book" style="color:#60a5fa;font-size:14px;"></i>
        <strong style="color:#93c5fd;">Edit Team Rank Guide</strong>
        <i class="bi bi-chevron-down pg-ch" style="color:#60a5fa;margin-left:auto;font-size:12px;transition:transform .2s;"></i>
    </div>
    <div hidden style="margin-top:12px;color:#94a3b8;line-height:1.75;">
        <p style="margin:0 0 6px;">This is a fallback form — editing ranks inline on the <a href="{{ route('admin.team.index') }}" style="color:#60a5fa;">Team Management page</a> shows a live badge preview.</p>
        <ul style="margin:0;padding-left:18px;">
            <li><strong style="color:#c4d4e8;">Rank Name</strong> — changing the name updates the badge immediately for all members who hold this rank.</li>
            <li><strong style="color:#c4d4e8;">Display Order</strong> — controls the order ranks appear in the assignment dropdown.</li>
        </ul>
    </div>
</div>
<div class="admin-form-card" style="max-width:500px;">
    <form method="POST" action="{{ route('admin.team.rank.update', $rank) }}">
        @csrf @method('PUT')
        <div class="form-group">
            <label>Rank Name *</label>
            <input type="text" name="name" value="{{ old('name', $rank->name) }}" required maxlength="100">
        </div>
        <div class="form-group">
            <label>Display Order</label>
            <input type="number" name="display_order" value="{{ old('display_order', $rank->display_order) }}" min="0">
        </div>
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Update Rank</button>
            <a href="{{ route('admin.team.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
