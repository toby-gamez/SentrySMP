@extends('layouts.app')
@section('title', 'Profile — SentrySMP')
@section('content')

@if(isset($username) && $username)

<button class="secondary" onclick="history.length > 1 ? history.back() : window.location='{{ route('active-players') }}'" style="margin-bottom:16px;">
    <i class="bi bi-arrow-left"></i> Back
</button>

@php
    $ap = $activePlayer ?? null;
    $cleanRank = $ap?->rank ? trim(preg_replace('/&[0-9a-fk-or]/i', '', $ap->rank)) : null;
    $purchaseCount = $transactions->filter(fn($t) =>
        $t->amount > 0 && $t->status &&
        (stripos($t->status, 'captured') !== false || stripos($t->status, 'succeeded') !== false || stripos($t->status, 'paid') !== false)
    )->count();
    $totalSpent = $transactions->filter(fn($t) =>
        $t->amount > 0 && $t->status &&
        (stripos($t->status, 'captured') !== false || stripos($t->status, 'succeeded') !== false || stripos($t->status, 'paid') !== false)
    )->sum('amount');
@endphp

<div class="login-card profile-card">
    <div class="login-card-inner">
        <div class="login-skin-panel">
            <img src="https://minotar.net/helm/{{ urlencode($username) }}/100"
                 alt="{{ $username }}"
                 onerror="this.src='https://minotar.net/helm/MHF_Steve/100'">
            <div class="login-skin-name">{{ $username }}</div>
            @if($cleanRank)
                <span class="role-badge badge" style="background-color:{{ $rankHex ?? '#666' }};">{{ $cleanRank }}</span>
            @else
                <div class="login-skin-badge">Player</div>
            @endif
        </div>

        <div class="login-form-panel">
            <div class="profile-stats">
                @if($ap && !$ap->error)
                    <div class="profile-stat-item">
                        <span class="profile-stat-label"><i class="bi bi-coin"></i> Coins</span>
                        <span class="profile-stat-value">{{ number_format($ap->coins) }}</span>
                    </div>
                    <div class="profile-stat-item">
                        <span class="profile-stat-label"><i class="bi bi-currency-dollar"></i> Money</span>
                        <span class="profile-stat-value">${{ number_format($ap->money, 2) }}</span>
                    </div>
                @endif

                @if($purchaseCount > 0)
                    <div class="profile-stat-item">
                        <span class="profile-stat-label"><i class="bi bi-receipt"></i> Purchases</span>
                        <span class="profile-stat-value">{{ $purchaseCount }}</span>
                    </div>
                    <div class="profile-stat-item">
                        <span class="profile-stat-label"><i class="bi bi-currency-euro"></i> Total Spent</span>
                        <span class="profile-stat-value">€{{ number_format($totalSpent, 2) }}</span>
                    </div>
                @endif

                @if($ap && !$ap->error)
                    @php
                        $h = intdiv($ap->play_time_seconds, 3600);
                        $m = intdiv($ap->play_time_seconds % 3600, 60);
                        $playTimeStr = ($h > 0 ? "{$h}h " : '') . ($m > 0 ? "{$m}m" : ($h === 0 ? '0m' : ''));
                    @endphp
                    <div class="profile-stat-item">
                        <span class="profile-stat-label"><i class="bi bi-clock"></i> Play time</span>
                        <span class="profile-stat-value">{{ trim($playTimeStr) ?: '0m' }}</span>
                    </div>
                    <div class="profile-stat-item">
                        <span class="profile-stat-label"><i class="bi bi-emoji-dizzy"></i> Deaths</span>
                        <span class="profile-stat-value">{{ number_format($ap->deaths) }}</span>
                    </div>
                    <div class="profile-stat-item">
                        <span class="profile-stat-label"><i class="bi bi-crosshair"></i> Player kills</span>
                        <span class="profile-stat-value">{{ number_format($ap->player_kills) }}</span>
                    </div>
                    <div class="profile-stat-item">
                        <span class="profile-stat-label"><i class="bi bi-geo-alt"></i> Blocks travelled</span>
                        <span class="profile-stat-value">{{ number_format($ap->blocks_travelled) }}</span>
                    </div>
                @elseif(!$ap && $purchaseCount === 0)
                    <p style="color:#9e9e9e;margin:0;">No data found for this player.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@if($transactions->isNotEmpty())
<h2 class="mt-4">Purchase history</h2>
<div class="table-wrapper" style="margin-top:12px;">
    <table class="table table-sm table-striped">
        <thead>
            <tr>
                <th>Amount</th>
                <th>Provider</th>
                <th>Status</th>
                <th class="text-end">Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $tx)
            <tr>
                <td style="color:#23d05e;font-weight:700;font-family:monospace;">€{{ number_format($tx->amount, 2) }}</td>
                <td>{{ ucfirst($tx->provider ?? '') }}</td>
                <td style="color:#888;">{{ $tx->status }}</td>
                <td class="text-end" style="color:#555;font-size:12px;">
                    {{ $tx->created_at ? $tx->created_at->format('d.m.Y H:i') : '—' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@else

<div class="login-card" style="max-width:480px;">
    <div class="login-skin-panel">
        <img src="https://minotar.net/helm/MHF_Steve/100" alt="Skin preview" class="login-skin-img">
        <div class="login-skin-name">Minecraft Player</div>
        <div class="login-skin-badge">Not logged in</div>
    </div>
    <div class="login-form-panel">
        <form method="GET" action="{{ route('profile') }}" style="display:flex;gap:10px;flex-direction:column;">
            <p style="color:#9e9e9e;margin:0 0 12px;">Enter a Minecraft username to view their profile.</p>
            <div style="display:flex;gap:8px;">
                <input type="text" name="username" value="{{ old('username') }}"
                       placeholder="Minecraft username..."
                       style="flex:1;background:#111;border:1px solid #333;color:#ccc;padding:10px 14px;border-radius:8px;font-size:15px;">
                <button type="submit" class="great-button" style="white-space:nowrap;">Search</button>
            </div>
        </form>
    </div>
</div>

@endif

@endsection
