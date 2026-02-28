window.sentryTheme = (function () {
    // Use preferences: store both 'le' (boolean string for light enabled)
    // and 'cs' (color-scheme = 'light'|'dark'). For compatibility expose
    // the same `applyTheme(dark)` API which accepts a boolean where
    // true means dark (keeps existing callers working).
    function applyTheme(dark) {
        try {
            console.log('sentryTheme.applyTheme called with', dark);
            // We keep boolean semantics: `dark === true` means dark theme.
            function setStoredPreferences(isLight) {
                try {
                    localStorage.setItem('le', isLight ? 'true' : 'false');
                    localStorage.setItem('cs', isLight ? 'light' : 'dark');
                    // Keep legacy key for compatibility
                    localStorage.setItem('dark', isLight ? 'false' : 'true');
                    console.log('sentryTheme: set localStorage.le=', localStorage.getItem('le'), ' cs=', localStorage.getItem('cs'));
                } catch (e) { }
            }

            if (dark) {
                // dark theme -> ensure `.light` class is removed
                document.body.classList.remove('light');
                try { document.documentElement.classList.remove('light'); } catch (_) { }
                try { document.documentElement.setAttribute('data-theme', 'dark'); } catch (_) { }
                setStoredPreferences(false);
            } else {
                // light theme -> add `.light` class (CSS now targets body.light)
                document.body.classList.add('light');
                try { document.documentElement.classList.add('light'); } catch (_) { }
                try { document.documentElement.setAttribute('data-theme', 'light'); } catch (_) { }
                setStoredPreferences(true);
            }
        } catch (e) {
            // ignore
            console.warn('sentryTheme.applyTheme error', e);
        }
    }

    function getSavedTheme() {
        // Return legacy-compatible 'true' (dark) / 'false' (light) string
        try {
            var cs = localStorage.getItem('cs');
            if (cs === 'light') {
                console.log('sentryTheme.getSavedTheme -> cs=light');
                return 'false'; // not dark
            }
            if (cs === 'dark') {
                console.log('sentryTheme.getSavedTheme -> cs=dark');
                return 'true'; // dark
            }

            var le = localStorage.getItem('le');
            if (le === 'true') {
                console.log('sentryTheme.getSavedTheme -> le=true');
                return 'false';
            }
            if (le === 'false') {
                console.log('sentryTheme.getSavedTheme -> le=false');
                return 'true';
            }

            // Fallback to legacy 'dark' key if present
            var legacy = localStorage.getItem('dark');
            if (legacy === 'true' || legacy === 'false') {
                console.log('sentryTheme.getSavedTheme -> legacy dark=', legacy);
                return legacy;
            }

            return null;
        } catch (e) {
            console.warn('sentryTheme.getSavedTheme error', e);
            return null;
        }
    }

    // Apply saved theme on script load so initial render is correct
    try {
        var saved = getSavedTheme();
        if (saved === 'true') {
            // dark saved -> ensure .light is removed
            document.body.classList.remove('light');
        } else if (saved === 'false') {
            // light saved -> ensure .light is present
            document.body.classList.add('light');
        }
    } catch (_) { }

    // Re-apply theme on client-side navigation (single-page navigation)
    // Override history API methods so we can detect push/replace state navigation
    try {
        function ensureThemeSticks(dark) {
            // Re-apply theme a few times and observe DOM mutations briefly to combat frameworks that override classes
            try {
                var attempts = [0, 50, 150, 350];
                attempts.forEach(function (delay) {
                    setTimeout(function () {
                        try {
                            if (dark) {
                                // Ensure light class removed for dark theme
                                document.body.classList.remove('light');
                                document.documentElement.classList.remove('light');
                                document.documentElement.setAttribute('data-theme', 'dark');
                            } else {
                                // Ensure light class present for light theme
                                document.body.classList.add('light');
                                document.documentElement.classList.add('light');
                                document.documentElement.setAttribute('data-theme', 'light');
                            }
                        } catch (e) { }
                    }, delay);
                });

                // Use a short-lived MutationObserver to reapply if something removes the class shortly after navigation
                try {
                    var observer = new MutationObserver(function (mutations) {
                        try {
                            var s = getSavedTheme();
                            var wantDark = s === 'true';
                            if (wantDark) {
                                if (document.body.classList.contains('light')) {
                                    document.body.classList.remove('light');
                                    document.documentElement.classList.remove('light');
                                    document.documentElement.setAttribute('data-theme', 'dark');
                                }
                            } else {
                                if (!document.body.classList.contains('light')) {
                                    document.body.classList.add('light');
                                    document.documentElement.classList.add('light');
                                    document.documentElement.setAttribute('data-theme', 'light');
                                }
                            }
                        } catch (e) { }
                    });

                    observer.observe(document.documentElement || document, { attributes: true, attributeFilter: ['class'], subtree: true, childList: false });

                    // Disconnect after 1 second
                    setTimeout(function () {
                        try { observer.disconnect(); } catch (e) { }
                    }, 1000);
                } catch (e) { }
            } catch (e) { }
        }

        function applySaved() {
            try {
                var s = getSavedTheme();
                console.log('sentryTheme.applySaved ->', s);
                var dark = s === 'true';
                if (dark) {
                    document.body.classList.remove('light');
                    try { document.documentElement.classList.remove('light'); } catch (_) { }
                    try { document.documentElement.setAttribute('data-theme', 'dark'); } catch (_) { }
                }
                else {
                    document.body.classList.add('light');
                    try { document.documentElement.classList.add('light'); } catch (_) { }
                    try { document.documentElement.setAttribute('data-theme', 'light'); } catch (_) { }
                }
                // Ensure theme sticks across any subsequent DOM manipulations
                ensureThemeSticks(dark);
            } catch (e) { console.warn('sentryTheme.applySaved error', e); }
        }

        (function () {
            var _pushState = history.pushState;
            history.pushState = function () {
                var ret = _pushState.apply(this, arguments);
                // apply immediately so theme class is present before other navigation handlers run
                try { applySaved(); } catch (e) { setTimeout(applySaved, 0); }
                return ret;
            };

            var _replaceState = history.replaceState;
            history.replaceState = function () {
                var ret = _replaceState.apply(this, arguments);
                try { applySaved(); } catch (e) { setTimeout(applySaved, 0); }
                return ret;
            };

            window.addEventListener('popstate', function () { try { applySaved(); } catch (e) { setTimeout(applySaved, 0); } });
        })();
    } catch (e) { console.warn('sentryTheme navigation handler setup failed', e); }

    return {
        applyTheme: applyTheme,
        getSavedTheme: getSavedTheme
    };
})();
