@extends('layouts.admin')
@section('title', 'Edit Bundle')
@section('content')
<div class="admin-form-card">
    <form method="POST" action="{{ route('admin.bundles.update', $bundle) }}">
        @csrf @method('PUT')
        @include('admin._partials.product-form', ['item' => $bundle, 'imageSubDir' => 'bundles'])
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Update Bundle</button>
            <a href="{{ route('admin.bundles.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>

@include('admin._partials.commands', ['commandType' => 'Bundle', 'commandTypeId' => $bundle->Id, 'commands' => $commands])
@endsection
