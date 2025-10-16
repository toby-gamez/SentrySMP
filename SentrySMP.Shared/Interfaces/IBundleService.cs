using SentrySMP.Shared.DTOs;

namespace SentrySMP.Shared.Interfaces;

public interface IBundleService
{
    Task<IEnumerable<BundleResponse>> GetAllBundlesAsync();
    Task<IEnumerable<BundleResponse>> GetBundlesByServerIdAsync(int serverId);
    Task<BundleResponse?> GetBundleByIdAsync(int id);
    Task<BundleResponse> CreateBundleAsync(CreateBundleDto createBundleDto);
    Task<BundleResponse?> UpdateBundleAsync(int id, UpdateBundleDto updateBundleDto);
    Task<bool> DeleteBundleAsync(int id);
}
