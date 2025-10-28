window.sentryTheme = (function () {
    // Use localStorage key 'dark' with values 'true' or 'false'
    function applyTheme(dark) {
        try {
            console.log('sentryTheme.applyTheme called with', dark);
            if (dark) {
                document.body.classList.add('dark');
                try { document.documentElement.classList.add('dark'); } catch (_) { }
                try { document.documentElement.setAttribute('data-theme', 'dark'); } catch (_) { }
                localStorage.setItem('dark', 'true');
                console.log('sentryTheme: set localStorage.dark = true');
            } else {
                document.body.classList.remove('dark');
                try { document.documentElement.classList.remove('dark'); } catch (_) { }
                try { document.documentElement.setAttribute('data-theme', 'light'); } catch (_) { }
                localStorage.setItem('dark', 'false');
                console.log('sentryTheme: set localStorage.dark = false');
            }
        } catch (e) {
            // ignore
            console.warn('sentryTheme.applyTheme error', e);
        }
    }

    function getSavedTheme() {
        try {
            var v = localStorage.getItem('dark');
            console.log('sentryTheme.getSavedTheme ->', v);
            return v;
        } catch (e) {
            console.warn('sentryTheme.getSavedTheme error', e);
            return null;
        }
    }

    // Apply saved theme on script load so initial render is correct
    try {
        var saved = getSavedTheme();
        if (saved === 'true') {
            document.body.classList.add('dark');
        } else if (saved === 'false') {
            document.body.classList.remove('dark');
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
                                document.body.classList.add('dark');
                                document.documentElement.classList.add('dark');
                                document.documentElement.setAttribute('data-theme', 'dark');
                            } else {
                                document.body.classList.remove('dark');
                                document.documentElement.classList.remove('dark');
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
                            var want = s === 'true';
                            if (want) {
                                if (!document.body.classList.contains('dark')) {
                                    document.body.classList.add('dark');
                                    document.documentElement.classList.add('dark');
                                    document.documentElement.setAttribute('data-theme', 'dark');
                                }
                            } else {
                                if (document.body.classList.contains('dark')) {
                                    document.body.classList.remove('dark');
                                    document.documentElement.classList.remove('dark');
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
                    document.body.classList.add('dark');
                    try { document.documentElement.classList.add('dark'); } catch (_) { }
                    try { document.documentElement.setAttribute('data-theme', 'dark'); } catch (_) { }
                }
                else {
                    document.body.classList.remove('dark');
                    try { document.documentElement.classList.remove('dark'); } catch (_) { }
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
