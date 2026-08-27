@extends('layouts.app')

@section('title', 'Home - SentrySMP')

@push('head')
<style>
.latest-announcements { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
.latest-announcements h2 { text-align: center; margin-bottom: 30px; color: white; }
</style>
@endpush

@section('content')
<div class="grid-div">
    <div class="thing-grid">
        @foreach($categories as $category)
        <a href="{{ route('shop.category', $category) }}">
            <div class="thing-grid-item" style="background-color:{{ $category->color }};border-bottom:5px solid {{ $category->secondary_color }};">
                <div class="thing-grid-text">
                    <p class="thing-grid-title">{{ $category->name }}</p>
                    <p class="thing-grid-subtitle">Click to view ›</p>
                </div>
                @if($category->image)
                <div class="thing-grid-image">
                    <img class="thing-grid-ghost" src="{{ $category->image }}" width="90px" height="100px" style="border-radius:0" alt="">
                    <img src="{{ $category->image }}" width="90px" height="100px" style="border-radius:0" alt="{{ $category->name }}">
                </div>
                @endif
            </div>
        </a>
        @endforeach
    </div>
</div>

<div class="latest-announcements">
    <h2>Latest Announcements</h2>
    <div style="margin-left:auto;margin-right:auto;width:min-content;">
        <button onclick="window.location.href='{{ route('news') }}'" style="height:40px;width:100px">Show more</button>
    </div>
</div>
@endsection
