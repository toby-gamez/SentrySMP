@extends('layouts.admin')
@section('title', 'Add Coin')
@section('content')
<div class="admin-form-card">
    <form method="POST" action="{{ route('admin.coins.store') }}">
        @csrf
        @include('admin._partials.product-form', ['item' => null, 'imageSubDir' => 'coins'])
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Save Coin</button>
            <a href="{{ route('admin.coins.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
