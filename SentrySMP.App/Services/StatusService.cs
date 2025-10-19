using System.Threading.Tasks;
using Microsoft.Extensions.Logging;
using SentrySMP.Shared.Interfaces;
using SentrySMP.Shared.DTOs;

namespace SentrySMP.App.Services
{
    public class StatusService
    {
        private readonly ISentryApi _api;
        private readonly ILogger<StatusService> _logger;

        public StatusService(ISentryApi api, ILogger<StatusService> logger)
        {
            _api = api;
            _logger = logger;
        }

        public async Task<int?> GetDiscordMembersAsync()
        {
            try
            {
                var resp = await _api.GetDiscordStatusAsync();
                if (resp == null)
                {
                    _logger.LogWarning("Discord response was null when calling ISentryApi.GetDiscordStatusAsync");
                }
                return resp?.total;
            }
            catch (Refit.ApiException ae)
            {
                _logger.LogWarning(ae, "API error while calling GetDiscordStatusAsync");
                return null;
            }
            catch (System.Exception ex)
            {
                _logger.LogWarning(ex, "Unexpected error when fetching Discord members via ISentryApi");
                return null;
            }
        }

        public async Task<int?> GetMcPlayersAsync()
        {
            try
            {
                var resp = await _api.GetMcStatusAsync();
                if (resp == null)
                {
                    _logger.LogWarning("MC response was null when calling ISentryApi.GetMcStatusAsync");
                }
                return resp?.players;
            }
            catch (Refit.ApiException ae)
            {
                _logger.LogWarning(ae, "API error while calling GetMcStatusAsync");
                return null;
            }
            catch (System.Exception ex)
            {
                _logger.LogWarning(ex, "Unexpected error when fetching MC players via ISentryApi");
                return null;
            }
        }
    }
}
