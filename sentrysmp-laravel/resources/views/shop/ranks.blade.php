@extends('layouts.app')
@section('title', 'Ranks - SentrySMP')
@section('content')
<div class="main-wrapper">
    @include('shop._product_grid', ['fallbackImage' => 'images/green-bundle.png'])
</div>
@endsection
