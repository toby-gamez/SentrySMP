{{--
    Edit Team Member (fallback form — team/index handles edits inline via JS)
    Variables: $member (TeamMember), $categories (Collection<TeamCategory>),
               $ranks (Collection<TeamRank>)
    PUTs to admin.team.members.update.
--}}
@extends('layouts.admin')
@section('title', 'Edit Team Member')
@section('content')
{{-- ── Page Guide ─────────────────────────────────────────────────────── --}}
<div style="background:#0b1929;border:1px solid #1e3a5f;border-left:3px solid #3b82f6;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:13px;">
    <div onclick="var n=this.nextElementSibling;n.hidden=!n.hidden;this.querySelector('.pg-ch').style.transform=n.hidden?'':'rotate(180deg)'"
         style="display:flex;align-items:center;gap:8px;cursor:pointer;user-select:none;">
        <i class="bi bi-book" style="color:#60a5fa;font-size:14px;"></i>
        <strong style="color:#93c5fd;">Edit Team Member Guide</strong>
        <i class="bi bi-chevron-down pg-ch" style="color:#60a5fa;margin-left:auto;font-size:12px;transition:transform .2s;"></i>
    </div>
    <div hidden style="margin-top:12px;color:#94a3b8;line-height:1.75;">
        <p style="margin:0 0 6px;">This is a fallback form — editing members inline on the <a href="{{ route('admin.team.index') }}" style="color:#60a5fa;">Team Management page</a> is faster and shows a live skin preview.</p>
        <ul style="margin:0;padding-left:18px;">
            <li>All fields are the same as on the create form. Changes apply immediately on submit.</li>
            <li><strong style="color:#c4d4e8;">Minecraft Name</strong> — changing this also updates the skin shown on the team page (fetched from Minotar using the new username).</li>
            <li><strong style="color:#c4d4e8;">Category</strong> — move the member to a different group by selecting a new category.</li>
        </ul>
    </div>
</div>
<div class="admin-form-card" style="max-width:600px;">
    <form method="POST" action="{{ route('admin.team.members.update', $member) }}">
        @csrf @method('PUT')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="form-group">
                <label>Minecraft Name *</label>
                <input type="text" name="MinecraftName" value="{{ old('MinecraftName', $member->MinecraftName) }}" required maxlength="100">
            </div>
            <div class="form-group">
                <label>Category *</label>
                <select name="TeamCategoryId" required>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->Id }}" {{ old('TeamCategoryId', $member->TeamCategoryId) == $cat->Id ? 'selected' : '' }}>{{ $cat->Name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Rank</label>
                <select name="TeamRankId">
                    <option value="">— none —</option>
                    @foreach($ranks as $rank)
                        <option value="{{ $rank->Id }}" {{ old('TeamRankId', $member->TeamRankId) == $rank->Id ? 'selected' : '' }}>{{ $rank->Name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Sort Order</label>
                <input type="number" name="SortOrder" value="{{ old('SortOrder', $member->SortOrder) }}" min="0">
            </div>
            <div class="form-group" style="grid-column:1/-1;">
                <label>Skin URL <span style="color:#555;">(leave blank to use Minotar)</span></label>
                <input type="text" name="SkinUrl" value="{{ old('SkinUrl', $member->SkinUrl) }}" maxlength="500" placeholder="https://...">
            </div>
        </div>
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Update Member</button>
            <a href="{{ route('admin.team.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
