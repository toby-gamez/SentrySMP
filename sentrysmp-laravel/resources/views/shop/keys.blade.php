@extends('layouts.app')
@section('title', 'Keys - SentrySMP')
@section('content')
<div class="main-wrapper">
    @include('shop._product_grid', ['fallbackImage' => 'images/red-bundle.png'])
</div>
@endsection
