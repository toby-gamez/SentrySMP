@extends('layouts.app')
@section('title', 'Bundles - SentrySMP')
@section('content')
<div class="main-wrapper">
    @include('shop._product_grid', ['fallbackImage' => 'images/pink-bundle.png'])
</div>
@endsection
