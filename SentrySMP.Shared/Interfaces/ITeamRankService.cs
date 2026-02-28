using SentrySMP.Shared.DTOs;

namespace SentrySMP.Shared.Interfaces;

public interface ITeamRankService
{
    Task<IEnumerable<TeamRankDto>> GetAllAsync();
    Task<TeamRankDto> CreateAsync(TeamRankDto dto);
    Task<TeamRankDto> UpdateAsync(int id, TeamRankDto dto);
    Task DeleteAsync(int id);
}
