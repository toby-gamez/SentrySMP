using System.Threading.Tasks;

namespace SentrySMP.Shared.Interfaces
{
    public interface IStatusService
    {
        Task<int?> GetDiscordMembersAsync();
        Task<int?> GetMcPlayersAsync();
    }
}
