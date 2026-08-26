@extends('layouts.app')
@section('title', 'Battle Passes - SentrySMP')
@section('content')
<div class="main-wrapper">
    <p class="main">Battle Passes</p>
    <p style="color:#888;text-align:center;margin-bottom:30px;">Seasonal passes with exclusive rewards</p>
    @include('shop._product_grid', ['fallbackImage' => 'images/blue-bundle.png'])
</div>
@endsection
