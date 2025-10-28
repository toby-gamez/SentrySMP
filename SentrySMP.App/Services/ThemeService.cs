using System;
using System.Threading.Tasks;
using System;
using System.Threading.Tasks;
using Microsoft.JSInterop;

namespace SentrySMP.App.Services
{
    /// <summary>
    /// Manages the application theme (dark/light) and persists the preference to localStorage.
    /// Follows the same pattern as CookieConsentService: JS runtime is provided in InitializeAsync.
    /// </summary>
    public class ThemeService
    {
        private const string StorageKey = "dark";
        private IJSRuntime? _js;

        /// <summary>
        /// Current theme state. True when dark mode is enabled.
        /// </summary>
        public bool IsDark { get; private set; }

        /// <summary>
        /// Raised when the theme changes.
        /// </summary>
        public event Action<bool>? OnThemeChanged;

        /// <summary>
        /// Initialize the service. Must be called from a component with IJSRuntime available.
        /// This reads stored preference from localStorage and applies the theme via the JS helper.
        /// </summary>
        public async Task InitializeAsync(IJSRuntime js)
        {
            _js = js ?? throw new ArgumentNullException(nameof(js));

            string? stored = null;
            try
            {
                // Debug log: inform console that we're reading localStorage
                await _js.InvokeVoidAsync("console.debug", "ThemeService.InitializeAsync: reading localStorage key", StorageKey);
                stored = await _js.InvokeAsync<string>("localStorage.getItem", StorageKey);
                await _js.InvokeVoidAsync("console.debug", "ThemeService.InitializeAsync: read value", stored);
            }
            catch
            {
                // ignore
            }

            if (string.IsNullOrEmpty(stored))
            {
                IsDark = false;
            }
            else
            {
                IsDark = string.Equals(stored, "true", StringComparison.OrdinalIgnoreCase);
            }

            try
            {
                await _js.InvokeVoidAsync("console.debug", "ThemeService.InitializeAsync: calling sentryTheme.applyTheme", IsDark);
                await _js.InvokeVoidAsync("sentryTheme.applyTheme", IsDark);
                await _js.InvokeVoidAsync("console.debug", "ThemeService.InitializeAsync: finished calling sentryTheme.applyTheme");
            }
            catch
            {
                // swallow
            }

            OnThemeChanged?.Invoke(IsDark);
        }

        /// <summary>
        /// Set theme and persist to localStorage.
        /// </summary>
        public async Task SetThemeAsync(bool dark)
        {
            if (_js is null) throw new InvalidOperationException("Service not initialized. Call InitializeAsync first.");

            if (IsDark == dark)
                return;

            IsDark = dark;

            try
            {
                await _js.InvokeVoidAsync("console.debug", "ThemeService.SetThemeAsync: setting localStorage key", StorageKey, dark ? "true" : "false");
                await _js.InvokeVoidAsync("localStorage.setItem", StorageKey, dark ? "true" : "false");
            }
            catch { }

            try
            {
                await _js.InvokeVoidAsync("console.debug", "ThemeService.SetThemeAsync: calling sentryTheme.applyTheme", dark);
                await _js.InvokeVoidAsync("sentryTheme.applyTheme", dark);
            }
            catch { }

            OnThemeChanged?.Invoke(dark);
        }
    }
}
