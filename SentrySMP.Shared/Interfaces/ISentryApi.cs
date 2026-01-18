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

    // Rank endpoints
    [Get("/api/ranks")]
    Task<IEnumerable<RankResponse>> GetRanksAsync();


    [Get("/api/ranks/{id}")]
    Task<RankResponse> GetRankAsync(int id);

    [Post("/api/ranks")]
    Task<RankResponse> CreateRankAsync([Body] CreateRankDto rank);

    [Put("/api/ranks/{id}")]
    Task<RankResponse> UpdateRankAsync(int id, [Body] UpdateRankDto rank);

    [Delete("/api/ranks/{id}")]
    Task DeleteRankAsync(int id);

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

    // BattlePass endpoints
    [Get("/api/battlepasses")]
    Task<IEnumerable<BattlePassResponse>> GetBattlePassesAsync();

    [Get("/api/battlepasses/server/{serverId}")]
    Task<IEnumerable<BattlePassResponse>> GetBattlePassesByServerAsync(int serverId);

    [Get("/api/battlepasses/{id}")]
    Task<BattlePassResponse> GetBattlePassAsync(int id);

    [Post("/api/battlepasses")]
    Task<BattlePassResponse> CreateBattlePassAsync([Body] CreateBattlePassDto bp);

    [Put("/api/battlepasses/{id}")]
    Task<BattlePassResponse> UpdateBattlePassAsync(int id, [Body] UpdateBattlePassDto bp);

    [Delete("/api/battlepasses/{id}")]
    Task DeleteBattlePassAsync(int id);
    
    // Status endpoints (MC / Discord)
    [Get("/api/status/mc")]
    Task<McStatusResponse> GetMcStatusAsync();

    [Get("/api/status/discord")]
    Task<DiscordStatusResponse> GetDiscordStatusAsync();
    
    // Announcements / News
    [Get("/api/announcements")]
    Task<IEnumerable<SentrySMP.Shared.DTOs.AnnouncementDto>> GetAnnouncementsAsync();
    
    // Coin endpoints
    [Get("/api/coins")]
    Task<IEnumerable<CoinResponse>> GetCoinsAsync();

    [Get("/api/coins/by-server/{serverId}")]
    Task<IEnumerable<CoinResponse>> GetCoinsByServerAsync(int serverId);

    [Get("/api/coins/{id}")]
    Task<CoinResponse> GetCoinAsync(int id);

    [Post("/api/coins")]
    Task<CoinResponse> CreateCoinAsync([Body] CreateCoinDto coin);

    [Put("/api/coins/{id}")]
    Task<CoinResponse> UpdateCoinAsync(int id, [Body] UpdateCoinDto coin);

    [Delete("/api/coins/{id}")]
    Task DeleteCoinAsync(int id);

    // File upload endpoints
    [Multipart]
    [Post("/api/files/upload")]
    Task<FileUploadResponse> UploadImageAsync([AliasAs("file")] StreamPart stream);
    
    [Delete("/api/files/{fileName}")]
    Task DeleteImageAsync(string fileName);

    // Images endpoints (listing and sync)
    [Get("/api/images")]
    Task<IEnumerable<SentrySMP.Shared.DTOs.ImageInfoDto>> GetImagesAsync();

    // Download a single remote image into the server's uploads/keys folder
    [Post("/api/images/download/{fileName}")]
    Task<SentrySMP.Shared.DTOs.ImageSyncResultDto> DownloadImageAsync(string fileName, [Query] string? remoteBase = null);

    // Team endpoints
    [Get("/api/team")]
    Task<TeamResponseDto> GetTeamAsync();

    [Post("/api/team")]
    Task<TeamResponseDto> SaveTeamAsync([Body] TeamResponseDto dto);

    // Transaction endpoints
    [Get("/api/transactions")]
    Task<IEnumerable<SentrySMP.Shared.DTOs.TransactionResponse>> GetTransactionsAsync();

    // Payment settings endpoints
    [Get("/api/settings/payments")]
    Task<PaymentSettingsResponse?> GetPaymentSettingsAsync();

    [Put("/api/settings/payments")]
    Task<PaymentSettingsResponse?> UpdatePaymentSettingsAsync([Body] UpdatePaymentSettingsRequest request);
}