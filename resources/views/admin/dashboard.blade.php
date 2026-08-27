{{--
    Admin Dashboard
    Variables: $totalTransactions (int), $totalRevenue (float), $pendingCommands (int),
               $recentTransactions (Collection<Transaction>)
    Shows at-a-glance stats and the 10 most recent transactions.
    The pending-commands card turns yellow when there are undelivered commands.
--}}
@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
{{-- ── Page Guide ─────────────────────────────────────────────────────── --}}
<div style="background:#0b1929;border:1px solid #1e3a5f;border-left:3px solid #3b82f6;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:13px;">
    <div onclick="var n=this.nextElementSibling;n.hidden=!n.hidden;this.querySelector('.pg-ch').style.transform=n.hidden?'':'rotate(180deg)'"
         style="display:flex;align-items:center;gap:8px;cursor:pointer;user-select:none;">
        <i class="bi bi-book" style="color:#60a5fa;font-size:14px;"></i>
        <strong style="color:#93c5fd;">Dashboard Guide</strong>
        <i class="bi bi-chevron-down pg-ch" style="color:#60a5fa;margin-left:auto;font-size:12px;transition:transform .2s;"></i>
    </div>
    <div hidden style="margin-top:12px;color:#94a3b8;line-height:1.75;">
        <ul style="margin:0;padding-left:18px;">
            <li><strong style="color:#c4d4e8;">Total Transactions</strong> — number of orders placed across all statuses.</li>
            <li><strong style="color:#c4d4e8;">Total Revenue</strong> — sum of all <em>SUCCEEDED</em> payment amounts.</li>
            <li><strong style="color:#c4d4e8;">Pending Commands</strong> — in-game delivery commands waiting to be sent. If this is non-zero, some buyers haven't received their items yet — click the link to investigate via the Command Queue.</li>
            <li><strong style="color:#c4d4e8;">Quick Actions</strong> — shortcuts to the most common admin tasks.</li>
            <li><strong style="color:#c4d4e8;">Recent Transactions</strong> — the 10 latest orders. Click any row to view full details including the purchased products and delivery status.</li>
        </ul>
    </div>
</div>
<div class="stat-cards-row">
    <div class="stat-card stat-card-blue">
        <div class="stat-card-label"><i class="bi bi-credit-card"></i> Total Transactions</div>
        <div class="stat-card-value">{{ number_format($totalTransactions) }}</div>
    </div>
    <div class="stat-card stat-card-green">
        <div class="stat-card-label"><i class="bi bi-cash-coin"></i> Total Revenue</div>
        <div class="stat-card-value">€{{ number_format($totalRevenue, 2) }}</div>
    </div>
    <div class="stat-card {{ $pendingCommands > 0 ? 'stat-card-yellow' : '' }}">
        <div class="stat-card-label"><i class="bi bi-list-task"></i> Pending Commands</div>
        <div class="stat-card-value" style="{{ $pendingCommands > 0 ? 'color:#ffc107' : '' }}">{{ $pendingCommands }}</div>
        @if($pendingCommands > 0)
            <div style="margin-top:6px;">
                <a href="{{ route('admin.command-queue.index') }}?status=pending" style="font-size:11px;text-decoration:none;">
                    View pending <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        @endif
    </div>
</div>

{{-- Quick Actions --}}
<div class="admin-card" style="margin-bottom:20px;">
    <div class="admin-card-header" style="margin-bottom:0;">
        <h2 class="admin-card-title">Quick Actions</h2>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:16px;">
        <a href="{{ route('admin.products.create') }}" class="btn-admin btn-admin-primary"><i class="bi bi-plus-lg"></i> Add Product</a>
        <a href="{{ route('admin.categories.create') }}" class="btn-admin btn-admin-secondary"><i class="bi bi-tags-fill"></i> Add Category</a>
        <a href="{{ route('admin.vouchers.create') }}" class="btn-admin btn-admin-secondary"><i class="bi bi-ticket-fill"></i> Add Voucher</a>
        <a href="{{ route('admin.transactions.index') }}" class="btn-admin btn-admin-secondary"><i class="bi bi-credit-card-fill"></i> Transactions</a>
        <a href="{{ route('admin.command-queue.index') }}" class="btn-admin btn-admin-secondary"><i class="bi bi-list-task"></i> Command Queue</a>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">Recent Transactions</h2>
        <a href="{{ route('admin.transactions.index') }}" class="btn-admin btn-admin-secondary">View All</a>
    </div>
    @if($recentTransactions->isEmpty())
        <div style="text-align:center;padding:32px 0;color:#555;">
            <i class="bi bi-inbox" style="font-size:32px;display:block;margin-bottom:8px;"></i>
            No transactions yet.
        </div>
    @else
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Player</th>
                    <th>Provider</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentTransactions as $tx)
                <tr class="admin-table-row-link" data-href="{{ route('admin.transactions.show', $tx) }}">
                    <td style="color:#555;font-size:12px;">#{{ $tx->id }}</td>
                    <td style="color:white;font-weight:600;">{{ $tx->minecraft_username ?: '—' }}</td>
                    <td style="color:#888;">{{ $tx->provider }}</td>
                    <td>€{{ number_format($tx->amount, 2) }}</td>
                    <td>@include('admin._partials.tx-status', ['status' => $tx->status])</td>
                    <td style="color:#666;font-size:12px;white-space:nowrap;">{{ $tx->created_at?->format('Y-m-d H:i') }}</td>
                    <td><a href="{{ route('admin.transactions.show', $tx) }}" class="btn-admin btn-admin-secondary" style="padding:4px 10px;"><i class="bi bi-eye"></i></a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
