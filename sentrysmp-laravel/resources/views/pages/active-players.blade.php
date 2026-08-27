@extends('layouts.app')
@section('title', 'Active Players — SentrySMP')
@section('content')
<div class="main-wrapper">
<p class="main">Active Players</p>
</div>

<div class="info">
    @php $players = $players ?? collect(); @endphp
    @if($players->isNotEmpty())
        <p style="color:#888;margin-bottom:16px;">{{ $players->count() }} player{{ $players->count() === 1 ? '' : 's' }} online</p>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">
            @foreach($players as $player)
            <div style="background:#111;border:1px solid #222;border-radius:10px;padding:16px;display:flex;flex-direction:column;gap:10px;">

                {{-- Header: avatar + name + rank --}}
                <div style="display:flex;align-items:center;gap:12px;">
                    <img src="https://minotar.net/avatar/{{ urlencode($player->username) }}/48"
                         alt="{{ $player->username }}"
                         style="width:48px;height:48px;border-radius:6px;image-rendering:pixelated;flex-shrink:0;"
                         onerror="this.src='https://minotar.net/avatar/steve/48'">
                    <div>
                        <div style="font-size:15px;font-weight:600;color:#eee;">{{ $player->username }}</div>
                        @if($player->rank)
                            <div style="font-size:12px;color:#f59e0b;margin-top:2px;">{{ $player->rank }}</div>
                        @endif
                        @if($player->error)
                            <div style="font-size:11px;color:#ef4444;margin-top:2px;" title="{{ $player->error }}">⚠ Stats unavailable</div>
                        @endif
                    </div>
                </div>

                @if(!$player->error)
                {{-- Economy --}}
                <div style="display:flex;gap:8px;">
                    <div style="flex:1;background:#1a1a1a;border-radius:6px;padding:8px;text-align:center;">
                        <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:.05em;">Coins</div>
                        <div style="font-size:14px;color:#facc15;font-weight:600;margin-top:2px;">{{ number_format($player->coins) }}</div>
                    </div>
                    <div style="flex:1;background:#1a1a1a;border-radius:6px;padding:8px;text-align:center;">
                        <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:.05em;">Money</div>
                        <div style="font-size:14px;color:#4ade80;font-weight:600;margin-top:2px;">${{ number_format($player->money, 2) }}</div>
                    </div>
                </div>

                {{-- Stats --}}
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;">
                    <div style="background:#1a1a1a;border-radius:6px;padding:7px;text-align:center;">
                        <div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:.04em;">Deaths</div>
                        <div style="font-size:13px;color:#f87171;font-weight:600;margin-top:2px;">{{ number_format($player->deaths) }}</div>
                    </div>
                    <div style="background:#1a1a1a;border-radius:6px;padding:7px;text-align:center;">
                        <div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:.04em;">PvP Kills</div>
                        <div style="font-size:13px;color:#a78bfa;font-weight:600;margin-top:2px;">{{ number_format($player->player_kills) }}</div>
                    </div>
                    <div style="background:#1a1a1a;border-radius:6px;padding:7px;text-align:center;">
                        <div style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:.04em;">Mob Kills</div>
                        <div style="font-size:13px;color:#60a5fa;font-weight:600;margin-top:2px;">{{ number_format($player->mobs_killed) }}</div>
                    </div>
                </div>

                {{-- Playtime + Blocks --}}
                <div style="display:flex;gap:8px;">
                    <div style="flex:1;background:#1a1a1a;border-radius:6px;padding:8px;text-align:center;">
                        <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:.05em;">Play Time</div>
                        @php
                            $h = intdiv($player->play_time_seconds, 3600);
                            $m = intdiv($player->play_time_seconds % 3600, 60);
                        @endphp
                        <div style="font-size:13px;color:#ccc;font-weight:600;margin-top:2px;">{{ $h }}h {{ $m }}m</div>
                    </div>
                    <div style="flex:1;background:#1a1a1a;border-radius:6px;padding:8px;text-align:center;">
                        <div style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:.05em;">Blocks Walked</div>
                        <div style="font-size:13px;color:#ccc;font-weight:600;margin-top:2px;">{{ number_format($player->blocks_travelled) }}</div>
                    </div>
                </div>
                @endif

            </div>
            @endforeach
        </div>
    @else
        <p style="color:#666;">No players online right now.</p>
    @endif
</div>
@endsection
