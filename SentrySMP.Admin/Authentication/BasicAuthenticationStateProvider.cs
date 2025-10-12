using System.Security.Claims;
using Microsoft.AspNetCore.Components.Authorization;

namespace SentrySMP.Admin.Authentication;

public class BasicAuthenticationStateProvider : AuthenticationStateProvider, IDisposable
{
    private readonly CredentialStore _credentialStore;
    private readonly ILogger<BasicAuthenticationStateProvider> _logger;

    public BasicAuthenticationStateProvider(
        CredentialStore credentialStore,
        ILogger<BasicAuthenticationStateProvider> logger)
    {
        _credentialStore = credentialStore;
        _logger = logger;
        
        // Subscribe to credential changes
        _credentialStore.CredentialsChanged += OnCredentialsChanged;
    }

    public override Task<AuthenticationState> GetAuthenticationStateAsync()
    {
        var credentials = _credentialStore.Get();
        
        if (credentials.HasValue)
        {
            var claims = new[]
            {
                new Claim(ClaimTypes.Name, credentials.Value.Username),
                new Claim(ClaimTypes.NameIdentifier, credentials.Value.Username)
            };

            var identity = new ClaimsIdentity(claims, "basic");
            var principal = new ClaimsPrincipal(identity);
            
            _logger.LogDebug("User {Username} is authenticated", credentials.Value.Username);
            return Task.FromResult(new AuthenticationState(principal));
        }

        _logger.LogDebug("No authentication credentials found");
        return Task.FromResult(new AuthenticationState(new ClaimsPrincipal(new ClaimsIdentity())));
    }

    private void OnCredentialsChanged()
    {
        NotifyAuthenticationStateChanged(GetAuthenticationStateAsync());
    }

    public void NotifyAuthenticationStateChanged()
    {
        NotifyAuthenticationStateChanged(GetAuthenticationStateAsync());
    }

    public void Dispose()
    {
        _credentialStore.CredentialsChanged -= OnCredentialsChanged;
    }
}