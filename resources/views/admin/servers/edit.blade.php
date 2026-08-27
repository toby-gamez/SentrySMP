{{--
    Edit Server
    Variables: $server (Server)
    Password field is optional — leaving it blank preserves the stored password.
    PUTs to admin.servers.update.
--}}
@extends('layouts.admin')
@section('title', 'Edit Server')
@section('content')
{{-- ── Page Guide ─────────────────────────────────────────────────────── --}}
<div style="background:#0b1929;border:1px solid #1e3a5f;border-left:3px solid #3b82f6;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:13px;">
    <div onclick="var n=this.nextElementSibling;n.hidden=!n.hidden;this.querySelector('.pg-ch').style.transform=n.hidden?'':'rotate(180deg)'"
         style="display:flex;align-items:center;gap:8px;cursor:pointer;user-select:none;">
        <i class="bi bi-book" style="color:#60a5fa;font-size:14px;"></i>
        <strong style="color:#93c5fd;">Edit Server Guide</strong>
        <i class="bi bi-chevron-down pg-ch" style="color:#60a5fa;margin-left:auto;font-size:12px;transition:transform .2s;"></i>
    </div>
    <div hidden style="margin-top:12px;color:#94a3b8;line-height:1.75;">
        <ul style="margin:0;padding-left:18px;">
            <li>Update connection details as needed. Changes take effect immediately for any subsequent command deliveries.</li>
            <li><strong style="color:#c4d4e8;">Password</strong> — leave the field <em>blank</em> to keep the existing password. Enter a new value only if you changed it in <code style="color:#888;">server.properties</code>.</li>
            <li>If the server is unreachable after saving, check that RCON is enabled and that the IP, port, and password all match <code style="color:#888;">server.properties</code>.</li>
        </ul>
    </div>
</div>
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
