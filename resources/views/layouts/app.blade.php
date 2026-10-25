<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SentrySMP')</title>

    <!-- SEO -->
    <meta name="description" content="SentrySMP is a Minecraft server focused on providing a safe and enjoyable experience for players of all ages. It is SMP with addons.">
    <meta name="keywords" content="Minecraft, SMP, English, Czech, server, safe, enjoyable, experience, players, vip, premium, exclusive">
    <meta name="author" content="Sentry SMP">

    <!-- Open Graph -->
    <meta property="og:title" content="Sentry SMP">
    <meta property="og:description" content="Sentry SMP server is a Minecraft server focused on providing a safe and enjoyable experience for players of all ages. It is SMP with addons.">
    <meta property="og:image" content="https://www.sentrysmp.eu/images/logo.png">
    <meta property="og:url" content="https://www.sentrysmp.eu/">
    <meta property="og:type" content="website">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Sentry SMP">
    <meta name="twitter:description" content="Sentry SMP server is a Minecraft server focused on providing a safe and enjoyable experience for players of all ages. It is SMP with addons.">
    <meta name="twitter:image" content="https://www.sentrysmp.eu/images/logo.png">

    <meta http-equiv="content-language" content="en">
    <meta name="language" content="English">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <!-- Load theme.js FIRST — before CSS to avoid theme flicker on initial paint -->
    <script src="{{ asset('js/theme.js') }}"></script>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.min.css">

    <script src="{{ asset('analytics-consent.js') }}"></script>
    <script src="{{ asset('js/menuToggle.js') }}"></script>
    <script>
        // Auto-initialize GA if consent was previously granted
        try {
            if (window.analyticsConsent && window.analyticsConsent.autoInitIfGranted) {
                setTimeout(function () {
                    try {
                        window.analyticsConsent.autoInitIfGranted('G-SGG2CLM06D');
                    } catch (e) { console.log('GA auto-init failed:', e); }
                }, 10);
            }
        } catch (e) { console.log('GA auto-init setup failed:', e); }
    </script>

    @stack('head')
</head>
<body class="{{ $season }}">

@if ($season === 'christmas')
<!-- Seasonal particles -->
<div class="snowflakes" aria-hidden="true">
    @for ($i = 0; $i < 50; $i++)
        <div class="snowflake">❅</div>
    @endfor
</div>
<script>
    // Keep the snowfall continuous across page loads
    (function () {
        var t0 = Date.now();
        try {
            t0 = +sessionStorage.getItem('seasonT0') || t0;
            sessionStorage.setItem('seasonT0', t0);
        } catch (e) {}
        var elapsed = (Date.now() - t0) / 1000;
        document.querySelectorAll('.snowflake').forEach(function (el) {
            var delay = parseFloat(getComputedStyle(el).animationDelay) || 0;
            el.style.animationDelay = (delay - elapsed) + 's';
        });
    })();
</script>
@endif

<div id="maintenance-banner" class="maintenance-banner" style="display:none;">SERVER IN MAINTENANCE</div>

