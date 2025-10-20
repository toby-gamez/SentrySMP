using SentrySMP.Shared.DTOs;

namespace SentrySMP.Shared.Interfaces;

public interface ITeamService
{
    Task<TeamResponseDto> GetTeamAsync();
    Task SaveTeamAsync(TeamResponseDto dto);
}
