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
    /// Return a combined view aligning remote and local images by filename.
    /// Each item indicates whether it exists locally and/or remotely and includes available metadata.
    /// </summary>
    Task<IEnumerable<SentrySMP.Shared.DTOs.CombinedImageDto>> GetComparisonAsync(string? remoteBase = null);

    /// <summary>
    /// Check which of the provided file names exist on the configured remote base (HEAD requests).
    /// Returns the subset of names that exist remotely.
    /// </summary>
    Task<IEnumerable<string>> CheckRemoteExistsAsync(IEnumerable<string> fileNames, string? remoteBase = null);

    /// <summary>
    /// Fetch a remote index (if available) and download any files that exist on the remote but are missing locally.
    /// Useful to sync the local uploads/keys folder with the production remote.
    /// </summary>
    Task<ImageSyncResultDto> SyncAllMissingAsync(string? remoteBase = null);

    /// <summary>
    /// Download a specific remote file by its absolute URL and save it under wwwroot/uploads/keys using the file name from the URL.
    /// Returns an ImageSyncResultDto describing success/failure and the resolved local path when successful.
    /// </summary>
    Task<ImageSyncResultDto> DownloadByUrlAsync(string url);
}
