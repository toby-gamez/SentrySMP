{{--
    Create Server
    Collects RCON connection details (name, IP, port, password).
    Password is required on create; left blank on edit means keep the existing value.
    POSTs to admin.servers.store.
--}}
@extends('layouts.admin')
@section('title', 'Add Server')
@section('content')
{{-- ── Page Guide ─────────────────────────────────────────────────────── --}}
<div style="background:#0b1929;border:1px solid #1e3a5f;border-left:3px solid #3b82f6;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:13px;">
    <div onclick="var n=this.nextElementSibling;n.hidden=!n.hidden;this.querySelector('.pg-ch').style.transform=n.hidden?'':'rotate(180deg)'"
         style="display:flex;align-items:center;gap:8px;cursor:pointer;user-select:none;">
        <i class="bi bi-book" style="color:#60a5fa;font-size:14px;"></i>
        <strong style="color:#93c5fd;">Add Server Guide</strong>
        <i class="bi bi-chevron-down pg-ch" style="color:#60a5fa;margin-left:auto;font-size:12px;transition:transform .2s;"></i>
    </div>
    <div hidden style="margin-top:12px;color:#94a3b8;line-height:1.75;">
        <ul style="margin:0;padding-left:18px;">
            <li><strong style="color:#c4d4e8;">Name</strong> — a label for this server (e.g. <em>Main SMP</em>).</li>
            <li><strong style="color:#c4d4e8;">RCON IP</strong> — the IP address or hostname of your Minecraft server.</li>
            <li><strong style="color:#c4d4e8;">RCON Port</strong> — the port RCON listens on. Default is <code style="color:#888;">25575</code>; set via <code style="color:#888;">rcon.port</code> in <code style="color:#888;">server.properties</code>.</li>
            <li><strong style="color:#c4d4e8;">RCON Password</strong> — must match <code style="color:#888;">rcon.password</code> in <code style="color:#888;">server.properties</code>. Stored encrypted.</li>
        </ul>
    </div>
</div>
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
