using Microsoft.AspNetCore.Components.Authorization;
using Microsoft.JSInterop;

namespace SentrySMP.Admin.Authentication;

public class CredentialStore
{
    private (string Username, string Password)? _credentials;
    private readonly ILogger<CredentialStore> _logger;
    private readonly IServiceProvider _serviceProvider;
    
    // Event to notify when credentials change
    public event Action? CredentialsChanged;

    public CredentialStore(ILogger<CredentialStore> logger, IServiceProvider serviceProvider)
    {
        _logger = logger;
        _serviceProvider = serviceProvider;
    }

    public async Task InitializeAsync(IJSRuntime jsRuntime)
    {
        try
        {
            _logger.LogInformation("CredentialStore: Starting localStorage initialization");
            var username = await jsRuntime.InvokeAsync<string?>("localStorage.getItem", "blinked_username");
            var password = await jsRuntime.InvokeAsync<string?>("localStorage.getItem", "blinked_password");
            
            _logger.LogInformation("CredentialStore: Retrieved from localStorage - username: {HasUsername}, password: {HasPassword}", 
                !string.IsNullOrEmpty(username), !string.IsNullOrEmpty(password));
            
            if (!string.IsNullOrEmpty(username) && !string.IsNullOrEmpty(password))
            {
                _credentials = (username, password);
                _logger.LogInformation("CredentialStore: Restored credentials for user {Username}", username);
                
                // Notify listeners
                _logger.LogInformation("CredentialStore: Notifying {ListenerCount} listeners", CredentialsChanged?.GetInvocationList().Length ?? 0);
                CredentialsChanged?.Invoke();

                // Also try to notify AuthenticationStateProvider in case it wasn't subscribed yet
                try
                {
                    var authStateProvider = _serviceProvider.GetService(typeof(AuthenticationStateProvider)) as AuthenticationStateProvider;
                    if (authStateProvider is SentrySMP.Admin.Authentication.BasicAuthenticationStateProvider basicAuth)
                    {
                        basicAuth.NotifyAuthenticationStateChanged();
                    }
                }
                catch (Exception ex)
                {
                    _logger.LogWarning(ex, "CredentialStore: Failed to notify AuthenticationStateProvider");
                }
            }
            else
            {
                _logger.LogInformation("CredentialStore: No valid credentials found in localStorage");
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "CredentialStore: Failed to restore credentials from localStorage");
        }
    }

    public async Task SetAsync(string username, string password, IJSRuntime jsRuntime)
    {
        _credentials = (username, password);
        
        try
        {
            await jsRuntime.InvokeVoidAsync("localStorage.setItem", "blinked_username", username);
            await jsRuntime.InvokeVoidAsync("localStorage.setItem", "blinked_password", password);
            _logger.LogDebug("Stored credentials in localStorage for user {Username}", username);
        }
        catch (Exception ex)
        {
            _logger.LogWarning(ex, "Failed to store credentials in localStorage");
        }
        
        CredentialsChanged?.Invoke();

        try
        {
            var authStateProvider = _serviceProvider.GetService(typeof(AuthenticationStateProvider)) as AuthenticationStateProvider;
            if (authStateProvider is SentrySMP.Admin.Authentication.BasicAuthenticationStateProvider basicAuth)
            {
                basicAuth.NotifyAuthenticationStateChanged();
            }
        }
        catch (Exception ex)
        {
            _logger.LogWarning(ex, "CredentialStore: Failed to notify AuthenticationStateProvider");
        }
    }

    public void Set(string username, string password)
    {
        _credentials = (username, password);
        CredentialsChanged?.Invoke();

        try
        {
            var authStateProvider = _serviceProvider.GetService(typeof(AuthenticationStateProvider)) as AuthenticationStateProvider;
            if (authStateProvider is SentrySMP.Admin.Authentication.BasicAuthenticationStateProvider basicAuth)
            {
                basicAuth.NotifyAuthenticationStateChanged();
            }
        }
        catch (Exception ex)
        {
            _logger.LogWarning(ex, "CredentialStore: Failed to notify AuthenticationStateProvider");
        }
    }

    public (string Username, string Password)? Get()
    {
        return _credentials;
    }

    public async Task ClearAsync(IJSRuntime jsRuntime)
    {
        _credentials = null;
        
        try
        {
            await jsRuntime.InvokeVoidAsync("localStorage.removeItem", "blinked_username");
            await jsRuntime.InvokeVoidAsync("localStorage.removeItem", "blinked_password");
            _logger.LogDebug("Cleared credentials from localStorage");
        }
        catch (Exception ex)
        {
            _logger.LogWarning(ex, "Failed to clear credentials from localStorage");
        }
        
        CredentialsChanged?.Invoke();
    }

    public void Clear()
    {
        _credentials = null;
        CredentialsChanged?.Invoke();
    }
}