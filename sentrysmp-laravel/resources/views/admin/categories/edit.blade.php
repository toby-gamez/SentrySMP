@extends('layouts.admin')
@section('title', 'Edit Category')
@section('back_url', route('admin.categories.index'))
@section('content')
<div class="admin-form-card">
    <form method="POST" action="{{ route('admin.categories.update', $category) }}">
        @csrf @method('PUT')
        @include('admin._partials.category-form', ['item' => $category])
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Update Category</button>
            <a href="{{ route('admin.categories.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
