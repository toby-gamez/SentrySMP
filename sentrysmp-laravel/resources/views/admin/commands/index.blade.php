@extends('layouts.admin')
@section('title', 'Commands')
@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">Commands ({{ $items->count() }})</h2>
        <a href="{{ route('admin.commands.create') }}" class="btn-admin btn-admin-primary"><i class="bi bi-plus-lg"></i> Add Command</a>
    </div>
    <p style="color:#888;font-size:13px;margin-bottom:16px;">Use <code style="background:#1a1a1a;padding:2px 6px;border-radius:4px;">%player%</code> as a placeholder for the buyer's Minecraft username.</p>
    @if($items->isEmpty())
        <p style="color:#666;">No commands configured yet.</p>
    @else
    <table class="admin-table">
        <thead><tr><th>Type</th><th>Type ID</th><th>Command</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td><span class="badge" style="background:#1e3a20;color:#6be895;">{{ $item->type }}</span></td>
                <td>{{ $item->type_id }}</td>
                <td style="font-family:monospace;font-size:13px;color:#ccc;">{{ $item->command_text }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.commands.destroy', $item) }}" style="display:inline;" onsubmit="return confirm('Delete this command?')">
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
