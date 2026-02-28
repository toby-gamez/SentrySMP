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
        private const string LeKey = "le"; // light enabled ("true" means light)
        private const string CsKey = "cs"; // color-scheme ("light"|"dark")
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
            bool foundPreference = false;
            try
            {
                // Debug log: inform console that we're reading localStorage
                await _js.InvokeVoidAsync("console.debug", "ThemeService.InitializeAsync: reading localStorage key", StorageKey);
                // Try new keys first: cs, then le, then legacy 'dark'
                try
                {
                    var cs = await _js.InvokeAsync<string>("localStorage.getItem", CsKey);
                    await _js.InvokeVoidAsync("console.debug", "ThemeService.InitializeAsync: read cs", cs);
                    if (!string.IsNullOrEmpty(cs))
                    {
                        foundPreference = true;
                        IsDark = string.Equals(cs, "dark", StringComparison.OrdinalIgnoreCase);
                    }
                    else
                    {
                        var le = await _js.InvokeAsync<string>("localStorage.getItem", LeKey);
                        await _js.InvokeVoidAsync("console.debug", "ThemeService.InitializeAsync: read le", le);
                        if (!string.IsNullOrEmpty(le))
                        {
                            foundPreference = true;
                            // le == "true" means light enabled -> IsDark = false
                            IsDark = !string.Equals(le, "true", StringComparison.OrdinalIgnoreCase);
                        }
                        else
                        {
                            stored = await _js.InvokeAsync<string>("localStorage.getItem", StorageKey);
                            await _js.InvokeVoidAsync("console.debug", "ThemeService.InitializeAsync: read legacy dark", stored);
                            if (!string.IsNullOrEmpty(stored))
                            {
                                foundPreference = true;
                                IsDark = string.Equals(stored, "true", StringComparison.OrdinalIgnoreCase);
                            }
                        }
                    }
                }
                catch
                {
                    // fallback to legacy key read
                    stored = await _js.InvokeAsync<string>("localStorage.getItem", StorageKey);
                    await _js.InvokeVoidAsync("console.debug", "ThemeService.InitializeAsync: read legacy dark fallback", stored);
                    if (!string.IsNullOrEmpty(stored))
                    {
                        foundPreference = true;
                        IsDark = string.Equals(stored, "true", StringComparison.OrdinalIgnoreCase);
                    }
                }
            }
            catch
            {
                // ignore
            }
            // If no stored preference found, leave storage untouched (default "") and do not call JS applyTheme
            if (foundPreference)
            {
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

            // Persist new preference using new keys (le + cs) and keep legacy key for compatibility
            try
            {
                var isLight = !dark;
                await _js.InvokeVoidAsync("console.debug", "ThemeService.SetThemeAsync: setting localStorage le/cs/legacy", isLight, isLight ? "light" : "dark");
                await _js.InvokeVoidAsync("localStorage.setItem", LeKey, isLight ? "true" : "false");
                await _js.InvokeVoidAsync("localStorage.setItem", CsKey, isLight ? "light" : "dark");
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
