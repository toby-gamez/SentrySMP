@extends('layouts.app')
@section('title', 'Join — SentrySMP')
@section('content')

<div class="join-container" id="join-container">

    <div class="join-tabs">
        <button type="button" class="join-tab active" id="tab-java" onclick="setEdition('java')">
            <i class="bi bi-display"></i> Java Edition
        </button>
        <button type="button" class="join-tab" id="tab-bedrock" onclick="setEdition('bedrock')">
            <i class="bi bi-controller"></i> Bedrock Edition
        </button>
    </div>

    <!-- Java Edition -->
    <div id="edition-java">
        <div class="join-server-info">
            <div class="join-info-card">
                <div class="join-info-label">Server IP</div>
                <div class="join-info-value">sentrysmp.eu</div>
            </div>
            <div class="join-info-card">
                <div class="join-info-label">Version</div>
                <div class="join-info-value">1.21.4+</div>
            </div>
            <div class="join-info-card">
                <div class="join-info-label">Voice Chat</div>
                <div class="join-info-value join-info-green"><i class="bi bi-check-circle-fill"></i> Available</div>
            </div>
        </div>
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
            <p>We <b>recommend</b> playing on <b>vanilla Minecraft</b>. Please <b>do not use cheats</b> — we have a strong anti-cheat system and a fast admin team to detect hacks.</p>
        </div>
    </div>

    <!-- Bedrock Edition -->
    <div id="edition-bedrock" style="display:none">
        <div class="join-server-info">
            <div class="join-info-card">
                <div class="join-info-label">Server IP</div>
                <div class="join-info-value">node.sentrysmp.eu</div>
            </div>
            <div class="join-info-card">
                <div class="join-info-label">Version</div>
                <div class="join-info-value">Newest</div>
            </div>
            <div class="join-info-card">
                <div class="join-info-label">Port</div>
                <div class="join-info-value">25566</div>
            </div>
        </div>
        <div class="join-steps">
            <div class="join-step">
                <span class="join-step-badge">1</span>
                <img class="join-step-img" src="{{ asset('images/title-bed.png') }}" alt="Go to Play section">
                <div class="join-step-footer"><h3 class="join-step-title">Go to Play section</h3></div>
            </div>
            <div class="join-step">
                <span class="join-step-badge">2</span>
                <img class="join-step-img" src="{{ asset('images/play-bed.png') }}" alt="Go to servers tab">
                <div class="join-step-footer"><h3 class="join-step-title">Go to Servers tab</h3></div>
            </div>
            <div class="join-step">
                <span class="join-step-badge">3</span>
                <img class="join-step-img" src="{{ asset('images/servers-bed.png') }}" alt="Add a new server">
                <div class="join-step-footer"><h3 class="join-step-title">Add a new server</h3></div>
            </div>
            <div class="join-step">
                <span class="join-step-badge">4</span>
                <img class="join-step-img" src="{{ asset('images/details-bed.png') }}" alt="Enter the details">
                <div class="join-step-footer"><h3 class="join-step-title">Enter the details, add, and join</h3></div>
            </div>
            <div class="join-step">
                <span class="join-step-badge">5</span>
                <img class="join-step-img" src="{{ asset('images/procced-bed.png') }}" alt="Proceed with playing">
                <div class="join-step-footer"><h3 class="join-step-title">Proceed with playing</h3></div>
            </div>
            <div class="join-step">
                <span class="join-step-badge">6</span>
                <img class="join-step-img" src="{{ asset('images/resource-bed.png') }}" alt="Accept our resource packs">
                <div class="join-step-footer"><h3 class="join-step-title">Accept our resource packs</h3></div>
            </div>
            <div class="join-step">
                <span class="join-step-badge">7</span>
                <img class="join-step-img" src="{{ asset('images/login-bed.png') }}" alt="Login/register">
                <div class="join-step-footer"><h3 class="join-step-title">Register / Login</h3></div>
            </div>
            <div class="join-step">
                <span class="join-step-badge">8</span>
                <img class="join-step-img" src="{{ asset('images/enjoy-bed.png') }}" alt="Enjoy!">
                <div class="join-step-footer"><h3 class="join-step-title">Enjoy playing!</h3></div>
            </div>
        </div>
        <div class="join-notice">
            <p>Please <b>do not use cheats</b> — we have a strong anti-cheat system and a fast admin team to detect hacks.</p>
        </div>
    </div>

</div>

@push('scripts')
<script>
function setEdition(edition) {
    document.getElementById('edition-java').style.display    = edition === 'java'    ? '' : 'none';
    document.getElementById('edition-bedrock').style.display = edition === 'bedrock' ? '' : 'none';
    document.getElementById('tab-java').classList.toggle('active',    edition === 'java');
    document.getElementById('tab-bedrock').classList.toggle('active', edition === 'bedrock');
}
// Read ?edition= from URL on load
(function() {
    var params = new URLSearchParams(window.location.search);
    if (params.get('edition') === 'bedrock') setEdition('bedrock');
})();
</script>
@endpush
@endsection
