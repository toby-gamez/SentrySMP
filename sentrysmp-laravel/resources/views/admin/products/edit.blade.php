@extends('layouts.admin')
@section('title', 'Edit Product')
@section('content')
<div class="admin-form-card">
    <form method="POST" action="{{ route('admin.products.update', $product) }}">
        @csrf @method('PUT')
        @include('admin._partials.product-form', ['item' => $product, 'categories' => $categories])
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Update Product</button>
            <a href="{{ route('admin.products.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>

@include('admin._partials.commands', ['productId' => $product->id, 'commands' => $commands])
@endsection
