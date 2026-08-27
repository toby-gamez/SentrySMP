@extends('layouts.app')
@section('title', 'Our Team — SentrySMP')
@section('content')

@if($categories->isEmpty())
    <p><em>No team members configured yet.</em></p>
@else
    @foreach($categories as $category)
    <div class="section">
        <h2 class="section-title">{{ $category->Name }}</h2>
        <div class="team-grid">
            @foreach($category->members->sortBy('SortOrder') as $member)
            <div class="team-item">
                <div class="avatar">
                    @php
                        $skinSrc = !empty($member->SkinUrl)
                            ? $member->SkinUrl
                            : 'https://minotar.net/helm/' . urlencode($member->MinecraftName ?? 'steve') . '/100';
                    @endphp
                    <img src="{{ $skinSrc }}" class="skin-preview" alt="{{ $member->MinecraftName }}" width="75" height="75"
                         onerror="this.src='https://minotar.net/helm/steve/100'">
                </div>
                <h5 class="person">{{ $member->MinecraftName }}</h5>
                @if($member->rank)
                    @php $hex = !empty($member->rank->HexColor) ? $member->rank->HexColor : '#666'; @endphp
                    <span class="role-badge badge" style="background-color: {{ $hex }}">{{ $member->rank->Name }}</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
@endif

@endsection
