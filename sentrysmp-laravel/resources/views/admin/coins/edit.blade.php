@extends('layouts.admin')
@section('title', 'Edit Coin')
@section('content')
<div class="admin-form-card">
    <form method="POST" action="{{ route('admin.coins.update', $coin) }}">
        @csrf @method('PUT')
        @include('admin._partials.product-form', ['item' => $coin, 'imageSubDir' => 'coins'])
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Update Coin</button>
            <a href="{{ route('admin.coins.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>

@include('admin._partials.commands', ['commandType' => 'Coin', 'commandTypeId' => $coin->Id, 'commands' => $commands])
@endsection
