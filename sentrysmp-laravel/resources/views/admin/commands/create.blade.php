@extends('layouts.admin')
@section('title', 'Add Command')
@section('content')
<div class="admin-form-card">
    <p style="color:#888;font-size:13px;margin-bottom:20px;">Use <code style="background:#1a1a1a;padding:2px 6px;border-radius:4px;">%player%</code> as a placeholder — it will be replaced with the buyer's Minecraft username when a transaction succeeds.</p>
    <form method="POST" action="{{ route('admin.commands.store') }}">
        @csrf
        <div class="form-group">
            <label>Product Type *</label>
            <select name="type" required>
                @foreach($types as $t)
                    <option value="{{ $t }}" {{ old('type') == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Product ID * <span style="color:#666;font-weight:400;">(the numeric ID of the specific product)</span></label>
            <input type="number" name="type_id" value="{{ old('type_id') }}" required min="1">
        </div>
        <div class="form-group">
            <label>Command *</label>
            <input type="text" name="command_text" value="{{ old('command_text') }}" required placeholder="give %player% diamond 1">
        </div>
        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Save Command</button>
            <a href="{{ route('admin.commands.index') }}" class="btn-admin btn-admin-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
