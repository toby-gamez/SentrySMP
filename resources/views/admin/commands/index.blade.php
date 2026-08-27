{{--
    Global Command List
    Variables: $items (Collection<Command> with eager-loaded product)
    Read-only overview of every delivery command across all products.
    Commands are managed per-product on the product edit page; deletion is available here.
    Use %player% in command_text as a placeholder for the buyer's Minecraft username.
--}}
@extends('layouts.admin')
@section('title', 'Commands')
@section('content')
{{-- ── Page Guide ─────────────────────────────────────────────────────── --}}
<div style="background:#0b1929;border:1px solid #1e3a5f;border-left:3px solid #3b82f6;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:13px;">
    <div onclick="var n=this.nextElementSibling;n.hidden=!n.hidden;this.querySelector('.pg-ch').style.transform=n.hidden?'':'rotate(180deg)'"
         style="display:flex;align-items:center;gap:8px;cursor:pointer;user-select:none;">
        <i class="bi bi-book" style="color:#60a5fa;font-size:14px;"></i>
        <strong style="color:#93c5fd;">Commands Guide</strong>
        <i class="bi bi-chevron-down pg-ch" style="color:#60a5fa;margin-left:auto;font-size:12px;transition:transform .2s;"></i>
    </div>
    <div hidden style="margin-top:12px;color:#94a3b8;line-height:1.75;">
        <ul style="margin:0;padding-left:18px;">
            <li>This page lists every delivery command configured across all products.</li>
            <li>Commands are run on the game server when a player successfully purchases the linked product.</li>
            <li><strong style="color:#c4d4e8;"><code style="color:#888;">%player%</code></strong> is replaced with the buyer's Minecraft username at delivery time (e.g. <code style="color:#888;">give %player% diamond 64</code>).</li>
            <li>To <strong style="color:#c4d4e8;">add or edit</strong> commands for a product, go to that product's <strong style="color:#c4d4e8;">edit page</strong> — the inline command editor is only available there.</li>
            <li>You can <strong style="color:#c4d4e8;">delete</strong> commands from this global list using the trash icon.</li>
        </ul>
    </div>
</div>
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">Commands ({{ $items->count() }})</h2>
    </div>
    <p style="color:#888;font-size:13px;margin-bottom:16px;">Use <code style="background:#1a1a1a;padding:2px 6px;border-radius:4px;">%player%</code> as a placeholder for the buyer's Minecraft username.</p>
    @if($items->isEmpty())
        <p style="color:#666;">No commands configured yet. Add them via the product edit page.</p>
    @else
    <table class="admin-table">
        <thead><tr><th>Product</th><th>Command</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>
                    @if($item->product)
                        <a href="{{ route('admin.products.edit', $item->product) }}">
                            {{ $item->product->name }}
                        </a>
                    @else
                        <span style="color:#555;">—</span>
                    @endif
                </td>
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
