using SentrySMP.Shared.DTOs;

namespace SentrySMP.Shared.Interfaces;

public interface IServerService
{
    Task<IEnumerable<ServerResponse>> GetAllServersAsync();
    Task<ServerResponse?> GetServerByIdAsync(int id);
    Task<ServerResponse> CreateServerAsync(CreateServerDto createServerDto);
    Task<ServerResponse?> UpdateServerAsync(int id, UpdateServerDto updateServerDto);
    Task<bool> DeleteServerAsync(int id);
}