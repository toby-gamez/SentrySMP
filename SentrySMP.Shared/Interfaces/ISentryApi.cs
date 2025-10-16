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

    // Bundle endpoints
    [Get("/api/bundles")]
    Task<IEnumerable<BundleResponse>> GetBundlesAsync();

    [Get("/api/bundles/server/{serverId}")]
    Task<IEnumerable<BundleResponse>> GetBundlesByServerAsync(int serverId);

    [Get("/api/bundles/{id}")]
    Task<BundleResponse> GetBundleAsync(int id);

    [Post("/api/bundles")]
    Task<BundleResponse> CreateBundleAsync([Body] CreateBundleDto bundle);

    [Put("/api/bundles/{id}")]
    Task<BundleResponse> UpdateBundleAsync(int id, [Body] UpdateBundleDto bundle);

    [Delete("/api/bundles/{id}")]
    Task DeleteBundleAsync(int id);
    
    // Shard endpoints
    [Get("/api/shards")]
    Task<IEnumerable<ShardResponse>> GetShardsAsync();

    [Get("/api/shards/by-server/{serverId}")]
    Task<IEnumerable<ShardResponse>> GetShardsByServerAsync(int serverId);

    [Get("/api/shards/{id}")]
    Task<ShardResponse> GetShardAsync(int id);

    [Post("/api/shards")]
    Task<ShardResponse> CreateShardAsync([Body] CreateShardDto shard);

    [Put("/api/shards/{id}")]
    Task<ShardResponse> UpdateShardAsync(int id, [Body] UpdateShardDto shard);

    [Delete("/api/shards/{id}")]
    Task DeleteShardAsync(int id);

    // File upload endpoints
    [Multipart]
    [Post("/api/files/upload")]
    Task<FileUploadResponse> UploadImageAsync([AliasAs("file")] StreamPart stream);
    
    [Delete("/api/files/{fileName}")]
    Task DeleteImageAsync(string fileName);
}