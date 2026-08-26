@extends('layouts.admin')
@section('title', 'Add Other Item')
@section('content')
<div class="admin-form-card">
    <form method="POST" action="{{ route('admin.others.store') }}">
        @csrf
        @include('admin._partials.product-form', ['item' => null, 'imageSubDir' => 'others'])
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Save Item</button>
            <a href="{{ route('admin.others.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
