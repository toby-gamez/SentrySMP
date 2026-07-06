using SentrySMP.Shared.DTOs;

namespace SentrySMP.Shared.Interfaces;

public interface IGemService
{
    Task<IEnumerable<GemResponse>> GetAllGemsAsync();
    Task<IEnumerable<GemResponse>> GetGemsByServerIdAsync(int serverId);
    Task<GemResponse?> GetGemByIdAsync(int id);
    Task<GemResponse> CreateGemAsync(CreateGemDto createGemDto);
    Task<GemResponse?> UpdateGemAsync(int id, UpdateGemDto updateGemDto);
    Task<bool> DeleteGemAsync(int id);
}
