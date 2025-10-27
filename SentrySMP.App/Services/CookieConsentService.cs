using System;
using System.Threading.Tasks;
using Microsoft.JSInterop;

namespace SentrySMP.App.Services
{
    public enum ConsentState
    {
        Unknown,
        Granted,
        Denied
    }

    public class CookieConsentService
    {
        private const string StorageKey = "cookies-accepted";
        private IJSRuntime? _js;

        public ConsentState CurrentState { get; private set; } = ConsentState.Unknown;

        // Fired when consent changes (so UI can update)
        public event Action? OnConsentChanged;

        /// <summary>
        /// Initialize the service. Must be called from a component with IJSRuntime available.
        /// This will read stored consent from localStorage and tell the JS helper to set the appropriate consent state.
        /// If consent has been granted previously, it will also instruct the JS helper to load Google Analytics.
        /// </summary>
        public async Task InitializeAsync(IJSRuntime js, string gtagId)
        {
            _js = js ?? throw new ArgumentNullException(nameof(js));

            // Read localStorage directly via JS interop
            string? stored = null;
            try
            {
                stored = await _js.InvokeAsync<string>("localStorage.getItem", StorageKey);
            }
            catch
            {
                // If JS interop fails, keep Unknown
            }

            if (string.IsNullOrEmpty(stored))
            {
                CurrentState = ConsentState.Unknown;
                // Ensure JS has default denied consent so GA won't send anything before user choice
                try
                {
                    await _js.InvokeVoidAsync("analyticsConsent.setDefaultDenied");
                }
                catch { }
            }
            else if (stored == "granted")
            {
                CurrentState = ConsentState.Granted;
                try
                {
                    await _js.InvokeVoidAsync("analyticsConsent.updateConsent", "granted");
                    await _js.InvokeVoidAsync("analyticsConsent.loadAnalytics", gtagId);
                }
                catch { }
            }
            else
            {
                CurrentState = ConsentState.Denied;
                try
                {
                    await _js.InvokeVoidAsync("analyticsConsent.updateConsent", "denied");
                }
                catch { }
            }

            OnConsentChanged?.Invoke();
        }

        public async Task AcceptAsync(string gtagId)
        {
            if (_js is null) throw new InvalidOperationException("Service not initialized. Call InitializeAsync first.");

            await _js.InvokeVoidAsync("localStorage.setItem", StorageKey, "granted");
            CurrentState = ConsentState.Granted;

            try
            {
                await _js.InvokeVoidAsync("analyticsConsent.updateConsent", "granted");
                await _js.InvokeVoidAsync("analyticsConsent.loadAnalytics", gtagId);
            }
            catch { }

            OnConsentChanged?.Invoke();
        }

        public async Task DeclineAsync()
        {
            if (_js is null) throw new InvalidOperationException("Service not initialized. Call InitializeAsync first.");

            await _js.InvokeVoidAsync("localStorage.setItem", StorageKey, "denied");
            CurrentState = ConsentState.Denied;

            try
            {
                await _js.InvokeVoidAsync("analyticsConsent.updateConsent", "denied");
            }
            catch { }

            OnConsentChanged?.Invoke();
        }
    }
}
