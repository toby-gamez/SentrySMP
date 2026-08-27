@extends('layouts.app')
@section('title', 'Terms of Use — SentrySMP')
@section('content')
<div class="main-wrapper">
<p class="main">Terms of Use</p>
</div>

<div class="info">
    <p style="color:#666;font-size:13px;">Last updated: January 2025</p>

    <h2>1. Acceptance</h2>
    <p>By accessing SentrySMP or making a purchase, you agree to these Terms. If you do not agree, do not use our services.</p>

    <h2>2. Purchases &amp; Refunds</h2>
    <p>All purchases are for virtual in-game items. Due to the digital nature of these goods, all sales are <strong>final and non-refundable</strong> unless required by applicable law. In cases of technical failure on our end preventing delivery, we will re-deliver or issue store credit.</p>

    <h2>3. Chargebacks</h2>
    <p>Filing a chargeback without first contacting us will result in a permanent ban from the server. We are happy to resolve issues directly.</p>

    <h2>4. Account Responsibility</h2>
    <p>Purchases are tied to your Minecraft username. We are not responsible for purchases made by unauthorized use of your account.</p>

    <h2>5. Rules</h2>
    <p>All players must follow our <a href="{{ route('minecraft-rules') }}">Minecraft Rules</a>. Bans do not entitle you to a refund.</p>

    <h2>6. Modifications</h2>
    <p>We reserve the right to modify or discontinue in-game items, prices, or game features at any time. Purchases are for items as described at time of purchase.</p>

    <h2>7. EULA</h2>
    <p>All purchases comply with the <a href="https://www.minecraft.net/en-us/eula" target="_blank" rel="noopener">Minecraft EULA</a>.</p>

    <h2>8. Contact</h2>
    <p>For any concerns, contact us via <a href="{{ route('support') }}">Support</a>.</p>
</div>
@endsection
