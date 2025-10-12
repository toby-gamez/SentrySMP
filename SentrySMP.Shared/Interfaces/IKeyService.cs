using SentrySMP.Shared.DTOs;

namespace SentrySMP.Shared.Interfaces;

public interface IKeyService
{
    Task<IEnumerable<KeyResponse>> GetAllKeysAsync();
    Task<IEnumerable<KeyResponse>> GetKeysByServerIdAsync(int serverId);
    Task<KeyResponse?> GetKeyByIdAsync(int id);
    Task<KeyResponse> CreateKeyAsync(CreateKeyDto createKeyDto);
    Task<KeyResponse?> UpdateKeyAsync(int id, UpdateKeyDto updateKeyDto);
    Task<bool> DeleteKeyAsync(int id);
}