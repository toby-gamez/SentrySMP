@extends('layouts.admin')
@section('title', 'Add Rank')
@section('content')
<div class="admin-form-card">
    <form method="POST" action="{{ route('admin.ranks.store') }}">
        @csrf
        @include('admin._partials.product-form', ['item' => null, 'imageSubDir' => 'ranks'])
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Save Rank</button>
            <a href="{{ route('admin.ranks.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
