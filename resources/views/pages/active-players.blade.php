@extends('layouts.app')
@section('title', 'Active Players — SentrySMP')
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
                $cleanRank = $player->rank ? trim(stripMcFormat($player->rank)) : null;
                $rankHex   = $cleanRank ? ($rankColors->get(strtolower($cleanRank), null)) : null;
                $profileUrl = route('profile', ['username' => $player->username]);
            @endphp
            <li class="ban-item" style="cursor:pointer;" onclick="window.location='{{ $profileUrl }}'">
                <div class="ban-item-header">
                    <img src="https://minotar.net/helm/{{ urlencode($player->username) }}/32"
                         alt="{{ $player->username }}"
                         class="ban-avatar"
                         onerror="this.src='https://minotar.net/helm/MHF_Steve/32'">
                    <div>
                        @if($cleanRank)
                            <span class="role-badge badge" style="background-color:{{ $rankHex ?? '#666' }};">{{ $cleanRank }}</span>
                        @endif
                        <span class="ban-name">{{ $player->username }}</span>
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
