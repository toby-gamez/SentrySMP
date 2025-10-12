using SentrySMP.Admin.Authentication;
using Microsoft.JSInterop;

namespace SentrySMP.Admin.Services;

public class AuthInitializationService
{
    private readonly CredentialStore _credentialStore;
    private readonly ILogger<AuthInitializationService> _logger;
    private bool _initialized = false;

    public AuthInitializationService(CredentialStore credentialStore, ILogger<AuthInitializationService> logger)
    {
        _credentialStore = credentialStore;
        _logger = logger;
    }

    public async Task InitializeAsync(IJSRuntime jsRuntime)
    {
        if (_initialized) return;
        
        _logger.LogInformation("AuthInitializationService: Starting initialization");
        await _credentialStore.InitializeAsync(jsRuntime);
        _initialized = true;
        _logger.LogInformation("AuthInitializationService: Initialization completed");
    }
}