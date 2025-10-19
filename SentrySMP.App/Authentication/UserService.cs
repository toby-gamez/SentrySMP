using System;
using System.Threading.Tasks;
using Microsoft.JSInterop;

namespace SentrySMP.App.Authentication
{
    // Simple service to get/set username in localStorage and notify components on change
    public class UserService
    {
    private const string StorageKey = "minecraft-username";
    private const string EditionKey = "minecraft-edition";
        private readonly IJSRuntime _js;

        private string? _cachedUsername;
        public event Action? OnChange;

        public UserService(IJSRuntime js)
        {
            _js = js;
        }

        public async Task<string?> GetUsernameAsync()
        {
            try
            {
                if (_cachedUsername != null)
                {
                    return _cachedUsername;
                }
                var fromStorage = await _js.InvokeAsync<string?>("localStorage.getItem", StorageKey);
                _cachedUsername = fromStorage;
                return fromStorage;
            }
            catch
            {
                return null;
            }
        }

        public async Task SetUsernameAsync(string? username)
        {
            if (string.IsNullOrEmpty(username))
            {
                await _js.InvokeVoidAsync("localStorage.removeItem", StorageKey);
                _cachedUsername = null;
            }
            else
            {
                await _js.InvokeVoidAsync("localStorage.setItem", StorageKey, username);
                _cachedUsername = username;
            }
            OnChange?.Invoke();
        }

        public async Task<string?> GetEditionAsync()
        {
            try
            {
                var edition = await _js.InvokeAsync<string?>("localStorage.getItem", EditionKey);
                return edition;
            }
            catch
            {
                return null;
            }
        }

        public async Task SetEditionAsync(string? edition)
        {
            if (string.IsNullOrEmpty(edition))
            {
                await _js.InvokeVoidAsync("localStorage.removeItem", EditionKey);
            }
            else
            {
                await _js.InvokeVoidAsync("localStorage.setItem", EditionKey, edition);
            }
            OnChange?.Invoke();
        }

        // Mirror the PHP response structure for client-side use
        public class UserInfo
        {
            public bool LoggedIn { get; set; }
            public string? Username { get; set; }
            public string? Edition { get; set; }
            public string? Skin { get; set; }
        }

        public async Task<UserInfo> GetUserInfoAsync()
        {
            var username = await GetUsernameAsync();
            var edition = await GetEditionAsync() ?? "java";

            var info = new UserInfo
            {
                LoggedIn = !string.IsNullOrEmpty(username),
                Username = username,
                Edition = edition
            };

            if (info.LoggedIn)
            {
                if (string.Equals(edition, "java", StringComparison.OrdinalIgnoreCase))
                {
                    // Java: use user head
                    info.Skin = $"https://minotar.net/helm/{Uri.EscapeDataString(username!)} /100".Replace(" ", "");
                }
                else
                {
                    // Bedrock / cracked: default Steve head
                    info.Skin = "https://minotar.net/helm/MHF_Steve/100";
                }
            }

            return info;
        }
    }
}
