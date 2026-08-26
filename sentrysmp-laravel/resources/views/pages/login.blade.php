@extends('layouts.app')
@section('title', 'Login - SentrySMP')
@section('content')

<div class="main-wrapper">
    <p class="main">Login</p>
</div>

<div class="login-card">
    <div class="login-card-inner">

        <!-- Left: skin preview panel -->
        <div class="login-skin-panel">
            <img id="skin-preview" src="https://minotar.net/helm/MHF_Steve/100" alt="Skin preview" class="login-skin-img">
            <div class="login-skin-name" id="skin-name">Minecraft Player</div>
            <div class="login-skin-badge" id="skin-badge">Choose edition →</div>
        </div>

        <!-- Right: form panel -->
        <div class="login-form-panel">
            <div class="login-field">
                <label for="login-username" class="login-label">Minecraft username</label>
                <input id="login-username" type="text" placeholder="Your Nick" oninput="loginUpdatePreview()">
                <small class="login-hint">Enter your username only, without a leading dot.</small>
            </div>

            <div class="login-field">
                <label class="login-label">Edition</label>
                <div class="edition-selector">
                    <label class="edition-option edition-option--active" id="edition-java" onclick="loginSetEdition('java')" tabindex="0">
                        <i class="bi bi-display"></i>
                        <span>Java</span>
                    </label>
                    <label class="edition-option" id="edition-bedrock" onclick="loginSetEdition('bedrock')" tabindex="0">
                        <i class="bi bi-phone"></i>
                        <span>Bedrock</span>
                    </label>
                </div>
            </div>

            <div class="login-actions">
                <button class="great-button" onclick="loginSave()">Log in</button>
                <button class="secondary" onclick="loginCancel()">Cancel</button>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
(function () {
    var _edition = 'java';

    // Define all functions first before any calls

    window.loginSetEdition = function (edition) {
        _edition = edition;
        document.getElementById('edition-java').classList.toggle('edition-option--active', edition === 'java');
        document.getElementById('edition-bedrock').classList.toggle('edition-option--active', edition === 'bedrock');
        window.loginUpdatePreview();
        window.loginUpdateBadge();
    };

    window.loginUpdatePreview = function () {
        var name    = document.getElementById('login-username').value.trim();
        var preview = document.getElementById('skin-preview');
        var nameEl  = document.getElementById('skin-name');
        if (!name) {
            preview.src = 'https://minotar.net/helm/MHF_Steve/100';
            nameEl.textContent = 'Minecraft Player';
        } else if (_edition === 'bedrock') {
            preview.src = 'https://minotar.net/helm/MHF_Steve/100';
            nameEl.textContent = name;
        } else {
            preview.src = 'https://minotar.net/helm/' + encodeURIComponent(name) + '/100';
            nameEl.textContent = name;
        }
        window.loginUpdateBadge();
    };

    window.loginUpdateBadge = function () {
        var name  = document.getElementById('login-username').value.trim();
        var badge = document.getElementById('skin-badge');
        if (!name) {
            badge.textContent = 'Choose edition →';
        } else {
            badge.textContent = _edition === 'bedrock' ? 'Bedrock Edition' : 'Java Edition';
        }
    };

    window.loginSave = function () {
        var name = document.getElementById('login-username').value.trim();
        if (!name) return;
        var toStore = (_edition === 'bedrock' && !name.startsWith('.')) ? '.' + name : name;
        localStorage.setItem('mc_username', toStore);
        localStorage.setItem('mc_edition', _edition);
        if (typeof refreshLoginBar === 'function') refreshLoginBar();
        window.location.href = '/';
    };

    window.loginCancel = function () {
        window.location.href = '/';
    };

    // Now safe to call — all functions are defined above
    var stored      = localStorage.getItem('mc_username') || '';
    var storedEdition = localStorage.getItem('mc_edition') || 'java';
    if (stored) {
        var isBedrock = stored.startsWith('.');
        document.getElementById('login-username').value = isBedrock ? stored.substring(1) : stored;
        _edition = isBedrock ? 'bedrock' : storedEdition;
    } else {
        _edition = storedEdition;
    }
    window.loginSetEdition(_edition);
})();
</script>
@endpush

@endsection
