using System.Net.Http.Headers;
using System.Text;

namespace SentrySMP.Admin.Authentication;

public class AuthenticationHeaderHandler : DelegatingHandler
{
    private readonly CredentialStore _credentialStore;
    private readonly ILogger<AuthenticationHeaderHandler> _logger;

    public AuthenticationHeaderHandler(CredentialStore credentialStore, ILogger<AuthenticationHeaderHandler> logger)
    {
        _credentialStore = credentialStore;
        _logger = logger;
    }

    protected override async Task<HttpResponseMessage> SendAsync(HttpRequestMessage request, CancellationToken cancellationToken)
    {
        (string Username, string Password)? credentials = _credentialStore.Get();
        if (credentials is (var username, var password))
        {
            var byteArray = Encoding.ASCII.GetBytes($"{username}:{password}");
            request.Headers.Authorization = new AuthenticationHeaderValue("Basic", Convert.ToBase64String(byteArray));

            string maskedUser;
            if (string.IsNullOrEmpty(username)) maskedUser = "<empty>";
            else if (username.Length <= 2) maskedUser = new string('*', username.Length);
            else maskedUser = $"{username[0]}{new string('*', Math.Max(1, username.Length - 2))}{username[^1]}";

            _logger.LogDebug("[AuthHandler] Injected Basic Auth for user {MaskedUser} (pwdLen={PwdLen})", maskedUser, password?.Length ?? 0);
        }
        else
        {
            _logger.LogWarning("[AuthHandler] No credentials present");
        }

        return await base.SendAsync(request, cancellationToken);
    }
}