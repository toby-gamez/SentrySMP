<div class="nav-list">
    <a href="{{ route('home') }}">
        <div class="{{ request()->is('/') || request()->routeIs('home') ? 'nav-item nav-item--active' : 'nav-item' }}">
            <div class="nav-icon"><i class="bi bi-house-door-fill"></i></div>
            <span>HOME</span>
        </div>
    </a>
</div>

<div class="nav-list">
    <a href="{{ route('shop.keys') }}">
        <div class="{{ request()->routeIs('shop.keys') ? 'nav-item nav-item--active' : 'nav-item' }}">
            <div class="nav-icon"><img class="nav-icon" src="{{ asset('images/red-bundle.png') }}" alt="KEYS"></div>
            <span>KEYS</span>
        </div>
    </a>
    <a href="{{ route('shop.coins') }}">
        <div class="{{ request()->routeIs('shop.coins') ? 'nav-item nav-item--active' : 'nav-item' }}">
            <div class="nav-icon"><img class="nav-icon" src="{{ asset('images/yellow-bundle.png') }}" alt="GEMS"></div>
            <span>GEMS</span>
        </div>
    </a>
    <a href="{{ route('shop.bundles') }}">
        <div class="{{ request()->routeIs('shop.bundles') ? 'nav-item nav-item--active' : 'nav-item' }}">
            <div class="nav-icon"><img class="nav-icon" src="{{ asset('images/pink-bundle.png') }}" alt="BUNDLES"></div>
            <span>BUNDLES</span>
        </div>
    </a>
    <a href="{{ route('shop.ranks') }}">
        <div class="{{ request()->routeIs('shop.ranks') ? 'nav-item nav-item--active' : 'nav-item' }}">
            <div class="nav-icon"><img class="nav-icon" src="{{ asset('images/green-bundle.png') }}" alt="RANKS"></div>
            <span>RANKS</span>
        </div>
    </a>
    <a href="{{ route('shop.other') }}">
        <div class="{{ request()->routeIs('shop.other') ? 'nav-item nav-item--active' : 'nav-item' }}">
            <div class="nav-icon"><img class="nav-icon" src="{{ asset('images/gray-bundle.png') }}" alt="OTHER"></div>
            <span>OTHER</span>
        </div>
    </a>
</div>

<div class="nav-list">
    <div class="nav-heading">JOIN</div>
    <a href="{{ route('join', ['edition' => 'java']) }}">
        <div class="{{ request()->routeIs('join') && request('edition') === 'java' ? 'nav-item nav-item--active' : 'nav-item' }}">
            <div class="nav-icon"><img class="nav-icon" src="{{ asset('images/java.png') }}" alt="java"></div>
            <span>JAVA</span>
        </div>
    </a>
    <a href="{{ route('join', ['edition' => 'bedrock']) }}">
        <div class="{{ request()->routeIs('join') && request('edition') === 'bedrock' ? 'nav-item nav-item--active' : 'nav-item' }}">
            <div class="nav-icon"><img class="nav-icon" src="{{ asset('images/bedrock.webp') }}" alt="bedrock"></div>
            <span>BEDROCK</span>
        </div>
    </a>
</div>

<div class="nav-list">
    <div class="nav-heading">LINKS</div>
    <a href="{{ route('about') }}">
        <div class="{{ request()->routeIs('about') ? 'nav-item nav-item--active' : 'nav-item' }}">
            <div class="nav-icon"><i class="bi bi-info-circle-fill"></i></div>
            <span>ABOUT SERVER</span>
        </div>
    </a>
    <a href="{{ route('our-team') }}">
        <div class="{{ request()->routeIs('our-team') ? 'nav-item nav-item--active' : 'nav-item' }}">
            <div class="nav-icon"><i class="bi bi-people-fill"></i></div>
            <span>OUR TEAM</span>
        </div>
    </a>
    <a href="{{ route('scoreboard') }}">
        <div class="{{ request()->routeIs('scoreboard') ? 'nav-item nav-item--active' : 'nav-item' }}">
            <div class="nav-icon"><i class="bi bi-trophy-fill"></i></div>
            <span>SCOREBOARD</span>
        </div>
    </a>
    <a href="{{ route('vote-for-us') }}">
        <div class="{{ request()->routeIs('vote-for-us') ? 'nav-item nav-item--active' : 'nav-item' }}">
            <div class="nav-icon"><i class="bi bi-heart-fill"></i></div>
            <span>VOTE FOR US</span>
        </div>
    </a>
    <a href="{{ route('active-players') }}">
        <div class="{{ request()->routeIs('active-players') ? 'nav-item nav-item--active' : 'nav-item' }}">
            <div class="nav-icon"><i class="bi bi-person-lines-fill"></i></div>
            <span>ACTIVE PLAYERS</span>
        </div>
    </a>
    <a href="{{ route('banlist') }}">
        <div class="{{ request()->routeIs('banlist') ? 'nav-item nav-item--active' : 'nav-item' }}">
            <div class="nav-icon"><i class="bi bi-person-fill-slash"></i></div>
            <span>BAN LIST</span>
        </div>
    </a>
</div>

@if(!empty($navTopDonors) && count($navTopDonors) > 0)
<div class="nav-list">
    <div class="nav-heading">TOP DONORS</div>
    @foreach($navTopDonors as $donor)
        <div class="nav-item nav-item--donor">
            <img class="nav-head" src="https://minotar.net/helm/{{ urlencode($donor->MinecraftUsername) }}/24" alt="{{ $donor->MinecraftUsername }}">
            <span class="nav-donor-name">{{ $donor->MinecraftUsername }}</span>
            <span class="nav-donor-amount">€{{ number_format($donor->total_paid, 2) }}</span>
        </div>
    @endforeach
</div>
@endif

<div class="nav-list">
    <div class="nav-heading">REFUND POLICY</div>
    <p class="text-muted">By making a purchase, you agree that all sales are final. Refunds cannot be provided under any circumstances.</p>
</div>

<div class="nav-list">
    <div class="nav-heading">THEME</div>
    <div class="nav-theme-switch">
        <span>Dark</span>
        <label class="switch">
            <input type="checkbox" id="modeToggle" onchange="toggleTheme(this.checked)">
            <span class="slider"></span>
        </label>
        <span>Light</span>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var saved = window.sentryTheme ? window.sentryTheme.getSavedTheme() : null;
        // getSavedTheme: 'true' = dark, 'false' = light, null = no preference
        var isLight = saved === 'false';
        var toggle = document.getElementById('modeToggle');
        if (toggle) toggle.checked = isLight;
    });

    function toggleTheme(isLight) {
        if (window.sentryTheme && window.sentryTheme.applyTheme) {
            // applyTheme(dark): true = dark mode, false = light mode
            window.sentryTheme.applyTheme(!isLight);
        }
    }
</script>
