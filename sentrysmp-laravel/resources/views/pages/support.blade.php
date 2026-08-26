@extends('layouts.app')
@section('title', 'Support — SentrySMP')
@section('content')
<div class="main-wrapper">
<p class="main">Support</p>
</div>

<div class="info">
    <h2>Need Help?</h2>
    <p>Our team is here to help! Choose the best way to reach us below.</p>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;margin-top:24px;">
        <div style="background:#111;border:1px solid #222;border-radius:10px;padding:20px;">
            <div style="font-size:28px;margin-bottom:10px;">💬</div>
            <h3 style="color:#fff;margin:0 0 8px;">Discord</h3>
            <p style="color:#888;font-size:13px;margin:0 0 14px;">Fastest response. Create a support ticket in our Discord server.</p>
            <a href="#" style="display:inline-block;background:#5865f2;color:#fff;padding:8px 18px;border-radius:6px;font-weight:700;text-decoration:none;font-size:13px;">Open Discord</a>
        </div>
        <div style="background:#111;border:1px solid #222;border-radius:10px;padding:20px;">
            <div style="font-size:28px;margin-bottom:10px;">🛒</div>
            <h3 style="color:#fff;margin:0 0 8px;">Purchase Issues</h3>
            <p style="color:#888;font-size:13px;margin:0 0 14px;">If you have a payment issue, include your transaction ID in your Discord ticket.</p>
            <span style="display:inline-block;background:#2a2a2a;color:#ccc;padding:8px 18px;border-radius:6px;font-size:13px;">Via Discord</span>
        </div>
    </div>

    <div style="margin-top:28px;padding:16px;background:#111;border-left:3px solid #23d05e;border-radius:4px;">
        <strong style="color:#23d05e;">Response time:</strong>
        <span style="color:#888;"> Usually within 24 hours on Discord.</span>
    </div>
</div>
@endsection
