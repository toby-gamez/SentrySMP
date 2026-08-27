@extends('layouts.admin')
@section('title', 'Categories')
@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">Categories ({{ $items->count() }})</h2>
        <a href="{{ route('admin.categories.create') }}" class="btn-admin btn-admin-primary"><i class="bi bi-plus-lg"></i> Add Category</a>
    </div>
    @if($items->isEmpty())
        <p style="color:#666;">No categories yet.</p>
    @else
    <table class="admin-table">
        <thead><tr><th>Color</th><th>Image</th><th>Name</th><th>Slug</th><th>Products</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>
                    <div style="width:36px;height:36px;border-radius:6px;background:{{ $item->color }};border:2px solid #333;"></div>
                </td>
                <td>
                    @if($item->image)
                        <img src="{{ $item->image }}" alt="{{ $item->name }}" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
                    @else
                        <span style="color:#555;">—</span>
                    @endif
                </td>
                <td style="color:white;font-weight:600;">{{ $item->name }}</td>
                <td style="color:#888;font-size:13px;">{{ $item->slug }}</td>
                <td>{{ $item->products_count }}</td>
                <td>
                    <a href="{{ route('admin.categories.edit', $item) }}" class="btn-admin btn-admin-secondary"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('admin.categories.destroy', $item) }}" style="display:inline;" onsubmit="return confirm('Delete this category and all its products?')">
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
