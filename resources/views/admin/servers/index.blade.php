{{--
    Server List
    Variables: $items (Collection<Server>)
    Lists configured game servers (RCON targets for command delivery).
    RCON passwords are stored encrypted and are never displayed here.
--}}
@extends('layouts.admin')
@section('title', 'Servers')
@section('content')
{{-- ── Page Guide ─────────────────────────────────────────────────────── --}}
<div style="background:#0b1929;border:1px solid #1e3a5f;border-left:3px solid #3b82f6;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:13px;">
    <div onclick="var n=this.nextElementSibling;n.hidden=!n.hidden;this.querySelector('.pg-ch').style.transform=n.hidden?'':'rotate(180deg)'"
         style="display:flex;align-items:center;gap:8px;cursor:pointer;user-select:none;">
        <i class="bi bi-book" style="color:#60a5fa;font-size:14px;"></i>
        <strong style="color:#93c5fd;">Server Management Guide</strong>
        <i class="bi bi-chevron-down pg-ch" style="color:#60a5fa;margin-left:auto;font-size:12px;transition:transform .2s;"></i>
    </div>
    <div hidden style="margin-top:12px;color:#94a3b8;line-height:1.75;">
        <ul style="margin:0;padding-left:18px;">
            <li>Servers are the <strong style="color:#c4d4e8;">RCON</strong> (Remote Console) targets used to deliver commands to your Minecraft server after a purchase.</li>
            <li>Without at least one configured server, purchased commands cannot be delivered — buyers will pay but receive nothing in-game.</li>
            <li>RCON must be enabled in your Minecraft server's <code style="color:#888;">server.properties</code>:<br>
                <code style="color:#888;">enable-rcon=true</code> &nbsp;|&nbsp; <code style="color:#888;">rcon.port=25575</code> &nbsp;|&nbsp; <code style="color:#888;">rcon.password=yourpassword</code>
            </li>
            <li>RCON passwords are stored encrypted and are never displayed in the admin panel.</li>
        </ul>
    </div>
</div>
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">Servers ({{ $items->count() }})</h2>
        <a href="{{ route('admin.servers.create') }}" class="btn-admin btn-admin-primary"><i class="bi bi-plus-lg"></i> Add Server</a>
    </div>
    @if($items->isEmpty())
        <p style="color:#666;">No servers configured.</p>
    @else
    <table class="admin-table">
        <thead><tr><th>Name</th><th>RCON IP</th><th>RCON Port</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td style="color:white;font-weight:600;">{{ $item->Name }}</td>
                <td style="color:#888;">{{ $item->RCONIP }}</td>
                <td>{{ $item->RCONPort }}</td>
                <td>
                    <a href="{{ route('admin.servers.edit', $item) }}" class="btn-admin btn-admin-secondary"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="{{ route('admin.servers.destroy', $item) }}" style="display:inline;" onsubmit="return confirm('Delete this server?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-admin btn-admin-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
