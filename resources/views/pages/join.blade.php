@extends('layouts.app')
@section('title', 'Join — SentrySMP')
@section('content')

<div class="join-container">

    <section class="join-hero">
        <img class="join-hero-icon" src="{{ asset('images/java.png') }}" alt="Minecraft Java Edition">
        <span class="join-hero-tag">Java Edition</span>
        <h1 class="join-hero-title">Join SentrySMP</h1>
        <p class="join-hero-text">Grab your friends, hop into the world and start your adventure. Follow the steps below and you will be playing in no time.</p>
        <button type="button" class="join-copy-btn" id="join-copy-btn" onclick="copyJoinIp()">
            <i class="bi bi-clipboard-fill" id="join-copy-icon"></i>
            <span id="join-copy-label">Copy server IP</span>
        </button>
    </section>

    <div class="join-server-info">
        <div class="join-info-card">
            <div class="join-info-icon"><i class="bi bi-hdd-network-fill"></i></div>
            <div>
                <div class="join-info-label">Server IP</div>
                <div class="join-info-value">sentrysmp.eu</div>
            </div>
        </div>
        <div class="join-info-card">
            <div class="join-info-icon"><i class="bi bi-cpu-fill"></i></div>
            <div>
                <div class="join-info-label">Version</div>
                <div class="join-info-value">1.21.4+</div>
            </div>
        </div>
        <div class="join-info-card">
            <div class="join-info-icon"><i class="bi bi-mic-fill"></i></div>
            <div>
                <div class="join-info-label">Voice Chat</div>
                <div class="join-info-value join-info-green"><i class="bi bi-check-circle-fill"></i> Available</div>
            </div>
        </div>
    </div>

    <h2 class="join-section-title"><i class="bi bi-signpost-split-fill"></i> How to join</h2>

    <div class="join-steps">
        <div class="join-step">
            <span class="join-step-badge">1</span>
            <img class="join-step-img" src="{{ asset('images/title-screen.png') }}" alt="title screen">
            <div class="join-step-footer"><h3 class="join-step-title">Go to Multiplayer section</h3></div>
        </div>
        <div class="join-step">
            <span class="join-step-badge">2</span>
            <img class="join-step-img" src="{{ asset('images/multiplayer.png') }}" alt="multiplayer screen">
            <div class="join-step-footer"><h3 class="join-step-title">Add a server</h3></div>
        </div>
        <div class="join-step">
            <span class="join-step-badge">3</span>
            <img class="join-step-img" src="{{ asset('images/ip.png') }}" alt="IP in Minecraft Java">
            <div class="join-step-footer"><h3 class="join-step-title">Enter the details</h3></div>
        </div>
        <div class="join-step">
            <span class="join-step-badge">4</span>
            <img class="join-step-img" src="{{ asset('images/join.png') }}" alt="Join the server">
            <div class="join-step-footer"><h3 class="join-step-title">Join the server</h3></div>
        </div>
        <div class="join-step">
            <span class="join-step-badge">5</span>
            <img class="join-step-img" src="{{ asset('images/login.png') }}" alt="Login/register">
            <div class="join-step-footer"><h3 class="join-step-title">Register / Login</h3></div>
        </div>
        <div class="join-step">
            <span class="join-step-badge">6</span>
            <img class="join-step-img" src="{{ asset('images/play.png') }}" alt="Enjoy!">
            <div class="join-step-footer"><h3 class="join-step-title">Enjoy playing!</h3></div>
        </div>
    </div>

    <div class="join-notice">
        <i class="bi bi-shield-exclamation"></i>
        <p>We <b>recommend</b> playing on <b>vanilla Minecraft</b>. Please <b>do not use cheats</b> — we have a strong anti-cheat system and a fast admin team to detect hacks.</p>
    </div>

</div>

@push('scripts')
<script>
function copyJoinIp() {
    var ip = 'sentrysmp.eu';
    var btn = document.getElementById('join-copy-btn');
    var label = document.getElementById('join-copy-label');
    var icon = document.getElementById('join-copy-icon');

    function done() {
        label.textContent = 'IP copied!';
        icon.className = 'bi bi-check2-circle';
        btn.classList.add('copied');
        setTimeout(function () {
            label.textContent = 'Copy server IP';
            icon.className = 'bi bi-clipboard-fill';
            btn.classList.remove('copied');
        }, 2000);
    }

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(ip).then(done).catch(fallbackCopy);
    } else {
        fallbackCopy();
    }

    function fallbackCopy() {
        var input = document.createElement('textarea');
        input.value = ip;
        input.style.position = 'fixed';
        input.style.opacity = '0';
        document.body.appendChild(input);
        input.select();
        try { document.execCommand('copy'); done(); } catch (e) {}
        document.body.removeChild(input);
    }
}
</script>
@endpush
@endsection
