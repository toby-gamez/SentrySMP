@extends('layouts.admin')
@section('title', 'Servers')
@section('content')
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
