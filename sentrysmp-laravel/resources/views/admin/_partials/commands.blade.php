{{-- Variables: $commands, $commandType (string), $commandTypeId (int) --}}
<div class="admin-card" style="margin-top:24px;">
    <div class="admin-card-header">
        <h2 class="admin-card-title"><i class="bi bi-terminal-fill"></i> Commands ({{ $commands->count() }})</h2>
    </div>

    @if($commands->isEmpty())
        <p style="color:#666;margin-bottom:16px;">No commands yet.</p>
    @else
    <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:20px;">
        @foreach($commands as $cmd)
        <div style="display:flex;gap:8px;align-items:center;">
            {{-- Edit form --}}
            <form method="POST" action="{{ route('admin.commands.update', $cmd) }}"
                  style="display:flex;gap:8px;align-items:center;flex:1;min-width:0;">
                @csrf @method('PUT')
                <input type="text" name="CommandText" value="{{ $cmd->CommandText }}"
                       required style="flex:1;min-width:0;font-family:monospace;font-size:13px;">
                <button type="submit" class="btn-admin btn-admin-secondary" style="padding:6px 12px;white-space:nowrap;flex-shrink:0;">
                    <i class="bi bi-floppy"></i> Save
                </button>
            </form>
            {{-- Delete form (sibling, not nested) --}}
            <form method="POST" action="{{ route('admin.commands.destroy', $cmd) }}"
                  onsubmit="return confirm('Delete this command?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-admin btn-admin-danger" style="padding:6px 10px;">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Add command --}}
    <form method="POST" action="{{ route('admin.commands.store') }}"
          style="display:flex;gap:8px;align-items:center;{{ $commands->isNotEmpty() ? 'padding-top:14px;border-top:1px solid #222;' : '' }}">
        @csrf
        <input type="hidden" name="Type" value="{{ $commandType }}">
        <input type="hidden" name="TypeId" value="{{ $commandTypeId }}">
        <input type="text" name="CommandText" placeholder="e.g. give %player% diamond 1"
               required style="flex:1;min-width:0;font-family:monospace;font-size:13px;">
        <button type="submit" class="btn-admin btn-admin-primary" style="padding:6px 14px;white-space:nowrap;flex-shrink:0;">
            <i class="bi bi-plus-lg"></i> Add
        </button>
    </form>
    <p style="color:#555;font-size:11px;margin-top:8px;">Use <code style="color:#888;">%player%</code> as placeholder for the buyer's username.</p>
</div>
