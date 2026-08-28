@extends('layouts.app')
@section('title', 'Ban List — SentrySMP')
@push('head')
<style>
.ban-item { cursor:default; }
.ban-item.ban-item--clickable:hover { background:#2a2a2a; cursor:pointer; }
</style>
@endpush
@section('content')

@php $bans = $bans ?? collect(); @endphp

<div class="main-wrapper">
    <p class="main">Ban List</p>
</div>

<div class="info">
    @if($bans->isEmpty())
        <div class="ban-empty">
            <i class="bi bi-shield-check ban-empty-icon"></i>
            <p>No banned players.</p>
        </div>
    @else
        <p class="ban-count">{{ $bans->count() }} banned player{{ $bans->count() === 1 ? '' : 's' }}</p>
        <ul class="ban-list">
            @foreach($bans as $ban)
            <li class="ban-item ban-item--clickable"
                style="border-left:none;"
                onclick="window.location='{{ route('profile', ['username' => $ban->player]) }}'">
                <div class="ban-item-body" style="align-items:center;">
                    <img src="https://minotar.net/helm/{{ urlencode($ban->player) }}/48"
                         alt="{{ $ban->player }}"
                         class="ban-avatar skin-img"
                         onerror="this.src='https://minotar.net/helm/MHF_Steve/48'">
                    <div class="ban-info">
                        <div class="ban-info-top" style="flex-wrap:nowrap;gap:0.75rem;">
                            <span class="ban-name" style="flex-shrink:0;">{{ $ban->player }}</span>
                            @if($ban->reason)
                                <span class="ban-reason" style="margin:0;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    <i class="bi bi-slash-circle"></i> {{ $ban->reason }}
                                </span>
                            @endif
                            @if($ban->banner)
                                <span class="ban-meta-item" style="flex-shrink:0;"><i class="bi bi-person"></i> {{ $ban->banner }}</span>
                            @endif
                            @if($ban->banned_ago)
                                <span class="ban-meta-item" style="flex-shrink:0;"><i class="bi bi-clock-history"></i> {{ $ban->banned_ago }}</span>
                            @endif
                            @if($ban->expires_at)
                                <span class="ban-meta-item ban-meta-item--expires" style="flex-shrink:0;"><i class="bi bi-hourglass-split"></i> {{ $ban->expires_at }}</span>
                            @else
                                <span class="ban-meta-item ban-meta-item--permanent" style="flex-shrink:0;"><i class="bi bi-infinity"></i> Permanent</span>
                            @endif
                        </div>
                    </div>
                </div>
            </li>
            @endforeach
        </ul>
    @endif
</div>

@endsection
