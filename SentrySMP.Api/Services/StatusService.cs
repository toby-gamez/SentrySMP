using System.Net.Http;
using System.Net.Http.Headers;
using System.Text.Json;
using Microsoft.Extensions.Configuration;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.Api.Services
{
    public class StatusService : IStatusService
    {
        private readonly IHttpClientFactory _httpFactory;
        private readonly IConfiguration _configuration;

        public StatusService(IHttpClientFactory httpFactory, IConfiguration configuration)
        {
            _httpFactory = httpFactory;
            _configuration = configuration;
        }

        public async Task<int?> GetDiscordMembersAsync()
        {
            try
            {
                var token = Environment.GetEnvironmentVariable("DISCORD_BOT_TOKEN") ?? _configuration["Discord:BotToken"];
                var guildId = Environment.GetEnvironmentVariable("DISCORD_GUILD_ID") ?? _configuration["Discord:GuildId"] ?? "1159130895190605854";

                if (string.IsNullOrEmpty(token))
                    return null;

                var client = _httpFactory.CreateClient();
                client.DefaultRequestHeaders.Authorization = new AuthenticationHeaderValue("Bot", token);

                var url = $"https://discord.com/api/v10/guilds/{guildId}?with_counts=true";
                var resp = await client.GetAsync(url);
                if (!resp.IsSuccessStatusCode)
                    return null;

                var json = await resp.Content.ReadAsStringAsync();
                using var doc = JsonDocument.Parse(json);
                if (doc.RootElement.TryGetProperty("approximate_member_count", out var countEl) && countEl.ValueKind == JsonValueKind.Number)
                {
                    return countEl.GetInt32();
                }

                return null;
            }
            catch
            {
                return null;
            }
        }

        public async Task<int?> GetMcPlayersAsync()
        {
            try
            {
                var client = _httpFactory.CreateClient();
                var url = "https://api.mcstatus.io/v2/status/java/mc.sentrysmp.eu";
                var resp = await client.GetAsync(url);
                if (!resp.IsSuccessStatusCode) return null;

                var json = await resp.Content.ReadAsStringAsync();
                using var doc = JsonDocument.Parse(json);
                if (doc.RootElement.TryGetProperty("players", out var playersEl) && playersEl.TryGetProperty("online", out var onlineEl) && onlineEl.ValueKind == JsonValueKind.Number)
                {
                    return onlineEl.GetInt32();
                }

                return null;
            }
            catch
            {
                return null;
            }
        }
    }
}
