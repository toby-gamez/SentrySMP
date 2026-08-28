@extends('layouts.app')
@section('title', 'Scoreboard — SentrySMP')
@section('content')

<div class="scoreboard-tabs mt-3">
    @foreach(['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'alltime' => 'All Time'] as $key => $label)
    <a href="{{ route('scoreboard', ['period' => $key]) }}"
       class="scoreboard-tab {{ $period === $key ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

<style>
.scoreboard-tabs {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
.scoreboard-tab {
    padding: 7px 18px;
    border-radius: 6px;
    border: 1px solid #2a2a2a;
    background: #111;
    color: #888;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    transition: background .15s, color .15s, border-color .15s;
}
.scoreboard-tab:hover {
    background: #1a1a1a;
    color: #ccc;
    border-color: #444;
}
.scoreboard-tab.active {
    background: #23d05e18;
    color: #23d05e;
    border-color: #23d05e55;
}
</style>

@if($entries->isEmpty())
    <div class="alert alert-info mt-3">No payments yet.</div>
@else
<div class="table-wrapper mt-3">
    <table class="table table-sm table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Player</th>
                <th class="text-end">Paid (€)</th>
                <th class="text-center">Transactions</th>
                <th>Last Payment</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entries as $i => $entry)
            <tr>
                <td style="color:{{ $i === 0 ? '#ffd700' : ($i === 1 ? '#c0c0c0' : ($i === 2 ? '#cd7f32' : '#555')) }};font-weight:700;">
                    #{{ $i + 1 }}
                </td>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <img src="https://minotar.net/avatar/{{ urlencode($entry->minecraft_username) }}/24"
                             style="width:24px;height:24px;border-radius:4px;image-rendering:pixelated;"
                             onerror="this.src='https://minotar.net/avatar/steve/24'" alt="{{ $entry->minecraft_username }}">
                        <span>{{ $entry->minecraft_username }}</span>
                    </div>
                </td>
                <td class="text-end" style="color:#23d05e;font-weight:700;font-family:monospace;">
                    €{{ number_format($entry->total_paid, 2) }}
                </td>
                <td class="text-center">{{ $entry->transaction_count }}</td>
                <td style="color:#888;font-size:13px;">
                    {{ $entry->last_payment ? \Carbon\Carbon::parse($entry->last_payment)->format('d.m.Y H:i') : '—' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@endsection
