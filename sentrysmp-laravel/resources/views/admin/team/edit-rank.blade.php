@extends('layouts.admin')
@section('title', 'Edit Team Rank')
@section('content')
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
