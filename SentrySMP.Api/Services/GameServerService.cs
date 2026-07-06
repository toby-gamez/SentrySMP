using System.Net.Http.Headers;
using System.Text.Json;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Logging;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.Api.Services
{
    public class GameServerService : IGameServerService
    {
        private readonly IHttpClientFactory _httpClientFactory;
        private readonly IConfiguration _configuration;
        private readonly ILogger<GameServerService> _logger;

        public GameServerService(IHttpClientFactory httpClientFactory, IConfiguration configuration, ILogger<GameServerService> logger)
        {
            _httpClientFactory = httpClientFactory;
            _configuration = configuration;
            _logger = logger;
        }

        private static int? TryReadNullableInt(JsonElement root, string name)
        {
            try
            {
                if (!root.TryGetProperty(name, out var el) || el.ValueKind == JsonValueKind.Null)
                    return null;

                if (el.ValueKind == JsonValueKind.Number)
                {
                    // try as Int32
                    if (el.TryGetInt32(out var i32)) return i32;
                    // fallback to Int64 then clamp
                    if (el.TryGetInt64(out var i64))
                    {
                        if (i64 > int.MaxValue) return int.MaxValue;
                        if (i64 < int.MinValue) return int.MinValue;
                        return (int)i64;
                    }
                    // as decimal
                    if (el.TryGetDecimal(out var dec))
                    {
                        var rounded = (int)Math.Round(dec, 0);
                        return rounded;
                    }
                }

                if (el.ValueKind == JsonValueKind.String)
                {
                    var s = el.GetString();
                    if (string.IsNullOrWhiteSpace(s)) return null;
                    s = s.Trim();
                    // Handle common thousand separators: comma, space, and dot.
                    // If string contains more than one dot it's almost certainly using dots as thousand separators (e.g. "1.000.600"); remove dots in that case.
                    var dotCount = s.Count(c => c == '.');
                    if (dotCount > 1)
                    {
                        s = s.Replace(".", string.Empty);
                    }
                    s = s.Replace(",", string.Empty).Replace(" ", string.Empty);
                    if (int.TryParse(s, System.Globalization.NumberStyles.Any, System.Globalization.CultureInfo.InvariantCulture, out var pi)) return pi;
                    if (long.TryParse(s, System.Globalization.NumberStyles.Any, System.Globalization.CultureInfo.InvariantCulture, out var pl))
                    {
                        if (pl > int.MaxValue) return int.MaxValue;
                        if (pl < int.MinValue) return int.MinValue;
                        return (int)pl;
                    }
                    if (decimal.TryParse(s, System.Globalization.NumberStyles.Any, System.Globalization.CultureInfo.InvariantCulture, out var pd))
                    {
                        return (int)Math.Round(pd, 0);
                    }
                }
            }
            catch { }
            return null;
        }

        // Read a nullable int directly from a JsonElement that may be Number or String.
        private static int? ReadNullableIntFromElement(JsonElement el)
        {
            try
            {
                if (el.ValueKind == JsonValueKind.Null) return null;

                if (el.ValueKind == JsonValueKind.Number)
                {
                    if (el.TryGetInt32(out var i32)) return i32;
                    if (el.TryGetInt64(out var i64))
                    {
                        if (i64 > int.MaxValue) return int.MaxValue;
                        if (i64 < int.MinValue) return int.MinValue;
                        return (int)i64;
                    }
                    if (el.TryGetDecimal(out var dec))
                    {
                        return (int)Math.Round(dec, 0);
                    }
                }

                if (el.ValueKind == JsonValueKind.String)
                {
                    var s = el.GetString();
                    if (string.IsNullOrWhiteSpace(s)) return null;
                    s = s.Trim();
                    var dotCount = s.Count(c => c == '.');
                    if (dotCount > 1) s = s.Replace(".", string.Empty);
                    s = s.Replace(",", string.Empty).Replace(" ", string.Empty);
                    if (int.TryParse(s, System.Globalization.NumberStyles.Any, System.Globalization.CultureInfo.InvariantCulture, out var pi)) return pi;
                    if (long.TryParse(s, System.Globalization.NumberStyles.Any, System.Globalization.CultureInfo.InvariantCulture, out var pl))
                    {
                        if (pl > int.MaxValue) return int.MaxValue;
                        if (pl < int.MinValue) return int.MinValue;
                        return (int)pl;
                    }
                    if (decimal.TryParse(s, System.Globalization.NumberStyles.Any, System.Globalization.CultureInfo.InvariantCulture, out var pd))
                    {
                        return (int)Math.Round(pd, 0);
                    }
                }
            }
            catch { }
            return null;
        }

        // Try to locate a property by name with a few fallbacks (case-insensitive, inside a "data" wrapper)
        private static bool TryFindProperty(JsonElement root, string name, out JsonElement element)
        {
            // direct
            if (root.ValueKind == JsonValueKind.Object && root.TryGetProperty(name, out element)) return true;

            // case-insensitive
            if (root.ValueKind == JsonValueKind.Object)
            {
                foreach (var prop in root.EnumerateObject())
                {
                    if (string.Equals(prop.Name, name, StringComparison.OrdinalIgnoreCase))
                    {
                        element = prop.Value;
                        return true;
                    }
                }
            }

            // inside wrapper `data`
            if (root.ValueKind == JsonValueKind.Object && root.TryGetProperty("data", out var dataEl) && dataEl.ValueKind == JsonValueKind.Object)
            {
                if (dataEl.TryGetProperty(name, out element)) return true;
                foreach (var prop in dataEl.EnumerateObject())
                {
                    if (string.Equals(prop.Name, name, StringComparison.OrdinalIgnoreCase))
                    {
                        element = prop.Value;
                        return true;
                    }
                }
            }

            element = default;
            return false;
        }

        public async Task<PlayerInfoResponse?> GetPlayerInfoAsync(string username)
        {
            if (string.IsNullOrWhiteSpace(username)) return null;

            var baseUrl = _configuration["GameServer:BaseUrl"] ?? _configuration["Delivery:ApiUrl"] ?? string.Empty;
            var apiKey = _configuration["GameServer:ApiKey"] ?? _configuration["Delivery:ApiKey"] ?? string.Empty;

            if (string.IsNullOrWhiteSpace(baseUrl))
            {
                _logger.LogWarning("GameServer base URL is not configured");
                return null;
            }

            try
            {
                var client = _httpClientFactory.CreateClient();
                client.DefaultRequestHeaders.Clear();
                client.DefaultRequestHeaders.Add("accept", "application/json");
                if (!string.IsNullOrWhiteSpace(apiKey))
                    client.DefaultRequestHeaders.Add("X-API-Key", apiKey);

                // Trim slashes and build url
                var trimmed = baseUrl.TrimEnd('/');
                var url = $"{trimmed}/player/{Uri.EscapeDataString(username)}";
                var httpResp = await client.GetAsync(url);
                var body = await httpResp.Content.ReadAsStringAsync();
                if (!httpResp.IsSuccessStatusCode)
                {
                    _logger.LogWarning("GameServer API returned HTTP {Status} for {Url}: {Body}", (int)httpResp.StatusCode, url, body);
                    return new PlayerInfoResponse { Player = username, Error = $"HTTP {(int)httpResp.StatusCode}" };
                }

                // Robust parsing: the remote API may return nulls, strings, or out-of-range numbers.
                try
                {
                    using var doc = JsonDocument.Parse(body);
                    var root = doc.RootElement;
                    var resp = new PlayerInfoResponse();

                    if (TryFindProperty(root, "player", out var playerEl) && playerEl.ValueKind != JsonValueKind.Null)
                        resp.Player = playerEl.GetString();

                    // coins (nullable int) - be permissive and try several fallbacks
                    if (TryFindProperty(root, "coins", out var coinsEl) && coinsEl.ValueKind != JsonValueKind.Null)
                    {
                        resp.Gems = ReadNullableIntFromElement(coinsEl);
                    }
                    else
                    {
                        resp.Gems = null;
                    }

                    // money (nullable int)
                    if (TryFindProperty(root, "money", out var moneyEl) && moneyEl.ValueKind != JsonValueKind.Null)
                    {
                        resp.Money = ReadNullableIntFromElement(moneyEl);
                    }
                    else
                    {
                        resp.Money = null;
                    }

                    if (TryFindProperty(root, "rank", out var rankEl) && rankEl.ValueKind != JsonValueKind.Null)
                        resp.Rank = rankEl.GetString();

                    if (TryFindProperty(root, "error", out var errEl) && errEl.ValueKind != JsonValueKind.Null)
                        resp.Error = errEl.GetString();

                    // statistics (new in API): permissive parsing, keep values nullable
                    if (TryFindProperty(root, "statistics", out var statsEl) && statsEl.ValueKind == JsonValueKind.Object)
                    {
                        var stats = new PlayerStatistics();
                        if (TryFindProperty(statsEl, "playTimeSeconds", out var ptsEl) && ptsEl.ValueKind != JsonValueKind.Null)
                            stats.PlayTimeSeconds = ReadNullableLongFromElement(ptsEl);
                        if (TryFindProperty(statsEl, "playTimeTicks", out var pttEl) && pttEl.ValueKind != JsonValueKind.Null)
                            stats.PlayTimeTicks = ReadNullableLongFromElement(pttEl);
                        if (TryFindProperty(statsEl, "deaths", out var dEl) && dEl.ValueKind != JsonValueKind.Null)
                            stats.Deaths = ReadNullableIntFromElement(dEl);
                        if (TryFindProperty(statsEl, "playerKills", out var pkEl) && pkEl.ValueKind != JsonValueKind.Null)
                            stats.PlayerKills = ReadNullableIntFromElement(pkEl);
                        if (TryFindProperty(statsEl, "mobsKilled", out var mkEl) && mkEl.ValueKind != JsonValueKind.Null)
                            stats.MobsKilled = ReadNullableIntFromElement(mkEl);
                        if (TryFindProperty(statsEl, "blocksTravelled", out var btEl) && btEl.ValueKind != JsonValueKind.Null)
                            stats.BlocksTravelled = ReadNullableLongFromElement(btEl);

                        resp.Statistics = stats;
                    }

                    // Additional logging for diagnostics when values are missing
                    if (resp.Gems == null || resp.Money == null)
                    {
                        _logger.LogInformation("GameServer parsed response for {User}: Player={Player} Rank={Rank} Gemss={GemssRaw} Money={MoneyRaw} Error={Error}. RawBody={Body}", username, resp.Player, resp.Rank, resp.Gems?.ToString() ?? "null", resp.Money?.ToString() ?? "null", resp.Error, body);
                    }

                    return resp;
                }
                catch (Exception exParse)
                {
                    _logger.LogWarning(exParse, "Could not parse GameServer response JSON for user {User}", username);
                    System.Console.WriteLine($"GameServerService: JSON parse error for {username}: {exParse.Message}");
                    return new PlayerInfoResponse { Player = username, Error = exParse.Message };
                }
            }
            catch (Exception ex)
            {
                _logger.LogWarning(ex, "Failed to call GameServer API for user {User}", username);
                return new PlayerInfoResponse { Player = username, Error = ex.Message };
            }
        }

        // Read a nullable long directly from a JsonElement that may be Number or String.
        private static long? ReadNullableLongFromElement(JsonElement el)
        {
            try
            {
                if (el.ValueKind == JsonValueKind.Null) return null;

                if (el.ValueKind == JsonValueKind.Number)
                {
                    if (el.TryGetInt64(out var i64)) return i64;
                    if (el.TryGetDecimal(out var dec))
                    {
                        return (long)Math.Round(dec, 0);
                    }
                }

                if (el.ValueKind == JsonValueKind.String)
                {
                    var s = el.GetString();
                    if (string.IsNullOrWhiteSpace(s)) return null;
                    s = s.Trim();
                    var dotCount = s.Count(c => c == '.');
                    if (dotCount > 1) s = s.Replace(".", string.Empty);
                    s = s.Replace(",", string.Empty).Replace(" ", string.Empty);
                    if (long.TryParse(s, System.Globalization.NumberStyles.Any, System.Globalization.CultureInfo.InvariantCulture, out var pl)) return pl;
                    if (decimal.TryParse(s, System.Globalization.NumberStyles.Any, System.Globalization.CultureInfo.InvariantCulture, out var pd))
                    {
                        return (long)Math.Round(pd, 0);
                    }
                }
            }
            catch { }
            return null;
        }

        public async Task<OnlinePlayersResponse?> GetOnlinePlayersAsync()
        {
            var baseUrl = _configuration["GameServer:BaseUrl"] ?? _configuration["Delivery:ApiUrl"] ?? string.Empty;
            var apiKey = _configuration["GameServer:ApiKey"] ?? _configuration["Delivery:ApiKey"] ?? string.Empty;

            if (string.IsNullOrWhiteSpace(baseUrl))
            {
                _logger.LogWarning("GameServer base URL is not configured");
                return null;
            }

            try
            {
                var client = _httpClientFactory.CreateClient();
                client.DefaultRequestHeaders.Clear();
                client.DefaultRequestHeaders.Add("accept", "application/json");
                if (!string.IsNullOrWhiteSpace(apiKey))
                    client.DefaultRequestHeaders.Add("X-API-Key", apiKey);

                var url = $"{baseUrl.TrimEnd('/')}/players";
                var httpResp = await client.GetAsync(url);
                var body = await httpResp.Content.ReadAsStringAsync();

                if (!httpResp.IsSuccessStatusCode)
                {
                    _logger.LogWarning("GameServer /players returned HTTP {Status}: {Body}", (int)httpResp.StatusCode, body);
                    return new OnlinePlayersResponse { Error = $"HTTP {(int)httpResp.StatusCode}" };
                }

                using var doc = JsonDocument.Parse(body);
                var result = new OnlinePlayersResponse();

                if (doc.RootElement.TryGetProperty("players", out var playersEl) && playersEl.ValueKind == JsonValueKind.Array)
                {
                    foreach (var item in playersEl.EnumerateArray())
                    {
                        var entry = new OnlinePlayerEntry();
                        if (item.TryGetProperty("name", out var nameEl) && nameEl.ValueKind != JsonValueKind.Null)
                            entry.Name = nameEl.GetString();
                        if (item.TryGetProperty("uuid", out var uuidEl) && uuidEl.ValueKind != JsonValueKind.Null)
                            entry.Uuid = uuidEl.GetString();
                        if (item.TryGetProperty("rank", out var rankEl) && rankEl.ValueKind != JsonValueKind.Null)
                            entry.Rank = rankEl.GetString();
                        result.Players.Add(entry);
                    }
                }

                return result;
            }
            catch (Exception ex)
            {
                _logger.LogWarning(ex, "Failed to call GameServer /players endpoint");
                return new OnlinePlayersResponse { Error = ex.Message };
            }
        }

        public async Task<int?> GetOnlinePlayerCountAsync()
        {
            var result = await GetOnlinePlayersAsync();
            if (result == null || result.Error != null) return null;
            return result.Players.Count;
        }

        public async Task<BanlistResponse?> GetBanlistAsync()
        {
            var baseUrl = _configuration["GameServer:BaseUrl"] ?? _configuration["Delivery:ApiUrl"] ?? string.Empty;
            var apiKey = _configuration["GameServer:ApiKey"] ?? _configuration["Delivery:ApiKey"] ?? string.Empty;

            if (string.IsNullOrWhiteSpace(baseUrl))
            {
                _logger.LogWarning("GameServer base URL is not configured");
                return null;
            }

            try
            {
                var client = _httpClientFactory.CreateClient();
                client.DefaultRequestHeaders.Clear();
                client.DefaultRequestHeaders.Add("accept", "application/json");
                if (!string.IsNullOrWhiteSpace(apiKey))
                    client.DefaultRequestHeaders.Add("X-API-Key", apiKey);

                var url = $"{baseUrl.TrimEnd('/')}/banlist";
                var httpResp = await client.GetAsync(url);
                var body = await httpResp.Content.ReadAsStringAsync();

                if (!httpResp.IsSuccessStatusCode)
                {
                    _logger.LogWarning("GameServer /banlist returned HTTP {Status}: {Body}", (int)httpResp.StatusCode, body);
                    return new BanlistResponse { Error = $"HTTP {(int)httpResp.StatusCode}" };
                }

                try
                {
                    using var doc = JsonDocument.Parse(body);
                    var root = doc.RootElement;
                    var result = new BanlistResponse();

                    if (root.TryGetProperty("banned", out var bannedEl) && bannedEl.ValueKind == JsonValueKind.Array)
                    {
                        foreach (var item in bannedEl.EnumerateArray())
                        {
                            var entry = new BannedEntry();
                            if (item.TryGetProperty("name", out var nameEl) && nameEl.ValueKind != JsonValueKind.Null)
                                entry.Name = nameEl.GetString();
                            if (item.TryGetProperty("uuid", out var uuidEl) && uuidEl.ValueKind != JsonValueKind.Null)
                                entry.Uuid = uuidEl.GetString();
                            if (item.TryGetProperty("reason", out var reasonEl) && reasonEl.ValueKind != JsonValueKind.Null)
                                entry.Reason = reasonEl.GetString();
                            result.Banned.Add(entry);
                        }
                    }

                    return result;
                }
                catch (Exception exParse)
                {
                    _logger.LogWarning(exParse, "Could not parse GameServer /banlist response JSON");
                    return new BanlistResponse { Error = exParse.Message };
                }
            }
            catch (Exception ex)
            {
                _logger.LogWarning(ex, "Failed to call GameServer /banlist endpoint");
                return new BanlistResponse { Error = ex.Message };
            }
        }
    }
}
