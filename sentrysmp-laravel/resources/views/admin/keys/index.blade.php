@extends('layouts.admin')
@section('title', 'Keys')
@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">Keys ({{ $items->count() }})</h2>
        <a href="{{ route('admin.keys.create') }}" class="btn-admin btn-admin-primary"><i class="bi bi-plus-lg"></i> Add Key</a>
    </div>
    @if($items->isEmpty())
        <p style="color:#666;">No keys yet.</p>
    @else
    <table class="admin-table">
        <thead><tr><th>Image</th><th>Name</th><th>Price</th><th>Sale</th><th>Max/User</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>
                    @if($item->Image)
                        <img src="{{ $item->Image }}" alt="{{ $item->Name }}" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
                    @else
                        <img src="{{ asset('images/red-bundle.png') }}" alt="{{ $item->Name }}" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
                    @endif
                </td>
                <td style="color:white;font-weight:600;">{{ $item->Name }}</td>
                <td>€{{ number_format($item->Price, 2) }}</td>
                <td>{{ $item->Sale > 0 ? $item->Sale . '%' : '—' }}</td>
                <td>{{ $item->GlobalMaxOrder ?? '∞' }}</td>
                <td>
                    <a href="{{ route('admin.keys.edit', $item) }}" class="btn-admin btn-admin-secondary"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('admin.keys.destroy', $item) }}" style="display:inline;" onsubmit="return confirm('Delete this key?')">
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
