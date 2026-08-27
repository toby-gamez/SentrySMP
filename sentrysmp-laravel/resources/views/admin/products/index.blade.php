@extends('layouts.admin')
@section('title', 'Products')
@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">Products ({{ $items->count() }})</h2>
        <a href="{{ route('admin.products.create') }}" class="btn-admin btn-admin-primary"><i class="bi bi-plus-lg"></i> Add Product</a>
    </div>
    @if($items->isEmpty())
        <p style="color:#666;">No products yet.</p>
    @else
    <table class="admin-table">
        <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Sale</th><th>Max/User</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>
                    @if($item->image)
                        <img src="{{ $item->image }}" alt="{{ $item->name }}" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
                    @else
                        <div style="width:48px;height:48px;border-radius:6px;background:#222;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-image" style="color:#555;"></i>
                        </div>
                    @endif
                </td>
                <td style="color:white;font-weight:600;">{{ $item->name }}</td>
                <td>
                    @if($item->category)
                        <span style="display:inline-flex;align-items:center;gap:6px;">
                            <span style="width:10px;height:10px;border-radius:50%;background:{{ $item->category->color }};display:inline-block;"></span>
                            {{ $item->category->name }}
                        </span>
                    @else
                        <span style="color:#555;">—</span>
                    @endif
                </td>
                <td>€{{ number_format($item->price, 2) }}</td>
                <td>{{ $item->sale > 0 ? $item->sale . '%' : '—' }}</td>
                <td>{{ $item->global_max_order ?? '∞' }}</td>
                <td>
                    <a href="{{ route('admin.products.edit', $item) }}" class="btn-admin btn-admin-secondary"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('admin.products.destroy', $item) }}" style="display:inline;" onsubmit="return confirm('Delete this product?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-admin btn-admin-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
