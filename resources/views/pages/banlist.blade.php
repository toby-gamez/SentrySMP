@extends('layouts.app')
@section('title', 'Ban List — SentrySMP')
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
            <li class="ban-item {{ $ban->active ? 'ban-item--active' : 'ban-item--inactive' }}">
                <div class="ban-item-body">
                    <img src="https://minotar.net/helm/{{ urlencode($ban->player) }}/48"
                         alt="{{ $ban->player }}"
                         class="ban-avatar skin-img"
                         onerror="this.src='https://minotar.net/helm/MHF_Steve/48'">
                    <div class="ban-info">
                        <div class="ban-info-top">
                            <span class="ban-name">{{ $ban->player }}</span>
                            @if($ban->active)
                                <span class="ban-badge ban-badge--active">Active</span>
                            @else
                                <span class="ban-badge ban-badge--inactive">Inactive</span>
                            @endif
                        </div>
                        @if($ban->uuid)
                            <div class="ban-uuid">{{ $ban->uuid }}</div>
                        @endif
                        @if($ban->reason)
                            <div class="ban-reason">
                                <i class="bi bi-slash-circle"></i> {{ $ban->reason }}
                            </div>
                        @endif
                        <div class="ban-meta">
                            @if($ban->banner)
                                <span class="ban-meta-item">
                                    <i class="bi bi-person"></i> {{ $ban->banner }}
                                </span>
                            @endif
                            @if($ban->banned_ago)
                                <span class="ban-meta-item">
                                    <i class="bi bi-clock-history"></i> {{ $ban->banned_ago }}
                                </span>
                            @endif
                            @if($ban->expires_at)
                                <span class="ban-meta-item ban-meta-item--expires">
                                    <i class="bi bi-hourglass-split"></i> {{ $ban->expires_at }}
                                </span>
                            @else
                                <span class="ban-meta-item ban-meta-item--permanent">
                                    <i class="bi bi-infinity"></i> Permanent
                                </span>
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
