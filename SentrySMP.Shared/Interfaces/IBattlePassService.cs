using SentrySMP.Shared.DTOs;

namespace SentrySMP.Shared.Interfaces;

public interface IBattlePassService
{
    Task<IEnumerable<BattlePassResponse>> GetAllBattlePassesAsync();
    Task<IEnumerable<BattlePassResponse>> GetBattlePassesByServerIdAsync(int serverId);
    Task<BattlePassResponse?> GetBattlePassByIdAsync(int id);
    Task<BattlePassResponse> CreateBattlePassAsync(CreateBattlePassDto createDto);
    Task<BattlePassResponse?> UpdateBattlePassAsync(int id, UpdateBattlePassDto updateDto);
    Task<bool> DeleteBattlePassAsync(int id);
}
