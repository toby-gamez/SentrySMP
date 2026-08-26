@extends('layouts.admin')
@section('title', 'Battle Passes')
@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">Battle Passes ({{ $items->count() }})</h2>
        <a href="{{ route('admin.battlepasses.create') }}" class="btn-admin btn-admin-primary"><i class="bi bi-plus-lg"></i> Add Battle Pass</a>
    </div>
    @if($items->isEmpty())
        <p style="color:#666;">No battle passes yet.</p>
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
                        <span style="color:#555;">—</span>
                    @endif
                </td>
                <td style="color:white;font-weight:600;">{{ $item->Name }}</td>
                <td>€{{ number_format($item->Price, 2) }}</td>
                <td>{{ $item->Sale > 0 ? $item->Sale . '%' : '—' }}</td>
                <td>{{ $item->GlobalMaxOrder ?? '∞' }}</td>
                <td>
                    <a href="{{ route('admin.battlepasses.edit', $item) }}" class="btn-admin btn-admin-secondary"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('admin.battlepasses.destroy', $item) }}" style="display:inline;" onsubmit="return confirm('Delete?')">
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
