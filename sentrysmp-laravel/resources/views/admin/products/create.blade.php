@extends('layouts.admin')
@section('title', 'Add Product')
@section('back_url', route('admin.products.index'))
@section('content')
<div class="admin-form-card">
    <form method="POST" action="{{ route('admin.products.store') }}">
        @csrf
        @include('admin._partials.product-form', ['item' => null, 'categories' => $categories])
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Save Product</button>
            <a href="{{ route('admin.products.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
