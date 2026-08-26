@extends('layouts.admin')
@section('title', 'Payment Settings')
@section('content')
<div class="admin-form-card" style="max-width:500px;">
    <form method="POST" action="{{ route('admin.settings.payment.update') }}">
        @csrf

        <div class="form-group" style="display:flex;align-items:center;justify-content:space-between;gap:20px;">
            <div>
                <label style="font-weight:600;color:white;margin:0;">Enable Payments</label>
                <p style="color:#888;font-size:12px;margin:4px 0 0;">Allow players to make purchases</p>
            </div>
            <label class="toggle-switch">
                <input type="hidden" name="EnablePayments" value="0">
                <input type="checkbox" name="EnablePayments" value="1" {{ $settings->EnablePayments ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
        </div>

        <hr style="border-color:#2a2a2a;margin:20px 0;">

        <div class="form-group" style="display:flex;align-items:center;justify-content:space-between;gap:20px;">
            <div>
                <label style="font-weight:600;color:white;margin:0;">Disable PayPal</label>
                <p style="color:#888;font-size:12px;margin:4px 0 0;">Hide the PayPal checkout button</p>
            </div>
            <label class="toggle-switch">
                <input type="hidden" name="DisablePayPal" value="0">
                <input type="checkbox" name="DisablePayPal" value="1" {{ $settings->DisablePayPal ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
        </div>

        <div class="form-group" style="display:flex;align-items:center;justify-content:space-between;gap:20px;margin-top:16px;">
            <div>
                <label style="font-weight:600;color:white;margin:0;">Disable Stripe</label>
                <p style="color:#888;font-size:12px;margin:4px 0 0;">Hide the Stripe checkout button</p>
            </div>
            <label class="toggle-switch">
                <input type="hidden" name="DisableStripe" value="0">
                <input type="checkbox" name="DisableStripe" value="1" {{ $settings->DisableStripe ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
        </div>

        <div style="margin-top:28px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Save Settings</button>
        </div>
    </form>
    <p style="color:#555;font-size:12px;margin-top:16px;">Last updated: {{ $settings->UpdatedAt?->format('Y-m-d H:i') ?? 'Never' }}</p>
</div>

<style>
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 52px;
    height: 28px;
    flex-shrink: 0;
    cursor: pointer;
}
.toggle-switch input[type="hidden"] { display: none; }
.toggle-switch input[type="checkbox"] {
    opacity: 0;
    width: 0;
    height: 0;
    position: absolute;
}
.toggle-slider {
    position: absolute;
    inset: 0;
    background: #333;
    border-radius: 28px;
    transition: .25s;
}
.toggle-slider:before {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    left: 4px;
    top: 4px;
    background: #888;
    border-radius: 50%;
    transition: .25s;
}
.toggle-switch input[type="checkbox"]:checked + .toggle-slider { background: #23d05e; }
.toggle-switch input[type="checkbox"]:checked + .toggle-slider:before {
    background: white;
    transform: translateX(24px);
}
</style>
@endsection
