@extends('layouts.admin')
@section('title', 'Edit Server')
@section('content')
<div class="admin-form-card">
    <form method="POST" action="{{ route('admin.servers.update', $server) }}">
        @csrf @method('PUT')
        <div class="form-group"><label>Name *</label><input type="text" name="Name" value="{{ old('Name', $server->Name) }}" required maxlength="100"></div>
        <div class="form-group"><label>RCON IP *</label><input type="text" name="RCONIP" value="{{ old('RCONIP', $server->RCONIP) }}" required maxlength="50"></div>
        <div class="form-group"><label>RCON Port *</label><input type="number" name="RCONPort" value="{{ old('RCONPort', $server->RCONPort) }}" required min="1" max="65535"></div>
        <div class="form-group"><label>RCON Password</label><input type="password" name="RCONPassword" placeholder="Leave blank to keep current" maxlength="100"></div>
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Update</button>
            <a href="{{ route('admin.servers.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
