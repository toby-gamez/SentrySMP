@extends('layouts.admin')
@section('title', 'Edit Key')
@section('content')
<div class="admin-form-card">
    <form method="POST" action="{{ route('admin.keys.update', $key) }}">
        @csrf @method('PUT')
        @include('admin._partials.product-form', ['item' => $key, 'imageSubDir' => 'keys'])
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Update Key</button>
            <a href="{{ route('admin.keys.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>

@include('admin._partials.commands', ['commandType' => 'Key', 'commandTypeId' => $key->Id, 'commands' => $commands])
@endsection
