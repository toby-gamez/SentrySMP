using System;
using System.Threading.Tasks;
using Microsoft.JSInterop;

namespace SentrySMP.App.Services
{
    /// <summary>
    /// Simple service to toggle the navigation menu CSS class using JS interop.
    /// Pattern matches ThemeService: Initialize with IJSRuntime, then call ToggleAsync.
    /// </summary>
    public class MenuToggleService
    {
        private IJSRuntime? _js;

        public Task InitializeAsync(IJSRuntime js)
        {
            _js = js ?? throw new ArgumentNullException(nameof(js));
            return Task.CompletedTask;
        }

        public async Task ToggleAsync()
        {
            if (_js is null) throw new InvalidOperationException("Service not initialized. Call InitializeAsync first.");
            try
            {
                await _js.InvokeVoidAsync("sentrySMP.toggleNav");
            }
            catch
            {
                // swallow JS errors (menu toggle is non-critical)
            }
        }
    }
}