<div class="page">
    <main>
        <!-- Mobile burger button — visible only on mobile via CSS -->
        <button type="button" class="burger-btn" onclick="document.body.classList.toggle('nav-open')" aria-label="Toggle navigation">
            <i class="bi bi-list"></i>
        </button>
        <!-- Overlay backdrop for mobile sidebar -->
        <div class="nav-overlay" onclick="document.body.classList.remove('nav-open')"></div>

        <article class="content px-4">

            <!-- ── Header ── -->
            <style>
                body.light .header-background {
                    background-image: url("/images/background-image.png");
                }
                body:not(.light) .header-background {
                    background-image: url("/images/background-image-dark.png");
                }
                body.christmas.light .header-background {
                    background-image: url("/images/background-image-snow.png");
                }
                body.christmas:not(.light) .header-background {
                    background-image: url("/images/background-image-snow-dark.png");
                }
            </style>
            <div class="header-background">
                @if ($season === 'halloween')
                <div class="bat-line" aria-hidden="true">
                    @php
                        // [top px, width px, fly seconds, delay s, bob seconds, flap seconds]
                        $bats = [[30, 30, 34, -6, 4.2, .34], [95, 22, 46, -31, 5.6, .30], [60, 36, 28, -17, 3.8, .38], [150, 20, 52, -9, 6.1, .28], [18, 24, 41, -38, 4.9, .32], [120, 28, 37, -22, 5.2, .36]];
                    @endphp
                    @foreach ($bats as [$top, $w, $fly, $delay, $bob, $flap])
                    <div class="bat-fly" style="top: {{ $top }}px; --fly: {{ $fly }}s; --delay: {{ $delay }}s">
                        <svg class="bat" viewBox="0 0 40 20" style="width: {{ $w }}px; --bob: {{ $bob }}s; --flap: {{ $flap }}s">
                            <path class="wing" d="M20 10C24 2 32 2 39 6C36 7 35 9 33 11C31 9 28 10 26 13C24 11 22 12 20 14Z"/>
                            <g transform="translate(40 0) scale(-1 1)"><path class="wing" d="M20 10C24 2 32 2 39 6C36 7 35 9 33 11C31 9 28 10 26 13C24 11 22 12 20 14Z"/></g>
                            <ellipse cx="20" cy="11" rx="2.6" ry="4.2"/>
                            <path d="M18 7.5L18.6 4.8L20 6.6L21.4 4.8L22 7.5Z"/>
                        </svg>
                    </div>
                    @endforeach
                </div>
                <script>
                    // Keep the bats' flight continuous across page loads
                    (function () {
                        var t0 = Date.now();
                        try {
                            t0 = +sessionStorage.getItem('seasonT0') || t0;
                            sessionStorage.setItem('seasonT0', t0);
                        } catch (e) {}
                        var elapsed = (Date.now() - t0) / 1000;
                        document.querySelectorAll('.bat-fly').forEach(function (el) {
                            var base = parseFloat(el.style.getPropertyValue('--delay')) || 0;
                            el.style.setProperty('--delay', (base - elapsed) + 's');
                        });
                    })();
                </script>
                @endif
            </div>
            <div class="grid">
                <i class="bi bi-controller mobile-none" style="font-size: 3rem"></i>
                <div class="grid-item" id="copy-ip" title="Copy IP address"
                     style="padding-left: 5px; cursor: pointer;"
                     onclick="(function(){
                         navigator.clipboard.writeText('sentrysmp.eu');
                         var el = document.querySelector('#copy-ip .big');
                         el.textContent = 'IP COPIED';
                         setTimeout(function(){ el.textContent = 'SENTRYSMP.EU'; }, 2000);
                     })()">
                    <span>
                        <small>PLAYING <span id="mc-players">—</span></small>
                        <a href="{{ route('join') }}" title="How can I join?" style="text-decoration:none;">
                            <i class="bi bi-info-circle"></i>
                        </a>
                    </span>
                    <br>
                    <span class="big">SENTRYSMP.EU</span>
                </div>
                <a href="{{ route('home') }}">
                    <div class="logo grid-item">
                        <img src="{{ asset('images/logo.png') }}" class="logo" alt="logo">
                    </div>
                </a>
                <a href="https://discord.gg/gXrXMwpuH4" target="_blank">
                    <div style="padding-right: 5px; text-align: right" class="grid-item" title="Join our Discord">
                        <span><small><span id="discord-count">—</span> MEMBERS</small></span>
                        <br>
                        <span class="big">JOIN <span style="visibility: hidden">.</span>DISCORD</span>
                    </div>
                </a>
                <i style="font-size: 3rem" class="bi bi-discord mobile-none"></i>
            </div>
            <!-- Login / username bar -->
            <div id="login-box">
                <div class="login-info">
                    <img id="login-skin" src="https://minotar.net/helm/MHF_Steve/100" class="skin-img" alt="skin">
                    <div>
                        <div><strong id="login-name">Guest</strong></div>
                    </div>
                </div>
                <!-- Shown when logged in -->
                <div id="login-buttons-in" style="display:none;gap:4px;margin-left:12px;">
                    <button class="secondary login-bar-icon" title="Log out" onclick="doLogout()">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                    <button class="secondary login-bar-icon" title="Profile" onclick="window.location.href='{{ route('profile') }}'">
                        <i class="bi bi-person-fill"></i>
                    </button>
                    <button class="great-button" onclick="openCartSidebar()">
                        <i class="bi bi-cart-fill cart-button-icon"></i> Cart
                    </button>
                </div>
                <!-- Shown when guest -->
                <div id="login-buttons-out" style="display:flex;gap:4px;margin-left:12px;">
                    <button class="great-button" onclick="doLogin()">Log in</button>
                </div>
            </div>

            <!-- Cart sidebar overlay -->
            <div id="cart-overlay" class="cart-sidebar-overlay" onclick="closeCartSidebar()" style="display:none;"></div>
            <!-- Cart sidebar -->
            <div id="cart-sidebar" class="cart-sidebar">
                <div class="cart-sidebar-header">
                    <h2><i class="bi bi-cart"></i> Cart</h2>
                    <button class="cart-sidebar-close" onclick="closeCartSidebar()" aria-label="Close cart"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="cart-sidebar-body">
                    <p id="cart-not-logged-in" style="display:none;color:red;">You are not logged in.</p>
                    <div id="cart-items-container" class="cart-sidebar-items"></div>
                </div>
                <div class="cart-sidebar-footer">
                    <div class="cart-sidebar-total">
                        <strong>Total:</strong>
                        <span id="cart-total">€0.00</span>
                    </div>
                    <div class="cart-sidebar-actions">
                        <button class="danger" onclick="clearCart()" id="cart-clear-btn" disabled>
                            <i class="bi bi-x-circle"></i> Clear Cart
                        </button>
                        <button class="great-button" onclick="goToCheckout()" id="cart-checkout-btn" style="width:100%" disabled>
                            <i class="bi bi-bag-check"></i> Checkout
                        </button>
                    </div>
                    <p class="cart-sidebar-note">To receive your purchase, you must be connected to the server where you bought items from. <b>Lobby is also a dedicated server.</b></p>
                </div>
            </div>

            <div class="container">
                <div class="page-layout">
                    <aside class="page-sidebar">
                        @include('layouts.partials.nav')
                    </aside>
                    <div class="page-content">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-error">{{ $errors->first() }}</div>
                        @endif
                        @yield('content')
                    </div>
                </div>
            </div>

            @include('layouts.partials.footer')
        </article>
    </main>

    <!-- Cookie banner -->
    <div id="cookie-banner" style="display:flex; gap:0.5rem; align-items:center;">
        <span>We use cookies to analyze traffic and improve your experience. By continuing to browse, you agree to our use of cookies.
            <a href="{{ route('privacy') }}">Learn more</a>
        </span>
        <div>
            <button class="accept btn btn-primary" onclick="acceptCookies()">Accept</button>
            <button class="decline btn btn-secondary" onclick="declineCookies()">Decline</button>
        </div>
    </div>
