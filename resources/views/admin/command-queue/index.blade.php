{{--
    Command Queue
    Variables: $items (LengthAwarePaginator<CommandQueue>),
               $status (?string active status filter), $player (?string active player filter)
    Lets admins manually override command statuses (mark executed / failed / reset to pending).
    Filter persists in GET params so the URL is shareable.
--}}
@extends('layouts.admin')
@section('title', 'Command Queue')
@section('content')
{{-- ── Page Guide ─────────────────────────────────────────────────────── --}}
<div style="background:#0b1929;border:1px solid #1e3a5f;border-left:3px solid #3b82f6;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:13px;">
    <div onclick="var n=this.nextElementSibling;n.hidden=!n.hidden;this.querySelector('.pg-ch').style.transform=n.hidden?'':'rotate(180deg)'"
         style="display:flex;align-items:center;gap:8px;cursor:pointer;user-select:none;">
        <i class="bi bi-book" style="color:#60a5fa;font-size:14px;"></i>
        <strong style="color:#93c5fd;">Command Queue Guide</strong>
        <i class="bi bi-chevron-down pg-ch" style="color:#60a5fa;margin-left:auto;font-size:12px;transition:transform .2s;"></i>
    </div>
    <div hidden style="margin-top:12px;color:#94a3b8;line-height:1.75;">
        <p style="margin:0 0 6px;">The command queue holds every in-game command waiting to be delivered after a purchase.</p>
        <p style="margin:0 0 6px;"><strong style="color:#c4d4e8;">Status meanings:</strong></p>
        <ul style="margin:0 0 8px;padding-left:18px;">
            <li><strong style="color:#c4d4e8;">pending</strong> — scheduled, not yet sent to the game server.</li>
            <li><strong style="color:#c4d4e8;">delivered</strong> — sent via the server API, awaiting execution confirmation.</li>
            <li><strong style="color:#c4d4e8;">executed</strong> — confirmed as successfully run on the server.</li>
            <li><strong style="color:#c4d4e8;">failed</strong> — delivery failed (e.g. server unreachable or RCON error).</li>
        </ul>
        <p style="margin:0 0 6px;"><strong style="color:#c4d4e8;">Manual overrides (action buttons):</strong></p>
        <ul style="margin:0;padding-left:18px;">
            <li><strong style="color:#4ade80;">✓ Mark executed</strong> — use when you've manually run the command in-game.</li>
            <li><strong style="color:#f87171;">✗ Mark failed</strong> — use to permanently mark a command as failed.</li>
            <li><strong style="color:#c4d4e8;">↺ Reset to pending</strong> — re-queues a failed or executed command for automatic re-delivery.</li>
        </ul>
    </div>
</div>
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
