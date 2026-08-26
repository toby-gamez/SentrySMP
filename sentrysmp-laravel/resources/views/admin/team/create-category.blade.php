@extends('layouts.admin')
@section('title', 'Add Team Category')
@section('content')
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
