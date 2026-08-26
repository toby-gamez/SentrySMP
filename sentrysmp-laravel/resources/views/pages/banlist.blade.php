@extends('layouts.app')
@section('title', 'Ban List — SentrySMP')
@section('content')

@php $bans = $bans ?? []; @endphp

@if(count($bans) === 0)
    <div class="alert alert-info">No banned players.</div>
@else
<ul class="ban-list">
    @foreach($bans as $ban)
    @php
        $name   = is_array($ban) ? ($ban['player'] ?? $ban['name'] ?? 'Unknown') : $ban;
        $reason = is_array($ban) ? ($ban['reason'] ?? '') : '';
    @endphp
    <li class="ban-item">
        <div class="ban-item-header">
            <img src="https://minotar.net/helm/{{ urlencode($name) }}/32"
                 alt="{{ $name }}"
                 class="ban-avatar skin-img"
                 onerror="this.src='https://minotar.net/helm/MHF_Steve/32'">
            <span class="ban-name">{{ $name }}</span>
        </div>
        @if($reason)
            <div class="ban-reason"><i class="bi bi-slash-circle"></i> {{ $reason }}</div>
        @endif
    </li>
    @endforeach
</ul>
<p class="text-muted mb-3">{{ count($bans) }} banned player{{ count($bans) === 1 ? '' : 's' }}</p>
@endif

@endsection
