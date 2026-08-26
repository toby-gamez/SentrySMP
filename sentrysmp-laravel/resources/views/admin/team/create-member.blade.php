@extends('layouts.admin')
@section('title', 'Add Team Member')
@section('content')
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
