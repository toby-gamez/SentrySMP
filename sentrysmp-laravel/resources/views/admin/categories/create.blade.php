@extends('layouts.admin')
@section('title', 'Add Category')
@section('content')
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
