// Minimal helper to manage Google Consent Mode and loading gtag.js
window.analyticsConsent = (function () {
    var gaLoaded = false;
    var debug = true; // Enable debug logging
    
    function log(message, data) {
        if (debug) {
            console.log('[GA Debug] ' + message, data || '');
        }
    }
    
    function ensureGtagFunction() {
        window.dataLayer = window.dataLayer || [];
        window.gtag = window.gtag || function () { 
            log('gtag called with:', arguments);
            window.dataLayer.push(arguments); 
        };
    }

    return {
        // Called when no choice was made yet. Set default denied so GA doesn't send data.
        setDefaultDenied: function () {
            log('Setting default consent to denied');
            ensureGtagFunction();
            try {
                window.gtag('consent', 'default', { analytics_storage: 'denied' });
                log('Default consent set successfully');
            } catch (e) { 
                log('Error setting default consent:', e);
            }
        },

        // Update consent state: 'granted' or 'denied'
        updateConsent: function (state) {
            log('Updating consent to:', state);
            ensureGtagFunction();
            try {
                window.gtag('consent', 'update', { analytics_storage: state });
                log('Consent updated successfully to:', state);
            } catch (e) { 
                log('Error updating consent:', e);
            }
        },

        // Load Google Analytics script and configure it
        loadAnalytics: function (gtagId) {
            log('Loading analytics with ID:', gtagId);
            
            // Validate GA tracking ID format
            if (!gtagId || gtagId.trim() === '') {
                log('Warning: No Google Analytics tracking ID provided. GA will be disabled.');
                return;
            }
            
            if (!/^G-[A-Z0-9]{10}$/i.test(gtagId)) {
                log('Error: Invalid GA tracking ID format. Expected format: G-XXXXXXXXXX, got:', gtagId);
                return;
            }
            
            if (gaLoaded) {
                log('Analytics already loaded, skipping');
                return;
            }
            
            // Check if this is a demo ID (but allow G-SGG2CLM06D as valid)
            if (gtagId === 'G-1234567890') {
                log('Warning: Using demo tracking ID (' + gtagId + '). Google Analytics will not collect real data.');
                log('Please replace with a valid Google Analytics 4 tracking ID from your GA4 property.');
                log('To get a real tracking ID: https://analytics.google.com/ → Admin → Data Streams → Web Stream');
                // Continue to load for testing purposes
            }
            
            gaLoaded = true;
            ensureGtagFunction();
            
            var script = document.createElement('script');
            script.async = true;
            script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(gtagId);
            log('Loading GA script from:', script.src);
            
            script.onload = function () {
                log('GA script loaded successfully');
                try {
                    window.gtag('js', new Date());
                    window.gtag('config', gtagId, {
                        debug_mode: debug,
                        send_page_view: true
                    });
                    log('GA configured with ID:', gtagId);
                    
                    // Test event to verify GA is working
                    setTimeout(function() {
                        try {
                            window.gtag('event', 'page_view', {
                                page_title: document.title,
                                page_location: window.location.href
                            });
                            log('Test page_view event sent');
                        } catch (e) {
                            log('Error sending test event:', e);
                        }
                    }, 1000);
                } catch (e) { 
                    log('Error configuring GA:', e);
                }
            };
            
            script.onerror = function (error) {
                log('ERROR: Failed to load GA script from:', script.src);
                log('Possible causes:');
                log('1. Ad blocker (uBlock Origin, AdBlock, etc.)');
                log('2. Network/DNS issue with googletagmanager.com');
                log('3. Firewall blocking Google domains');
                log('4. CSP (Content Security Policy) blocking external scripts');
                log('Script error object:', error);
                log('Error type:', error ? error.type : 'unknown');
                
                // Try to determine the specific cause
                try {
                    fetch('https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(gtagId), {method: 'HEAD'})
                        .then(function() {
                            log('Direct fetch test: SUCCESS - Ad blocker likely blocking the script tag');
                        })
                        .catch(function(e) {
                            log('Direct fetch test: FAILED - Network/DNS issue:', e.message);
                        });
                } catch (e) {
                    log('Could not perform fetch test:', e);
                }
            };
            
            // Add timeout for script loading
            var timeoutId = setTimeout(function() {
                log('GA script loading timed out after 10 seconds');
                log('This may indicate network issues or script blocking');
            }, 10000);
            
            var originalOnload = script.onload;
            script.onload = function () {
                clearTimeout(timeoutId);
                log('GA script loaded successfully');
                try {
                    window.gtag('js', new Date());
                    window.gtag('config', gtagId, {
                        debug_mode: debug,
                        send_page_view: true
                    });
                    log('GA configured with ID:', gtagId);
                    
                    // Test event to verify GA is working
                    setTimeout(function() {
                        try {
                            window.gtag('event', 'page_view', {
                                page_title: document.title,
                                page_location: window.location.href
                            });
                            log('Test page_view event sent');
                        } catch (e) {
                            log('Error sending test event:', e);
                        }
                    }, 1000);
                } catch (e) { 
                    log('Error configuring GA:', e);
                }
            };
            
            document.head.appendChild(script);
        }
        ,
        // Remove stored consent and reload the page
        removeConsentAndReload: function () {
            log('Removing consent and reloading page');
            try {
                localStorage.removeItem('cookies-accepted');
                log('Consent removed from localStorage');
            } catch (e) { 
                log('Error removing consent:', e);
            }
            try {
                // reload the page to apply new consent state
                location.reload();
            } catch (e) { 
                log('Error reloading page:', e);
            }
        },
        
        // Debug function to manually test GA
        testAnalytics: function() {
            log('Testing GA manually...');
            if (typeof window.gtag !== 'function') {
                log('Error: gtag function not available');
                return false;
            }
            
            try {
                window.gtag('event', 'test_event', {
                    custom_parameter: 'manual_test',
                    timestamp: new Date().toISOString()
                });
                log('Manual test event sent successfully');
                return true;
            } catch (e) {
                log('Error sending manual test event:', e);
                return false;
            }
        },
        
        // Check current state
        getDebugInfo: function() {
            return {
                gaLoaded: gaLoaded,
                gtagExists: typeof window.gtag === 'function',
                dataLayerExists: Array.isArray(window.dataLayer),
                dataLayerLength: window.dataLayer ? window.dataLayer.length : 0,
                consent: localStorage.getItem('cookies-accepted')
            };
        },
        
        // Auto-initialize if consent was already granted (called immediately by Blazor)
        autoInitIfGranted: function(gtagId) {
            try {
                var storedConsent = localStorage.getItem('cookies-accepted');
                if (storedConsent === 'granted' && gtagId) {
                    log('Auto-initializing GA because consent was previously granted');
                    this.updateConsent('granted');
                    this.loadAnalytics(gtagId);
                    return true;
                }
                return false;
            } catch (e) {
                log('Auto-init failed:', e);
                return false;
            }
        }
    };
})();

// Auto-initialize on script load (similar to theme.js approach)
try {
    // Check if consent was previously granted and auto-load GA
    var storedConsent = localStorage.getItem('cookies-accepted');
    if (storedConsent === 'granted') {
        // We don't have the tracking ID here, so we'll set up a way for Blazor to provide it
        window.analyticsConsent.autoInitOnGranted = true;
        console.log('[GA Debug] Consent was previously granted, ready for auto-initialization');
    }
} catch (e) {
    console.log('[GA Debug] Auto-init check failed:', e);
}
