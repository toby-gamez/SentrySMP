using Refit;
using SentrySMP.Shared.DTOs;

namespace SentrySMP.Shared.Interfaces;

public interface ISentryApi
{
    // Server endpoints
    [Get("/api/servers")]
    Task<IEnumerable<ServerResponse>> GetServersAsync();
    
    [Get("/api/servers/{id}")]
    Task<ServerResponse> GetServerAsync(int id);
    
    [Post("/api/servers")]
    Task<ServerResponse> CreateServerAsync([Body] CreateServerDto server);
    
    [Put("/api/servers/{id}")]
    Task<ServerResponse> UpdateServerAsync(int id, [Body] UpdateServerDto server);
    
    [Delete("/api/servers/{id}")]
    Task DeleteServerAsync(int id);
    
    // Key endpoints
    [Get("/api/keys")]
    Task<IEnumerable<KeyResponse>> GetKeysAsync();
    
    [Get("/api/keys/server/{serverId}")]
    Task<IEnumerable<KeyResponse>> GetKeysByServerAsync(int serverId);
    
    [Get("/api/keys/{id}")]
    Task<KeyResponse> GetKeyAsync(int id);
    
    [Post("/api/keys")]
    Task<KeyResponse> CreateKeyAsync([Body] CreateKeyDto key);
    
    [Put("/api/keys/{id}")]
    Task<KeyResponse> UpdateKeyAsync(int id, [Body] UpdateKeyDto key);
    
    [Delete("/api/keys/{id}")]
    Task DeleteKeyAsync(int id);
}