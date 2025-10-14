using SentrySMP.Shared.DTOs;

namespace SentrySMP.Shared.Interfaces;

public interface IShardService
{
    Task<IEnumerable<ShardResponse>> GetAllShardsAsync();
    Task<IEnumerable<ShardResponse>> GetShardsByServerIdAsync(int serverId);
    Task<ShardResponse?> GetShardByIdAsync(int id);
    Task<ShardResponse> CreateShardAsync(CreateShardDto createShardDto);
    Task<ShardResponse?> UpdateShardAsync(int id, UpdateShardDto updateShardDto);
    Task<bool> DeleteShardAsync(int id);
}
