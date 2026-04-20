using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.App.Services;

public class ImagesProxyService : IImageService
{
    private readonly SentrySMP.Shared.Interfaces.IImagesApi _imagesApi;
    private readonly IHttpClientFactory _httpFactory;
    private readonly IWebHostEnvironment _env;
    private readonly ILogger<ImagesProxyService> _logger;

    public ImagesProxyService(SentrySMP.Shared.Interfaces.IImagesApi imagesApi, IHttpClientFactory httpFactory, IWebHostEnvironment env, ILogger<ImagesProxyService> logger)
    {
        _imagesApi = imagesApi;
        _httpFactory = httpFactory;
        _env = env;
        _logger = logger;
    }

    public async Task<ImageSyncResultDto> SyncImagesAsync(IEnumerable<string> fileNames, string? remoteBase = null)
    {
        var result = new ImageSyncResultDto();
        var available = (await _imagesApi.GetImagesAsync(null)).Select(f => f.FileName).ToHashSet(StringComparer.OrdinalIgnoreCase);

        foreach (var name in fileNames)
        {
            try
            {
                if (!available.Contains(name))
                {
                    result.Skipped.Add(name);
                    continue;
                }

                // download and save locally
                var remoteFiles = await _imagesApi.GetImagesAsync(null);
                var file = remoteFiles.FirstOrDefault(f => string.Equals(f.FileName, name, StringComparison.OrdinalIgnoreCase));
                if (file == null)
                {
                    result.Failed.Add(new ImageSyncFailure { FileName = name, Error = "Not found on remote" });
                    continue;
                }

                var http = _httpFactory.CreateClient();
                using var resp = await http.GetAsync(file.Url);
                if (!resp.IsSuccessStatusCode)
                {
                    result.Failed.Add(new ImageSyncFailure { FileName = name, StatusCode = (int)resp.StatusCode, Url = file.Url });
                    continue;
                }

                var uploads = Path.Combine(_env.WebRootPath ?? _env.ContentRootPath, "uploads", "keys");
                Directory.CreateDirectory(uploads);
                var localPath = Path.Combine(uploads, name);
                await using var fs = new FileStream(localPath, FileMode.Create);
                await resp.Content.CopyToAsync(fs);

                result.Downloaded.Add(name);
                result.DownloadedWithLocalPath.Add(new ImageSyncLocalInfo { FileName = name, LocalPath = localPath });
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error syncing image {Name}", name);
                result.Failed.Add(new ImageSyncFailure { FileName = name, Error = ex.Message });
            }
        }

        return result;
    }

    public async Task<IEnumerable<ImageInfoDto>> GetAllImagesAsync()
    {
        var remote = await _imagesApi.GetImagesAsync(null);
        return remote.Select(r => new ImageInfoDto
        {
            FileName = r.FileName,
            Url = r.Url,
            Size = r.Size,
            ContentType = r.ContentType
        });
    }

    public async Task<IEnumerable<CombinedImageDto>> GetComparisonAsync(string? remoteBase = null)
    {
        var remote = (await _imagesApi.GetImagesAsync(null)).ToDictionary(f => f.FileName, StringComparer.OrdinalIgnoreCase);
        var uploads = Path.Combine(_env.WebRootPath ?? _env.ContentRootPath, "uploads", "keys");
        var localFiles = Directory.Exists(uploads) ? Directory.GetFiles(uploads).Select(p => new FileInfo(p)).ToDictionary(f => f.Name, StringComparer.OrdinalIgnoreCase) : new Dictionary<string, FileInfo>();

        var allNames = new HashSet<string>(remote.Keys, StringComparer.OrdinalIgnoreCase);
        foreach (var ln in localFiles.Keys) allNames.Add(ln);

        var list = new List<CombinedImageDto>();
        foreach (var name in allNames)
        {
            remote.TryGetValue(name, out var r);
            localFiles.TryGetValue(name, out var l);
            list.Add(new CombinedImageDto
            {
                FileName = name,
                ExistsLocally = l != null,
                LocalUrl = l != null ? $"/uploads/keys/{Uri.EscapeDataString(l.Name)}" : null,
                LocalSize = l?.Length,
                LocalLastModified = l?.LastWriteTimeUtc,
                ExistsRemotely = r != null,
                RemoteUrl = r?.Url,
                RemoteSize = r?.Size,
            });
        }

        return list;
    }

    public async Task<IEnumerable<string>> CheckRemoteExistsAsync(IEnumerable<string> fileNames, string? remoteBase = null)
    {
        var remote = await _imagesApi.GetImagesAsync(null);
        var set = remote.Select(f => f.FileName).ToHashSet(StringComparer.OrdinalIgnoreCase);
        return fileNames.Where(n => set.Contains(n));
    }

    public async Task<ImageSyncResultDto> SyncAllMissingAsync(string? remoteBase = null)
    {
        var remote = await _imagesApi.GetImagesAsync(null);
        var uploads = Path.Combine(_env.WebRootPath ?? _env.ContentRootPath, "uploads", "keys");
        Directory.CreateDirectory(uploads);

        var localSet = Directory.Exists(uploads) ? Directory.GetFiles(uploads).Select(p => Path.GetFileName(p)).ToHashSet(StringComparer.OrdinalIgnoreCase) : new HashSet<string>(StringComparer.OrdinalIgnoreCase);
        var missing = remote.Where(r => !localSet.Contains(r.FileName)).Select(r => r.FileName);
        return await SyncImagesAsync(missing);
    }

    public async Task<ImageSyncResultDto> DownloadByUrlAsync(string url)
    {
        var result = new ImageSyncResultDto();
        try
        {
            var fileName = Path.GetFileName(new Uri(url).LocalPath);
            var http = _httpFactory.CreateClient();
            using var resp = await http.GetAsync(url);
            if (!resp.IsSuccessStatusCode)
            {
                result.Failed.Add(new ImageSyncFailure { FileName = fileName, StatusCode = (int)resp.StatusCode, Url = url });
                return result;
            }

            var uploads = Path.Combine(_env.WebRootPath ?? _env.ContentRootPath, "uploads", "keys");
            Directory.CreateDirectory(uploads);
            var localPath = Path.Combine(uploads, fileName);
            await using var fs = new FileStream(localPath, FileMode.Create);
            await resp.Content.CopyToAsync(fs);

            result.Downloaded.Add(fileName);
            result.DownloadedWithLocalPath.Add(new ImageSyncLocalInfo { FileName = fileName, LocalPath = localPath });
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error downloading by url {Url}", url);
            result.Failed.Add(new ImageSyncFailure { FileName = "", Error = ex.Message, Url = url });
        }

        return result;
    }
}
