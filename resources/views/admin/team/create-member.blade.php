{{--
    Create Team Member (fallback form — team/index handles adds inline via JS)
    Variables: $categories (Collection<TeamCategory>), $ranks (Collection<TeamRank>)
    SkinUrl is optional; if blank, Minotar is used at display time.
    POSTs to admin.team.members.store.
--}}
@extends('layouts.admin')
@section('title', 'Add Team Member')
@section('content')
{{-- ── Page Guide ─────────────────────────────────────────────────────── --}}
<div style="background:#0b1929;border:1px solid #1e3a5f;border-left:3px solid #3b82f6;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:13px;">
    <div onclick="var n=this.nextElementSibling;n.hidden=!n.hidden;this.querySelector('.pg-ch').style.transform=n.hidden?'':'rotate(180deg)'"
         style="display:flex;align-items:center;gap:8px;cursor:pointer;user-select:none;">
        <i class="bi bi-book" style="color:#60a5fa;font-size:14px;"></i>
        <strong style="color:#93c5fd;">Add Team Member Guide</strong>
        <i class="bi bi-chevron-down pg-ch" style="color:#60a5fa;margin-left:auto;font-size:12px;transition:transform .2s;"></i>
    </div>
    <div hidden style="margin-top:12px;color:#94a3b8;line-height:1.75;">
        <p style="margin:0 0 6px;">This is a fallback form — most member management happens inline on the <a href="{{ route('admin.team.index') }}" style="color:#60a5fa;">Team Management page</a>.</p>
        <ul style="margin:0;padding-left:18px;">
            <li><strong style="color:#c4d4e8;">Minecraft Name</strong> — the player's in-game username. Used to fetch their skin from Minotar on the team page.</li>
            <li><strong style="color:#c4d4e8;">Category</strong> — which group section this member appears under (e.g. Management).</li>
            <li><strong style="color:#c4d4e8;">Rank</strong> — optional coloured badge shown next to their name.</li>
            <li><strong style="color:#c4d4e8;">Skin URL</strong> — leave blank to use Minotar (auto-fetched from Minecraft username). Provide a URL only if you need a custom skin image.</li>
            <li><strong style="color:#c4d4e8;">Sort Order</strong> — lower numbers appear first within the category.</li>
        </ul>
    </div>
</div>
<div class="admin-form-card" style="max-width:600px;">
    <form method="POST" action="{{ route('admin.team.members.store') }}">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="form-group">
                <label>Minecraft Name *</label>
                <input type="text" name="MinecraftName" value="{{ old('MinecraftName') }}" required maxlength="100" placeholder="Steve">
            </div>
            <div class="form-group">
                <label>Category *</label>
                <select name="TeamCategoryId" required>
                    <option value="">— select —</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->Id }}" {{ old('TeamCategoryId') == $cat->Id ? 'selected' : '' }}>{{ $cat->Name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Rank</label>
                <select name="TeamRankId">
                    <option value="">— none —</option>
                    @foreach($ranks as $rank)
                        <option value="{{ $rank->Id }}" {{ old('TeamRankId') == $rank->Id ? 'selected' : '' }}>{{ $rank->Name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Sort Order</label>
                <input type="number" name="SortOrder" value="{{ old('SortOrder', 0) }}" min="0">
            </div>
            <div class="form-group" style="grid-column:1/-1;">
                <label>Skin URL <span style="color:#555;">(leave blank to use Minotar)</span></label>
                <input type="text" name="SkinUrl" value="{{ old('SkinUrl') }}" maxlength="500" placeholder="https://...">
            </div>
        </div>
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Save Member</button>
            <a href="{{ route('admin.team.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
