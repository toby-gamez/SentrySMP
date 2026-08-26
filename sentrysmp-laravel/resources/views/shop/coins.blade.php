@extends('layouts.app')
@section('title', 'Coins - SentrySMP')
@section('content')
<div class="main-wrapper">
    @include('shop._product_grid', ['fallbackImage' => 'images/yellow-bundle.png'])
</div>
@endsection
