@extends('layouts.app')
@section('title', 'Other - SentrySMP')
@section('content')
<div class="main-wrapper">
    @include('shop._product_grid', ['fallbackImage' => 'images/gray-bundle.png'])
</div>
@endsection
