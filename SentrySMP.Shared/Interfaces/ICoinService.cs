using SentrySMP.Shared.DTOs;

namespace SentrySMP.Shared.Interfaces;

public interface ICoinService
{
    Task<IEnumerable<CoinResponse>> GetAllCoinsAsync();
    Task<IEnumerable<CoinResponse>> GetCoinsByServerIdAsync(int serverId);
    Task<CoinResponse?> GetCoinByIdAsync(int id);
    Task<CoinResponse> CreateCoinAsync(CreateCoinDto createCoinDto);
    Task<CoinResponse?> UpdateCoinAsync(int id, UpdateCoinDto updateCoinDto);
    Task<bool> DeleteCoinAsync(int id);
}
