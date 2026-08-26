@extends('layouts.admin')
@section('title', 'Add Battle Pass')
@section('content')
<div class="admin-form-card">
    <form method="POST" action="{{ route('admin.battlepasses.store') }}">
        @csrf
        @include('admin._partials.product-form', ['item' => null, 'imageSubDir' => 'battlepasses'])
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Save Battle Pass</button>
            <a href="{{ route('admin.battlepasses.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
