{{--
    Payment Settings
    Variables: $settings (PaymentSettings model — single row from the DB)
    Toggles: enable_payments (master on/off), disable_paypal, disable_stripe.
    The hidden inputs before each checkbox ensure the value is submitted as 0 when unchecked.
    POSTs to admin.settings.payment.update.
--}}
@extends('layouts.admin')
@section('title', 'Payment Settings')
@section('content')
{{-- ── Page Guide ─────────────────────────────────────────────────────── --}}
<div style="background:#0b1929;border:1px solid #1e3a5f;border-left:3px solid #3b82f6;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:13px;">
    <div onclick="var n=this.nextElementSibling;n.hidden=!n.hidden;this.querySelector('.pg-ch').style.transform=n.hidden?'':'rotate(180deg)'"
         style="display:flex;align-items:center;gap:8px;cursor:pointer;user-select:none;">
        <i class="bi bi-book" style="color:#60a5fa;font-size:14px;"></i>
        <strong style="color:#93c5fd;">Payment Settings Guide</strong>
        <i class="bi bi-chevron-down pg-ch" style="color:#60a5fa;margin-left:auto;font-size:12px;transition:transform .2s;"></i>
    </div>
    <div hidden style="margin-top:12px;color:#94a3b8;line-height:1.75;">
        <ul style="margin:0;padding-left:18px;">
            <li><strong style="color:#c4d4e8;">Enable Payments</strong> — master on/off switch. Turn off to temporarily block all new purchases (e.g. during maintenance or a price update). Existing transactions are unaffected.</li>
            <li><strong style="color:#c4d4e8;">Disable PayPal</strong> — hides the PayPal checkout button. Useful if your PayPal account needs attention while Stripe remains active.</li>
            <li><strong style="color:#c4d4e8;">Disable Stripe</strong> — hides the Stripe checkout button. Useful if your Stripe account needs attention while PayPal remains active.</li>
            <li>If both providers are disabled, players cannot complete checkout even if payments are globally enabled.</li>
        </ul>
    </div>
</div>
<div class="admin-form-card" style="max-width:500px;">
    <form method="POST" action="{{ route('admin.settings.payment.update') }}">
        @csrf

        <div class="form-group" style="display:flex;align-items:center;justify-content:space-between;gap:20px;">
            <div>
                <label style="font-weight:600;color:white;margin:0;">Enable Payments</label>
                <p style="color:#888;font-size:12px;margin:4px 0 0;">Allow players to make purchases</p>
            </div>
            <label class="toggle-switch">
                <input type="hidden" name="enable_payments" value="0">
                <input type="checkbox" name="enable_payments" value="1" {{ $settings->enable_payments ? 'checked' : '' }}>
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
                <input type="hidden" name="disable_paypal" value="0">
                <input type="checkbox" name="disable_paypal" value="1" {{ $settings->disable_paypal ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
        </div>

        <div class="form-group" style="display:flex;align-items:center;justify-content:space-between;gap:20px;margin-top:16px;">
            <div>
                <label style="font-weight:600;color:white;margin:0;">Disable Stripe</label>
                <p style="color:#888;font-size:12px;margin:4px 0 0;">Hide the Stripe checkout button</p>
            </div>
            <label class="toggle-switch">
                <input type="hidden" name="disable_stripe" value="0">
                <input type="checkbox" name="disable_stripe" value="1" {{ $settings->disable_stripe ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
        </div>

        <div style="margin-top:28px;">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="bi bi-save"></i> Save Settings</button>
        </div>
    </form>
    <p style="color:#555;font-size:12px;margin-top:16px;">Last updated: {{ $settings->updated_at?->format('Y-m-d H:i') ?? 'Never' }}</p>
</div>

<div class="admin-form-card" style="max-width:500px;margin-top:24px;border-color:#3b1f1f;">
    <h3 style="color:#f87171;font-size:15px;margin:0 0 8px;">Reset Stats</h3>
    <p style="color:#888;font-size:13px;margin:0 0 16px;">
        Resets the displayed revenue total and clears the public scoreboard rankings.
        Existing transactions are <strong style="color:#e2e8f0;">not deleted</strong> — only hidden from stats going forward.
        @if($settings->stats_reset_at)
            Last reset: <span style="color:#94a3b8;">{{ $settings->stats_reset_at->format('Y-m-d H:i') }}</span>
        @else
            Never reset.
        @endif
    </p>
    <form method="POST" action="{{ route('admin.settings.payment.reset-stats') }}"
          onsubmit="return confirm('Reset revenue and scoreboard stats? Transactions are kept, only the display resets.')">
        @csrf
        <button type="submit" class="btn-admin" style="background:#7f1d1d;color:#fca5a5;border:1px solid #991b1b;">
            <i class="bi bi-arrow-counterclockwise"></i> Reset Revenue &amp; Scoreboard
        </button>
    </form>
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
