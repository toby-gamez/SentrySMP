// Minimal helper to manage Google Consent Mode and loading gtag.js
window.analyticsConsent = (function () {
    var gaLoaded = false;
    function ensureGtagFunction() {
        window.dataLayer = window.dataLayer || [];
        window.gtag = window.gtag || function () { window.dataLayer.push(arguments); };
    }

    return {
        // Called when no choice was made yet. Set default denied so GA doesn't send data.
        setDefaultDenied: function () {
            ensureGtagFunction();
            try {
                window.gtag('consent', 'default', { analytics_storage: 'denied' });
            } catch (e) { /* swallow */ }
        },

        // Update consent state: 'granted' or 'denied'
        updateConsent: function (state) {
            ensureGtagFunction();
            try {
                window.gtag('consent', 'update', { analytics_storage: state });
            } catch (e) { /* swallow */ }
        },

        // Load Google Analytics script and configure it
        loadAnalytics: function (gtagId) {
            if (!gtagId) return;
            if (gaLoaded) return;
            gaLoaded = true;
            ensureGtagFunction();
            var script = document.createElement('script');
            script.async = true;
            script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(gtagId);
            document.head.appendChild(script);
            script.onload = function () {
                try {
                    window.gtag('js', new Date());
                    window.gtag('config', gtagId);
                } catch (e) { /* swallow */ }
            };
        }
        ,
        // Remove stored consent and reload the page
        removeConsentAndReload: function () {
            try {
                localStorage.removeItem('cookies-accepted');
            } catch (e) { /* swallow */ }
            try {
                // reload the page to apply new consent state
                location.reload();
            } catch (e) { /* swallow */ }
        }
    };
})();
