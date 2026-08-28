@extends('layouts.app')
@section('title', 'Active Players — SentrySMP')
@push('head')
<style>
.player-item:hover { background:#2a2a2a; }
</style>
@endpush

@section('content')
<div class="main-wrapper">
<p class="main">Active Players</p>
</div>

<div class="info">
    @php
        $players    = $players ?? collect();
        $rankColors = $rankColors ?? collect();

        function stripMcFormat(string $s): string {
            return preg_replace('/&[0-9a-fk-or]/i', '', $s);
        }
    @endphp
    @if($players->isNotEmpty())
        <ul class="ban-list">
            @foreach($players as $player)
            @php
                $cleanRank  = $player->rank ? trim(stripMcFormat($player->rank)) : null;
                $rankHex    = $cleanRank ? ($rankColors->get(strtolower($cleanRank), null)) : null;
                $profileUrl = route('profile', ['username' => $player->username]);
            @endphp
            <li class="ban-item player-item" style="border-left:none;cursor:pointer;" onclick="window.location='{{ $profileUrl }}'">
                <div class="ban-item-body" style="align-items:center;">
                    <img src="https://minotar.net/helm/{{ urlencode($player->username) }}/48"
                         alt="{{ $player->username }}"
                         class="ban-avatar"
                         onerror="this.src='https://minotar.net/helm/MHF_Steve/48'">
                    <div class="ban-info">
                        <div class="ban-info-top">
                            @if($cleanRank)
                                <span style="background:{{ $rankHex ?? '#444' }};color:#fff;padding:2px 8px;border-radius:4px;font-size:0.75em;"><strong>{{ $cleanRank }}</strong></span>
                            @endif
                            <span class="ban-name">{{ $player->username }}</span>
                        </div>
                    </div>
                </div>
            </li>
            @endforeach
        </ul>
        <p class="text-muted" style="margin-top:12px;">{{ $players->count() }} player{{ $players->count() === 1 ? '' : 's' }} online</p>
    @else
        <p style="color:#666;">No players online right now.</p>
    @endif
</div>
@endsection
