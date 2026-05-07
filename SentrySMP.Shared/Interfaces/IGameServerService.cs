using SentrySMP.Shared.DTOs;

namespace SentrySMP.Shared.Interfaces
{
    public interface IGameServerService
    {
        Task<PlayerInfoResponse?> GetPlayerInfoAsync(string username);
        Task<BanlistResponse?> GetBanlistAsync();
    }
}
