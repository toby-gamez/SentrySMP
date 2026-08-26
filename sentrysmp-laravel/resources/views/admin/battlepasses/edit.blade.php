@extends('layouts.admin')
@section('title', 'Edit Battle Pass')
@section('content')
<div class="admin-form-card">
    <form method="POST" action="{{ route('admin.battlepasses.update', $battlepass) }}">
        @csrf @method('PUT')
        @include('admin._partials.product-form', ['item' => $battlepass, 'imageSubDir' => 'battlepasses'])
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Update Battle Pass</button>
            <a href="{{ route('admin.battlepasses.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>

@include('admin._partials.commands', ['commandType' => 'BattlePass', 'commandTypeId' => $battlepass->Id, 'commands' => $commands])
@endsection
