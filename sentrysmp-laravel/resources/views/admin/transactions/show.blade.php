@extends('layouts.admin')
@section('title', 'Transaction #' . $transaction->Id)
@section('content')

@php
$cartItems = [];
if ($transaction->ItemsJson) {
    try { $cartItems = json_decode($transaction->ItemsJson, true, 512, JSON_THROW_ON_ERROR); }
    catch (\Throwable) { $cartItems = []; }
}
$typeColors = [
    'Key'        => ['bg' => '#1a2f1a', 'color' => '#4ade80'],
    'Coin'       => ['bg' => '#2a2010', 'color' => '#fbbf24'],
    'Bundle'     => ['bg' => '#1a1a3a', 'color' => '#818cf8'],
    'Rank'       => ['bg' => '#1a2030', 'color' => '#60a5fa'],
    'BattlePass' => ['bg' => '#2a1a2a', 'color' => '#e879f9'],
    'Other'      => ['bg' => '#1e1e1e', 'color' => '#9ca3af'],
    'Shard'      => ['bg' => '#1a2a2a', 'color' => '#22d3ee'],
];
@endphp

{{-- Top row: Details + Products side-by-side --}}
<div style="display:grid;grid-template-columns:320px 1fr;gap:20px;margin-bottom:20px;align-items:start;">

    {{-- Transaction Details --}}
    <div class="admin-card">
        <div style="font-size:11px;font-weight:700;color:#555;letter-spacing:.1em;text-transform:uppercase;margin-bottom:16px;">Transaction Details</div>

        @php
        $rows = [
            'ID'       => ['value' => '#' . $transaction->Id, 'style' => 'font-weight:700;font-size:18px;color:#fff;'],
            'Provider' => ['value' => $transaction->Provider],
            'TX ID'    => ['value' => $transaction->ProviderTransactionId, 'style' => 'font-size:11px;font-family:monospace;color:#888;word-break:break-all;'],
            'Player'   => ['value' => $transaction->MinecraftUsername ?: '—', 'style' => 'font-weight:700;color:#fff;'],
            'Amount'   => ['value' => '€' . number_format($transaction->Amount, 2) . ' ' . $transaction->Currency, 'style' => 'font-weight:700;font-size:16px;color:#fff;'],
        ];
        @endphp

        @foreach($rows as $label => $row)
        <div style="display:flex;flex-direction:column;gap:2px;padding:10px 0;border-bottom:1px solid #1e1e1e;">
            <span style="font-size:10px;font-weight:600;color:#555;text-transform:uppercase;letter-spacing:.08em;">{{ $label }}</span>
            <span style="{{ $row['style'] ?? 'color:#ccc;font-size:14px;' }}">{{ $row['value'] }}</span>
        </div>
        @endforeach

        <div style="display:flex;flex-direction:column;gap:2px;padding:10px 0;border-bottom:1px solid #1e1e1e;">
            <span style="font-size:10px;font-weight:600;color:#555;text-transform:uppercase;letter-spacing:.08em;">Status</span>
            <div style="padding-top:4px;">@include('admin._partials.tx-status', ['status' => $transaction->Status])</div>
        </div>

        <div style="display:flex;flex-direction:column;gap:2px;padding:10px 0;">
            <span style="font-size:10px;font-weight:600;color:#555;text-transform:uppercase;letter-spacing:.08em;">Created</span>
            <span style="color:#888;font-size:13px;">{{ $transaction->CreatedAt?->format('Y-m-d H:i:s') }}</span>
        </div>
    </div>

    {{-- Purchased Products --}}
    <div class="admin-card">
        <div style="font-size:11px;font-weight:700;color:#555;letter-spacing:.1em;text-transform:uppercase;margin-bottom:16px;">
            Purchased Products <span style="color:#444;">({{ count($cartItems) }})</span>
        </div>

        @if(empty($cartItems))
            <p style="color:#555;">No product data available.</p>
        @else
        <div style="display:flex;flex-direction:column;gap:10px;">
            @foreach($cartItems as $lineItem)
            @php
                $p    = $lineItem['product'] ?? $lineItem;
                $qty  = $lineItem['quantity'] ?? 1;
                $type = $p['type'] ?? '';
                $tc   = $typeColors[$type] ?? ['bg' => '#1e1e1e', 'color' => '#9ca3af'];
                $salePrice = ($p['sale'] ?? 0) > 0
                    ? $p['price'] * (1 - $p['sale'] / 100)
                    : ($p['price'] ?? 0);
            @endphp
            <div style="display:flex;gap:12px;align-items:center;background:#111;border:1px solid #2a2a2a;border-radius:8px;padding:12px 14px;">
                @if(!empty($p['image']))
                    <img src="{{ $p['image'] }}" alt="{{ $p['name'] ?? '' }}"
                         style="width:52px;height:52px;object-fit:cover;border-radius:6px;flex-shrink:0;background:#1a1a1a;">
                @else
                    <div style="width:52px;height:52px;border-radius:6px;background:#1a1a1a;flex-shrink:0;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-box" style="color:#333;font-size:20px;"></i>
                    </div>
                @endif

                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <span style="font-weight:700;color:#fff;font-size:14px;">{{ $p['name'] ?? 'Unknown' }}</span>
                        @if($type)
                        <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:4px;background:{{ $tc['bg'] }};color:{{ $tc['color'] }};">{{ $type }}</span>
                        @endif
                    </div>
                    @if(!empty($p['description']))
                    <div style="color:#555;font-size:12px;margin-top:3px;">{{ $p['description'] }}</div>
                    @endif
                </div>

                <div style="text-align:right;flex-shrink:0;margin-left:12px;">
                    @if(($p['sale'] ?? 0) > 0)
                    <div style="color:#666;font-size:11px;text-decoration:line-through;">€{{ number_format($p['price'], 2) }}</div>
                    @endif
                    <div style="color:#fff;font-weight:700;font-size:15px;">€{{ number_format($salePrice, 2) }}</div>
                    @if($qty > 1)
                    <div style="color:#666;font-size:11px;">× {{ $qty }}</div>
                    @endif
                </div>
            </div>
            @endforeach

            @php
                $total = collect($cartItems)->sum(function($li) {
                    $p = $li['product'] ?? $li;
                    $sale = $p['sale'] ?? 0;
                    $price = $p['price'] ?? 0;
                    $qty  = $li['quantity'] ?? 1;
                    return ($sale > 0 ? $price * (1 - $sale/100) : $price) * $qty;
                });
            @endphp
            <div style="display:flex;justify-content:flex-end;align-items:center;gap:12px;padding-top:12px;border-top:1px solid #222;margin-top:4px;">
                <span style="color:#555;font-size:13px;text-transform:uppercase;letter-spacing:.06em;">Total</span>
                <span style="color:#fff;font-weight:700;font-size:17px;">€{{ number_format($total, 2) }}</span>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Command Queue --}}
@if($commands->isNotEmpty())
<div class="admin-card" style="margin-bottom:20px;">
    <div style="font-size:11px;font-weight:700;color:#555;letter-spacing:.1em;text-transform:uppercase;margin-bottom:16px;">
        Command Queue <span style="color:#444;">({{ $commands->count() }})</span>
    </div>
    <div style="overflow-x:auto;">
    <table class="admin-table" style="min-width:0;">
        <thead><tr><th>#</th><th>Player</th><th>Command</th><th>Status</th><th>Updated</th></tr></thead>
        <tbody>
            @foreach($commands as $cmd)
            <tr>
                <td style="color:#555;">{{ $cmd->id }}</td>
                <td>{{ $cmd->player_name }}</td>
                <td style="font-family:monospace;font-size:12px;color:#ccc;white-space:normal;word-break:break-all;">{{ $cmd->command_text }}</td>
                <td>@include('admin._partials.tx-status', ['status' => $cmd->status])</td>
                <td style="color:#666;font-size:12px;">{{ $cmd->updated_at?->format('Y-m-d H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>
@endif

<a href="{{ route('admin.transactions.index') }}" class="btn-admin btn-admin-secondary">
    <i class="bi bi-arrow-left"></i> Back to Transactions
</a>
@endsection
