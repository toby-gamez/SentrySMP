@extends('layouts.admin')
@section('title', 'Edit Team Category')
@section('content')
<div class="admin-form-card" style="max-width:500px;">
    <form method="POST" action="{{ route('admin.team.categories.update', $category) }}">
        @csrf @method('PUT')
        <div class="form-group">
            <label>Category Name *</label>
            <input type="text" name="Name" value="{{ old('Name', $category->Name) }}" required maxlength="100">
        </div>
        <div class="form-group">
            <label>Sort Order</label>
            <input type="number" name="SortOrder" value="{{ old('SortOrder', $category->SortOrder) }}" min="0">
        </div>
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Update Category</button>
            <a href="{{ route('admin.team.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
