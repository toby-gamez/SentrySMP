@extends('layouts.app')
@section('title', 'Profile — SentrySMP')
@section('content')

@if(isset($username) && $username)

<div class="login-card profile-card">
    <div class="login-card-inner">
        <div class="login-skin-panel">
            <img src="https://minotar.net/helm/{{ urlencode($username) }}/100"
                 alt="{{ $username }}"
                 onerror="this.src='https://minotar.net/helm/MHF_Steve/100'">
            <div class="login-skin-name">{{ $username }}</div>
            <div class="login-skin-badge">Player</div>
        </div>
        <div class="login-form-panel">
            @if(isset($transactions) && $transactions->isNotEmpty())
            <div class="profile-stats">
                <div class="profile-stat-item">
                    <span class="profile-stat-label"><i class="bi bi-receipt"></i> Purchases</span>
                    <span class="profile-stat-value">{{ $transactions->count() }}</span>
                </div>
                <div class="profile-stat-item">
                    <span class="profile-stat-label"><i class="bi bi-currency-euro"></i> Total Spent</span>
                    <span class="profile-stat-value">€{{ number_format($transactions->sum('Amount'), 2) }}</span>
                </div>
            </div>
            @else
            <p style="color:#9e9e9e;margin:0;">No purchases found for this player.</p>
            @endif
        </div>
    </div>
</div>

@if(isset($transactions) && $transactions->isNotEmpty())
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
                <td style="color:#23d05e;font-weight:700;font-family:monospace;">€{{ number_format($tx->Amount, 2) }}</td>
                <td>{{ ucfirst($tx->Provider ?? '') }}</td>
                <td style="color:#888;">{{ $tx->Status }}</td>
                <td class="text-end" style="color:#555;font-size:12px;">
                    {{ $tx->CreatedAt ? $tx->CreatedAt->format('d.m.Y H:i') : '—' }}
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
