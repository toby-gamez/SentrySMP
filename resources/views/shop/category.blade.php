@extends('layouts.app')
@section('title', $category->name . ' - SentrySMP')
@section('content')
<div class="main-wrapper">
    @include('shop._product_grid', ['fallbackImage' => null])
</div>
@endsection
