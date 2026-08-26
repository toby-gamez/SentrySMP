@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="stat-cards-row">
    <div class="stat-card">
        <div class="stat-card-label"><i class="bi bi-credit-card"></i> Total Transactions</div>
        <div class="stat-card-value">{{ number_format($totalTransactions) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-label"><i class="bi bi-cash-coin"></i> Total Revenue</div>
        <div class="stat-card-value">€{{ number_format($totalRevenue, 2) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-label"><i class="bi bi-list-task"></i> Pending Commands</div>
        <div class="stat-card-value" style="{{ $pendingCommands > 0 ? 'color:#ffc107' : '' }}">{{ $pendingCommands }}</div>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">Recent Transactions</h2>
        <a href="{{ route('admin.transactions.index') }}" class="btn-admin btn-admin-secondary">View All</a>
    </div>
    @if($recentTransactions->isEmpty())
        <p style="color:#666;">No transactions yet.</p>
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
                </tr>
            </thead>
            <tbody>
                @foreach($recentTransactions as $tx)
                <tr>
                    <td>#{{ $tx->id }}</td>
                    <td>{{ $tx->minecraft_username ?: '—' }}</td>
                    <td>{{ $tx->provider }}</td>
                    <td>€{{ number_format($tx->amount, 2) }} {{ $tx->currency }}</td>
                    <td><span class="badge" style="background:#1e3a20;color:#6be895;">{{ $tx->status }}</span></td>
                    <td style="color:#666;font-size:12px;">{{ $tx->created_at?->format('Y-m-d H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
