@extends('layouts.app')

@section('title', 'Home - SentrySMP')

@push('head')
<style>
.latest-announcements { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
.latest-announcements h2 { text-align: center; margin-bottom: 30px; color: white; }
.announcements-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; }
</style>
@endpush

@section('content')
<div class="grid-div">
    <div class="thing-grid">
        <a href="{{ route('shop.keys') }}">
            <div class="thing-grid-item item-red">
                <div class="thing-grid-text">
                    <p class="thing-grid-title">Keys</p>
                    <p class="thing-grid-subtitle">Click to view ›</p>
                </div>
                <div class="thing-grid-image">
                    <img class="thing-grid-ghost" src="{{ asset('images/red-bundle.png') }}" width="90px" height="100px" style="border-radius:0" alt="">
                    <img src="{{ asset('images/red-bundle.png') }}" width="90px" height="100px" style="border-radius:0" alt="key">
                </div>
            </div>
        </a>
        <a href="{{ route('shop.coins') }}">
            <div class="thing-grid-item item-gold">
                <div class="thing-grid-text">
                    <p class="thing-grid-title">Coins</p>
                    <p class="thing-grid-subtitle">Click to view ›</p>
                </div>
                <div class="thing-grid-image">
                    <img class="thing-grid-ghost" src="{{ asset('images/yellow-bundle.png') }}" width="100px" height="100px" style="border-radius:0" alt="">
                    <img src="{{ asset('images/yellow-bundle.png') }}" width="100px" height="100px" style="border-radius:0" alt="coins">
                </div>
            </div>
        </a>
        <a href="{{ route('shop.bundles') }}">
            <div class="thing-grid-item item-pink">
                <div class="thing-grid-text">
                    <p class="thing-grid-title">Bundles</p>
                    <p class="thing-grid-subtitle">Click to view ›</p>
                </div>
                <div class="thing-grid-image">
                    <img class="thing-grid-ghost" src="{{ asset('images/pink-bundle.png') }}" width="100px" height="100px" style="border-radius:0" alt="">
                    <img src="{{ asset('images/pink-bundle.png') }}" width="100px" height="100px" style="border-radius:0" alt="bundle">
                </div>
            </div>
        </a>
        <a href="{{ route('shop.ranks') }}">
            <div class="thing-grid-item item-green">
                <div class="thing-grid-text">
                    <p class="thing-grid-title">Ranks</p>
                    <p class="thing-grid-subtitle">Click to view ›</p>
                </div>
                <div class="thing-grid-image">
                    <img class="thing-grid-ghost" src="{{ asset('images/green-bundle.png') }}" width="100px" height="100px" style="border-radius:0" alt="">
                    <img src="{{ asset('images/green-bundle.png') }}" width="100px" height="100px" style="border-radius:0" alt="rank">
                </div>
            </div>
        </a>
        <a href="{{ route('shop.other') }}">
            <div class="thing-grid-item item-gray">
                <div class="thing-grid-text">
                    <p class="thing-grid-title">Other</p>
                    <p class="thing-grid-subtitle">Click to view ›</p>
                </div>
                <div class="thing-grid-image">
                    <img class="thing-grid-ghost" src="{{ asset('images/gray-bundle.png') }}" width="100px" height="100px" style="border-radius:0" alt="">
                    <img src="{{ asset('images/gray-bundle.png') }}" width="100px" height="100px" style="border-radius:0" alt="other">
                </div>
            </div>
        </a>
    </div>
</div>

<div class="latest-announcements">
    <h2>Latest Announcements</h2>
    {{-- Announcements would go here when the table exists --}}
    <div style="margin-left:auto;margin-right:auto;width:min-content;">
        <button onclick="window.location.href='{{ route('news') }}'" style="height:40px;width:100px">Show more</button>
    </div>
</div>
@endsection
