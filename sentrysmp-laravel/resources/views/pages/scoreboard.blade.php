@extends('layouts.app')
@section('title', 'Scoreboard — SentrySMP')
@section('content')

<div class="btn-group mt-3" role="group">
    @foreach(['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'alltime' => 'All Time'] as $key => $label)
    <a href="{{ route('scoreboard', ['period' => $key]) }}"
       class="btn btn-outline-primary {{ $period === $key ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

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
                        <img src="https://minotar.net/avatar/{{ urlencode($entry->MinecraftUsername) }}/24"
                             style="width:24px;height:24px;border-radius:4px;image-rendering:pixelated;"
                             onerror="this.src='https://minotar.net/avatar/steve/24'" alt="{{ $entry->MinecraftUsername }}">
                        <span>{{ $entry->MinecraftUsername }}</span>
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
