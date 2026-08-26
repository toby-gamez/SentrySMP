@extends('layouts.admin')
@section('title', 'Transactions')
@section('content')

{{-- Filters --}}
<div class="admin-card" style="margin-bottom:16px;">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:180px;">
            <label style="display:block;font-size:11px;color:#888;margin-bottom:4px;">Search player / TX ID</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Username or transaction ID…"
                   style="width:100%;box-sizing:border-box;">
        </div>
        <div style="min-width:160px;">
            <label style="display:block;font-size:11px;color:#888;margin-bottom:4px;">Status</label>
            <select name="status" style="width:100%;">
                <option value="">All statuses</option>
                @foreach(['SUCCEEDED','FAILED','CANCELLED','RCON_FAILED','TRIED_AGAIN'] as $s)
                    <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        @if($providers->count() > 1)
        <div style="min-width:130px;">
            <label style="display:block;font-size:11px;color:#888;margin-bottom:4px;">Provider</label>
            <select name="provider" style="width:100%;">
                <option value="">All providers</option>
                @foreach($providers as $p)
                    <option value="{{ $p }}" {{ $provider === $p ? 'selected' : '' }}>{{ $p }}</option>
                @endforeach
            </select>
        </div>
        @else
            <input type="hidden" name="provider" value="">
        @endif
        <div>
            <label style="display:block;font-size:11px;color:#888;margin-bottom:4px;">From</label>
            <input type="date" name="date_from" value="{{ $dateFrom }}" style="width:140px;">
        </div>
        <div>
            <label style="display:block;font-size:11px;color:#888;margin-bottom:4px;">To</label>
            <input type="date" name="date_to" value="{{ $dateTo }}" style="width:140px;">
        </div>
        <div style="display:flex;gap:6px;padding-bottom:1px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-funnel"></i> Filter</button>
            @if($search || $status || $provider || $dateFrom || $dateTo)
                <a href="{{ route('admin.transactions.index') }}" class="btn-admin btn-admin-secondary"><i class="bi bi-x"></i> Clear</a>
            @endif
        </div>
    </form>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">Transactions <span style="color:#555;font-size:14px;font-weight:400;">({{ $items->total() }})</span></h2>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Provider</th>
                <th>Player</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Cmds</th>
                <th>Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $tx)
            <tr>
                <td style="color:#555;font-size:12px;">#{{ $tx->Id }}</td>
                <td style="color:#888;">{{ $tx->Provider }}</td>
                <td style="color:white;font-weight:600;">{{ $tx->MinecraftUsername ?: '—' }}</td>
                <td>€{{ number_format($tx->Amount, 2) }}</td>
                <td>@include('admin._partials.tx-status', ['status' => $tx->Status])</td>
                <td style="color:#555;font-size:12px;">{{ $tx->commandQueue->count() }}</td>
                <td style="color:#666;font-size:12px;white-space:nowrap;">{{ $tx->CreatedAt?->format('Y-m-d H:i') }}</td>
                <td>
                    <a href="{{ route('admin.transactions.show', $tx) }}" class="btn-admin btn-admin-secondary" style="padding:4px 10px;"><i class="bi bi-eye"></i></a>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;color:#666;padding:30px;">No transactions found.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($items->hasPages())
    <div style="margin-top:16px;display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
        {{-- Previous --}}
        @if($items->onFirstPage())
            <span class="btn-admin btn-admin-secondary" style="opacity:.3;pointer-events:none;padding:5px 12px;">‹ Prev</span>
        @else
            <a href="{{ $items->previousPageUrl() }}" class="btn-admin btn-admin-secondary" style="padding:5px 12px;">‹ Prev</a>
        @endif

        {{-- Page numbers --}}
        @foreach($items->getUrlRange(max(1, $items->currentPage()-3), min($items->lastPage(), $items->currentPage()+3)) as $page => $url)
            @if($page == $items->currentPage())
                <span class="btn-admin btn-admin-primary" style="padding:5px 12px;min-width:36px;text-align:center;">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="btn-admin btn-admin-secondary" style="padding:5px 12px;min-width:36px;text-align:center;">{{ $page }}</a>
            @endif
        @endforeach

        {{-- Next --}}
        @if($items->hasMorePages())
            <a href="{{ $items->nextPageUrl() }}" class="btn-admin btn-admin-secondary" style="padding:5px 12px;">Next ›</a>
        @else
            <span class="btn-admin btn-admin-secondary" style="opacity:.3;pointer-events:none;padding:5px 12px;">Next ›</span>
        @endif
    </div>
    @endif
</div>
@endsection
