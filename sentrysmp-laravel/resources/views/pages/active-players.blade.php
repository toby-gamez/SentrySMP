@extends('layouts.app')
@section('title', 'Active Players — SentrySMP')
@section('content')
<div class="main-wrapper">
<p class="main">Active Players</p>
</div>

<div class="info">
    @php $players = $players ?? []; @endphp
    @if(count($players) > 0)
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;">
            @foreach($players as $player)
            <div style="text-align:center;background:#111;border:1px solid #222;border-radius:8px;padding:12px;">
                <img src="https://minotar.net/avatar/{{ urlencode($player) }}/48"
                     alt="{{ $player }}"
                     style="width:48px;height:48px;border-radius:6px;image-rendering:pixelated;"
                     onerror="this.src='https://minotar.net/avatar/steve/48'">
                <div style="font-size:13px;color:#ccc;margin-top:8px;word-break:break-all;">{{ $player }}</div>
            </div>
            @endforeach
        </div>
    @else
        <p style="color:#666;">No players online right now.</p>
    @endif
</div>
@endsection
