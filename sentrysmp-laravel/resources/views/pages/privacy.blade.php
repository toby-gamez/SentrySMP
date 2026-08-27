@extends('layouts.app')
@section('title', 'Privacy Policy — SentrySMP')
@section('content')
<div class="main-wrapper">
<p class="main">Privacy Policy</p>
</div>

<div class="info">
    <p style="color:#666;font-size:13px;">Last updated: January 2025</p>

    <h2>1. Data We Collect</h2>
    <p>When you make a purchase, we collect your Minecraft username, payment details (processed by PayPal or Stripe — we do not store card numbers), and transaction metadata.</p>

    <h2>2. How We Use Your Data</h2>
    <p>Your data is used solely to process your order, deliver in-game purchases, and handle support requests. We do not sell or share your personal information with third parties except as required for payment processing.</p>

    <h2>3. Payment Processing</h2>
    <p>All payments are processed by PayPal or Stripe. We do not have access to your full card details. Please refer to PayPal's and Stripe's privacy policies for how they handle your payment information.</p>

    <h2>4. Cookies</h2>
    <p>We use cookies to maintain your session and cart state. By continuing to use this site you consent to the use of cookies.</p>

    <h2>5. Data Retention</h2>
    <p>Transaction records are retained for a minimum of 5 years for accounting and dispute purposes.</p>

    <h2>6. Your Rights</h2>
    <p>You may request deletion of your personal data (excluding legally required records) by contacting us via Discord.</p>

    <h2>7. Contact</h2>
    <p>If you have questions about this policy, contact us via <a href="{{ route('support') }}">Support</a>.</p>
</div>
@endsection
