@extends('layouts.admin')
@section('title', 'Add Team Rank')
@section('content')
<div class="admin-form-card" style="max-width:500px;">
    <form method="POST" action="{{ route('admin.team.ranks.store') }}">
        @csrf
        <div class="form-group">
            <label>Rank Name *</label>
            <input type="text" name="Name" value="{{ old('Name') }}" required maxlength="100" placeholder="e.g. Owner">
        </div>
        <div class="form-group">
            <label>Hex Color <span style="color:#555;">(e.g. #ff0000)</span></label>
            <input type="text" name="HexColor" value="{{ old('HexColor') }}" maxlength="20" placeholder="#23d05e">
        </div>
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Save Rank</button>
            <a href="{{ route('admin.team.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