</div>

<script src="{{ asset('js/cart.js') }}"></script>
<script>
    // Hide cookie banner if consent already given
    (function () {
        var consent = localStorage.getItem('cookies-accepted');
        if (consent === 'granted' || consent === 'declined') {
            document.getElementById('cookie-banner').style.display = 'none';
        }
    })();

    function acceptCookies() {
        localStorage.setItem('cookies-accepted', 'granted');
        document.getElementById('cookie-banner').style.display = 'none';
        if (window.analyticsConsent) {
            window.analyticsConsent.updateConsent('granted');
            window.analyticsConsent.loadAnalytics('G-SGG2CLM06D');
        }
    }

    function declineCookies() {
        localStorage.setItem('cookies-accepted', 'declined');
        document.getElementById('cookie-banner').style.display = 'none';
        if (window.analyticsConsent) {
            window.analyticsConsent.updateConsent('denied');
        }
    }

    // Login bar — refresh based on localStorage state
    function refreshLoginBar() {
        var username = localStorage.getItem('mc_username') || '';
        var isBedrock = username.startsWith('.');
        document.getElementById('login-name').textContent = username || 'Guest';
        document.getElementById('login-skin').src = username && !isBedrock
            ? 'https://minotar.net/helm/' + encodeURIComponent(username) + '/100'
            : 'https://minotar.net/helm/MHF_Steve/100';
        document.getElementById('login-buttons-in').style.display  = username ? 'flex' : 'none';
        document.getElementById('login-buttons-out').style.display = username ? 'none' : 'flex';
        // Update cart not-logged-in warning and checkout button
        var notIn = document.getElementById('cart-not-logged-in');
        if (notIn) notIn.style.display = username ? 'none' : '';
        updateCheckoutBtn();
    }

    function updateCheckoutBtn() {
        var loggedIn = !!localStorage.getItem('mc_username');
        var cartEmpty = typeof getCart === 'function' ? getCart().length === 0 : true;
        var checkoutBtn = document.getElementById('cart-checkout-btn');
        var clearBtn    = document.getElementById('cart-clear-btn');
        if (checkoutBtn) checkoutBtn.disabled = !loggedIn || cartEmpty;
        if (clearBtn)    clearBtn.disabled    = cartEmpty;
    }

    function doLogin() {
        window.location.href = '{{ route('player.login') }}';
    }

    function doLogout() {
        localStorage.removeItem('mc_username');
        refreshLoginBar();
    }

    function goToCheckout() {
        closeCartSidebar();
        window.location.href = '{{ route('checkout') }}';
    }

    // Run on load
    refreshLoginBar();

    // Load live player / Discord member counts; show maintenance banner if server is down
    fetch('/api/status')
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (data) {
            var banner = document.getElementById('maintenance-banner');
            if (!data) {
                if (banner) banner.style.display = '';
                return;
            }
            var fresh = data.player_fresh === true;
            var count = fresh ? data.player_count : 0;
            document.getElementById('mc-players').textContent = count;
            if (banner) banner.style.display = fresh ? 'none' : '';
            if (data.discord_members !== undefined && data.discord_members !== null) {
                document.getElementById('discord-count').textContent = data.discord_members;
            }
        })
        .catch(function () {
            var banner = document.getElementById('maintenance-banner');
            if (banner) banner.style.display = '';
        });
</script>
@stack('scripts')
</body>
</html>
