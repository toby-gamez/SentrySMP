using SentrySMP.Shared.DTOs;

namespace SentrySMP.Shared.Interfaces;

public interface IRankService
{
    Task<IEnumerable<RankResponse>> GetAllRanksAsync();
    Task<RankResponse?> GetRankByIdAsync(int id);
    Task<RankResponse> CreateRankAsync(CreateRankDto createRankDto);
    Task<RankResponse?> UpdateRankAsync(int id, UpdateRankDto updateRankDto);
    Task<bool> DeleteRankAsync(int id);
}
