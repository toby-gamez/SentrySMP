@extends('layouts.admin')
@section('title', 'Add Voucher')
@section('content')
<div class="admin-form-card" style="max-width:700px;">
    <form method="POST" action="{{ route('admin.vouchers.store') }}">
        @csrf
        @include('admin._partials.voucher-form', ['item' => null])
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Save Voucher</button>
            <a href="{{ route('admin.vouchers.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
