@extends('layouts.admin')
@section('title', 'Edit Item')
@section('content')
<div class="admin-form-card">
    <form method="POST" action="{{ route('admin.others.update', $other) }}">
        @csrf @method('PUT')
        @include('admin._partials.product-form', ['item' => $other, 'imageSubDir' => 'others'])
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Update Item</button>
            <a href="{{ route('admin.others.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>

@include('admin._partials.commands', ['commandType' => 'Other', 'commandTypeId' => $other->Id, 'commands' => $commands])
@endsection
