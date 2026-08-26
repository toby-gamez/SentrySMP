@extends('layouts.admin')
@section('title', 'Edit Rank')
@section('content')
<div class="admin-form-card">
    <form method="POST" action="{{ route('admin.ranks.update', $rank) }}">
        @csrf @method('PUT')
        @include('admin._partials.product-form', ['item' => $rank, 'imageSubDir' => 'ranks'])
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Update Rank</button>
            <a href="{{ route('admin.ranks.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>

@include('admin._partials.commands', ['commandType' => 'Rank', 'commandTypeId' => $rank->Id, 'commands' => $commands])
@endsection
