using SentrySMP.Shared.DTOs;

namespace SentrySMP.Shared.Interfaces;

public interface IImageService
{
    /// <summary>
    /// Ensure the supplied file names exist locally under wwwroot/uploads/keys by downloading missing ones from remoteBase.
    /// </summary>
    Task<ImageSyncResultDto> SyncImagesAsync(IEnumerable<string> fileNames, string? remoteBase = null);

    /// <summary>
    /// Return metadata for all images stored under wwwroot/uploads/keys (relative URL, size, content type, last modified).
    /// </summary>
    Task<IEnumerable<SentrySMP.Shared.DTOs.ImageInfoDto>> GetAllImagesAsync();

    /// <summary>
    /// Check which of the provided file names exist on the configured remote base (HEAD requests).
    /// Returns the subset of names that exist remotely.
    /// </summary>
    Task<IEnumerable<string>> CheckRemoteExistsAsync(IEnumerable<string> fileNames, string? remoteBase = null);
}
