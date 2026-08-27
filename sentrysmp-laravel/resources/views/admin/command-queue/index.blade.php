@extends('layouts.admin')
@section('title', 'Command Queue')
@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">Command Queue</h2>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <form method="GET" style="display:flex;gap:8px;align-items:center;">
                <select name="status" onchange="this.form.submit()" style="background:#1c1c1c;border:1px solid #333;color:white;border-radius:8px;padding:8px 12px;font-size:13px;">
                    <option value="">All statuses</option>
                    <option value="pending"   {{ $status === 'pending'   ? 'selected' : '' }}>Pending</option>
                    <option value="delivered" {{ $status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="executed"  {{ $status === 'executed'  ? 'selected' : '' }}>Executed</option>
                    <option value="failed"    {{ $status === 'failed'    ? 'selected' : '' }}>Failed</option>
                </select>
                <input type="text" name="player" value="{{ $player }}" placeholder="Filter by player" style="margin:0;padding:8px 12px;border-radius:8px;width:160px;">
                <button type="submit" class="btn-admin btn-admin-secondary">Filter</button>
            </form>
        </div>
    </div>

    <table class="admin-table">
        <thead><tr><th>ID</th><th>Transaction</th><th>Player</th><th>Command</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
        <tbody>
            @forelse($items as $item)
            <tr>
                <td style="color:#666;">{{ $item->id }}</td>
                <td>
                    @if($item->transaction_id)
                        <a href="{{ route('admin.transactions.show', $item->transaction_id) }}">#{{ $item->transaction_id }}</a>
                    @else —
                    @endif
                </td>
                <td style="color:white;font-weight:600;">{{ $item->player_name }}</td>
                <td style="font-family:monospace;font-size:12px;color:#ccc;max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $item->command_text }}">{{ $item->command_text }}</td>
                <td><span class="badge badge-{{ $item->status }}">{{ $item->status }}</span></td>
                <td style="color:#666;font-size:12px;">{{ $item->created_at?->format('Y-m-d H:i') }}</td>
                <td>
                    @if($item->status !== 'executed')
                    <form method="POST" action="{{ route('admin.command-queue.executed', $item) }}" style="display:inline;">
                        @csrf <button type="submit" class="btn-admin btn-admin-success" title="Mark executed"><i class="bi bi-check-lg"></i></button>
                    </form>
                    @endif
                    @if($item->status !== 'failed')
                    <form method="POST" action="{{ route('admin.command-queue.failed', $item) }}" style="display:inline;">
                        @csrf <button type="submit" class="btn-admin btn-admin-danger" title="Mark failed"><i class="bi bi-x-lg"></i></button>
                    </form>
                    @endif
                    @if($item->status !== 'pending')
                    <form method="POST" action="{{ route('admin.command-queue.pending', $item) }}" style="display:inline;">
                        @csrf <button type="submit" class="btn-admin btn-admin-secondary" title="Reset to pending"><i class="bi bi-arrow-counterclockwise"></i></button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;color:#666;padding:30px;">No commands found.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($items->hasPages())
    <div class="admin-pagination">
        @if($items->onFirstPage())
            <span class="disabled">‹</span>
        @else
            <a href="{{ $items->previousPageUrl() }}">‹</a>
        @endif

        @foreach($items->getUrlRange(max(1, $items->currentPage()-3), min($items->lastPage(), $items->currentPage()+3)) as $page => $url)
            @if($page == $items->currentPage())
                <span class="current">{{ $page }}</span>
            @else
                <a href="{{ $url }}">{{ $page }}</a>
            @endif
        @endforeach

        @if($items->hasMorePages())
            <a href="{{ $items->nextPageUrl() }}">›</a>
        @else
            <span class="disabled">›</span>
        @endif
    </div>
    @endif
</div>
@endsection
