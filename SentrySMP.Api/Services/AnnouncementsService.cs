using System.Net.Http.Headers;
using System.Text.Json;
using System.Text.RegularExpressions;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Logging;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.Api.Services
{
    public class AnnouncementsService : IAnnouncementsService
    {
        private readonly IHttpClientFactory _httpFactory;
        private readonly IConfiguration _configuration;
        private readonly ILogger<AnnouncementsService> _logger;

        public AnnouncementsService(IHttpClientFactory httpFactory, IConfiguration configuration, ILogger<AnnouncementsService> logger)
        {
            _httpFactory = httpFactory;
            _configuration = configuration;
            _logger = logger;
        }

        public async Task<IEnumerable<AnnouncementDto>> GetLatestAnnouncementsAsync()
        {
            var channelId = Environment.GetEnvironmentVariable("DISCORD_CHANNEL_ID") ?? _configuration["Discord:ChannelId"] ?? _configuration["Discord:ChannelID"];
            if (string.IsNullOrEmpty(channelId))
            {
                _logger.LogWarning("Missing DISCORD_CHANNEL_ID configuration");
                return Enumerable.Empty<AnnouncementDto>();
            }
            return await FetchFromChannelAsync(channelId, authorFilter: null, limit: 20);
        }

        public async Task<IEnumerable<AnnouncementDto>> GetServerChangelogAsync()
        {
            return await FetchFromChannelAsync("1477752609463861442", authorFilter: null, excludeAuthor: "tobygamez7747", limit: 100);
        }

        private async Task<IEnumerable<AnnouncementDto>> FetchFromChannelAsync(string channelId, string? authorFilter, string? excludeAuthor = null, int limit = 20)
        {
            try
            {
                var botToken = Environment.GetEnvironmentVariable("DISCORD_BOT_TOKEN") ?? _configuration["Discord:BotToken"];

                if (string.IsNullOrEmpty(botToken))
                {
                    _logger.LogWarning("Missing DISCORD_BOT_TOKEN configuration");
                    return Enumerable.Empty<AnnouncementDto>();
                }

                var client = _httpFactory.CreateClient();
                client.DefaultRequestHeaders.Authorization = new AuthenticationHeaderValue("Bot", botToken);
                client.DefaultRequestHeaders.UserAgent.ParseAdd("SentrySMP-Website/1.0");

                var url = $"https://discord.com/api/v10/channels/{channelId}/messages?limit={limit}";
                var resp = await client.GetAsync(url);
                if (!resp.IsSuccessStatusCode)
                {
                    _logger.LogWarning("Discord API returned non-success: {Status}", resp.StatusCode);
                    return Enumerable.Empty<AnnouncementDto>();
                }

                var json = await resp.Content.ReadAsStringAsync();
                using var doc = JsonDocument.Parse(json);
                var root = doc.RootElement;
                if (root.ValueKind != JsonValueKind.Array)
                {
                    _logger.LogWarning("Unexpected Discord API response shape");
                    return Enumerable.Empty<AnnouncementDto>();
                }

                var results = new List<AnnouncementDto>();
                int idCounter = 1;
                foreach (var msg in root.EnumerateArray())
                {
                    try
                    {
                        // Skip bot messages
                        if (msg.TryGetProperty("author", out var authorEl) && authorEl.TryGetProperty("bot", out var botEl) && botEl.ValueKind == JsonValueKind.True)
                        {
                            _logger.LogDebug("Skipping bot message from {Author}", authorEl.GetPropertyOrDefault("username"));
                            continue;
                        }

                        // Filter by author if specified
                        var authorName = msg.TryGetProperty("author", out var ael) ? ael.GetPropertyOrDefault("username") : "Unknown";
                        if (authorFilter is not null && !string.Equals(authorName, authorFilter, StringComparison.OrdinalIgnoreCase))
                            continue;
                        if (excludeAuthor is not null && string.Equals(authorName, excludeAuthor, StringComparison.OrdinalIgnoreCase))
                            continue;

                        string content = msg.GetPropertyOrDefault("content");

                        // Try embeds
                        if (string.IsNullOrWhiteSpace(content) && msg.TryGetProperty("embeds", out var embeds) && embeds.ValueKind == JsonValueKind.Array && embeds.GetArrayLength() > 0)
                        {
                            var embed = embeds[0];
                            var title = embed.GetPropertyOrDefault("title");
                            var desc = embed.GetPropertyOrDefault("description");
                            content = (title + "\n" + desc).Trim();
                        }

                        // Try attachments
                        if (string.IsNullOrWhiteSpace(content) && msg.TryGetProperty("attachments", out var attachments) && attachments.ValueKind == JsonValueKind.Array && attachments.GetArrayLength() > 0)
                        {
                            var names = attachments.EnumerateArray().Select(a => a.GetPropertyOrDefault("filename")).Where(x => !string.IsNullOrEmpty(x));
                            content = "\U0001f4ce " + string.Join(", ", names);
                        }

                        // If still empty, placeholder
                        if (string.IsNullOrWhiteSpace(content))
                        {
                            var tsStr = msg.GetPropertyOrDefault("timestamp");
                            if (DateTime.TryParse(tsStr, out var mdate))
                            {
                                content = $"*[Message from {{mdate:yyyy-MM-dd HH:mm}} - no content available]*";
                            }
                            else
                            {
                                content = "*[Message - no content available]*";
                            }
                        }

                        // Extract a title heuristically
                        var lines = content.Split('\n');
                        var titleCandidate = "Announcement";
                        if (lines.Length > 1)
                        {
                            var firstLine = lines[0].Trim();
                            if (firstLine.Length < 120 && firstLine.Length > 3 && (firstLine.Contains("**") || firstLine.Contains("__") || firstLine.StartsWith("#") || Regex.IsMatch(firstLine, "^[A-Z][^.!?]*$") || lines.Length > 2))
                            {
                                titleCandidate = Regex.Replace(firstLine, @"^#+\s*", "").Trim();
                                titleCandidate = Regex.Replace(titleCandidate, @"(\*\*|__|~~|`)", "").Trim();
                                content = string.Join("\n", lines.Skip(1)).Trim();
                            }
                        }

                        if (titleCandidate == "Announcement" && content.Length > 50)
                        {
                            var words = content.Split(' ', StringSplitOptions.RemoveEmptyEntries);
                            titleCandidate = string.Join(' ', words.Take(Math.Min(6, words.Length))) + (words.Length > 6 ? "..." : "");
                        }

                        // Convert Discord markdown to standard markdown/html-friendly text
                        content = ConvertDiscordMarkdown(content);

                        var createdAtStr = msg.GetPropertyOrDefault("timestamp");
                        DateTime createdAt = DateTime.UtcNow;
                        if (!string.IsNullOrEmpty(createdAtStr) && DateTime.TryParse(createdAtStr, out var parsed))
                            createdAt = parsed;

                        results.Add(new AnnouncementDto
                        {
                            Id = idCounter++,
                            Title = titleCandidate,
                            Author = authorName,
                            Content = content,
                            CreatedAt = createdAt
                        });
                    }
                    catch (Exception ex)
                    {
                        _logger.LogDebug(ex, "Error processing a Discord message element, skipping");
                        continue;
                    }
                }

                _logger.LogInformation("Processed {Count} messages from channel {ChannelId}", results.Count, channelId);
                return results;
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Unexpected error while fetching messages from Discord channel {ChannelId}", channelId);
                return Enumerable.Empty<AnnouncementDto>();
            }
        }

        private static string ConvertDiscordMarkdown(string text)
        {
            if (string.IsNullOrEmpty(text)) return string.Empty;

            // Remove @everyone and @here
            text = text.Replace("@everyone", "").Replace("@here", "");

            // Spoilers ||text|| -> <span class="spoiler">text</span>
            text = Regex.Replace(text, @"\|\|(.*?)\|\|", "<span class=\"spoiler\">$1</span>", RegexOptions.Singleline);

            // Mentions <@...> -> @user
            text = Regex.Replace(text, "<@!?(\\d+)>", "@user");

            // Role mentions <@&...> -> @role
            text = Regex.Replace(text, "<@&(\\d+)>", "@role");

            // Channel mentions <#...> -> #channel
            text = Regex.Replace(text, "<#(\\d+)>", "#channel");

            // Custom emoji <:name:id> or <a:name:id> -> :name:
            text = Regex.Replace(text, "<a?:(.*?):\\d+>", ":$1:");

            // Timestamps <t:...> => convert epoch to readable
            text = Regex.Replace(text, "<t:(\\d+)(?::[tTdDfFR])?>", match =>
            {
                if (long.TryParse(match.Groups[1].Value, out var seconds))
                {
                    var dt = DateTimeOffset.FromUnixTimeSeconds(seconds).UtcDateTime;
                    return dt.ToString("yyyy-MM-dd HH:mm:ss");
                }
                return match.Value;
            });

            // Underline __text__ -> <u>text</u>
            text = Regex.Replace(text, "__(.*?)__", "<u>$1</u>", RegexOptions.Singleline);

            // Preserve line breaks for markdown rendering by converting single line breaks to double
            text = text.Replace("\r\n", "\n").Replace("\n\n", "\n\n");
            text = Regex.Replace(text, "(?<!\n)\n(?!\n)", "\n\n");

            // Trim and normalize spaces
            text = Regex.Replace(text, "[ \t]+", " ").Trim();

            return text;
        }
    }

    internal static class JsonElementExtensions
    {
        public static string GetPropertyOrDefault(this JsonElement el, string name)
        {
            if (el.ValueKind == JsonValueKind.Undefined || el.ValueKind == JsonValueKind.Null) return string.Empty;
            if (el.TryGetProperty(name, out var prop) && prop.ValueKind == JsonValueKind.String)
                return prop.GetString() ?? string.Empty;
            return string.Empty;
        }
    }
}
