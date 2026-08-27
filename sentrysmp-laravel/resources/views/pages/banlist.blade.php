@extends('layouts.app')
@section('title', 'Ban List — SentrySMP')
@section('content')

@php $bans = $bans ?? collect(); @endphp

<div class="main-wrapper">
<p class="main">Ban List</p>
</div>

<div class="info">
@if($bans->isEmpty())
    <p style="color:#666;">No banned players.</p>
@else
    <p style="color:#888;margin-bottom:16px;">{{ $bans->count() }} banned player{{ $bans->count() === 1 ? '' : 's' }}</p>
    <ul class="ban-list">
        @foreach($bans as $ban)
        <li class="ban-item">
            <div class="ban-item-header">
                <img src="https://minotar.net/helm/{{ urlencode($ban->player) }}/32"
                     alt="{{ $ban->player }}"
                     class="ban-avatar skin-img"
                     onerror="this.src='https://minotar.net/helm/MHF_Steve/32'">
                <div>
                    <span class="ban-name">{{ $ban->player }}</span>
                    @if($ban->uuid)
                        <div style="font-size:11px;color:#555;font-family:monospace;">{{ $ban->uuid }}</div>
                    @endif
                </div>
            </div>
            @if($ban->reason)
                <div class="ban-reason"><i class="bi bi-slash-circle"></i> {{ $ban->reason }}</div>
            @endif
        </li>
        @endforeach
    </ul>
@endif
</div>

@endsection
