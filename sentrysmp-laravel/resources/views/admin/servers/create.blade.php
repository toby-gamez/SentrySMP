@extends('layouts.admin')
@section('title', 'Add Server')
@section('content')
<div class="admin-form-card">
    <form method="POST" action="{{ route('admin.servers.store') }}">
        @csrf
        <div class="form-group"><label>Name *</label><input type="text" name="Name" value="{{ old('Name') }}" required maxlength="100"></div>
        <div class="form-group"><label>RCON IP *</label><input type="text" name="RCONIP" value="{{ old('RCONIP') }}" required maxlength="50"></div>
        <div class="form-group"><label>RCON Port *</label><input type="number" name="RCONPort" value="{{ old('RCONPort', 25575) }}" required min="1" max="65535"></div>
        <div class="form-group"><label>RCON Password *</label><input type="password" name="RCONPassword" required maxlength="100"></div>
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Save</button>
            <a href="{{ route('admin.servers.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
