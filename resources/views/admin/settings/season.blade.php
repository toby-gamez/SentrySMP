{{--
    Season Settings
    Variables: $season (auto|halloween|christmas|none), $options (value => label)
    POSTs to admin.settings.season.update.
--}}
@extends('layouts.admin')
@section('title', 'Season')
@section('content')
<div class="admin-form-card" style="max-width:500px;">
    <form method="POST" action="{{ route('admin.settings.season.update') }}">
        @csrf
        <div class="form-group">
            <label style="font-weight:600;color:white;">Shop theme</label>
            <select name="season" class="form-control">
                @foreach($options as $value => $label)
                    <option value="{{ $value }}" {{ $season === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <p style="color:#888;font-size:12px;margin:8px 0 0;">Controls the seasonal colours and falling particles on the public shop.</p>
        </div>
        <div style="margin-top:20px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Save</button>
        </div>
    </form>
</div>
@endsection
