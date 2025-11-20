using System.Net;
using Microsoft.Extensions.Hosting;
using Microsoft.Extensions.Logging;
using SentrySMP.Shared.DTOs;
using SentrySMP.Shared.Interfaces;

namespace SentrySMP.Api.Services;

public class ImageService : IImageService
{
    private readonly IHttpClientFactory _httpFactory;
    private readonly IHostEnvironment _env;
    private readonly ILogger<ImageService> _logger;

    private const string DefaultRemoteBase = "https://sentrysmp.eu/uploads/keys/";

    public ImageService(IHttpClientFactory httpFactory, IHostEnvironment env, ILogger<ImageService> logger)
    {
        _httpFactory = httpFactory;
        _env = env;
        _logger = logger;
    }

    public async Task<ImageSyncResultDto> SyncImagesAsync(IEnumerable<string> fileNames, string? remoteBase = null)
    {
        var result = new ImageSyncResultDto();
        if (fileNames == null) return result;

        remoteBase = string.IsNullOrWhiteSpace(remoteBase) ? DefaultRemoteBase : remoteBase;
        if (!remoteBase.EndsWith('/')) remoteBase += '/';

    var uploads = GetUploadsPath();
        Directory.CreateDirectory(uploads);

        var client = _httpFactory.CreateClient();

        foreach (var raw in fileNames)
        {
            if (string.IsNullOrWhiteSpace(raw)) continue;
            var name = Path.GetFileName(raw.Trim());
            var localPath = Path.Combine(uploads, name);
            if (System.IO.File.Exists(localPath))
            {
                result.Skipped.Add(name);
                continue;
            }

            var url = remoteBase + WebUtility.UrlEncode(name);
            try
            {
                using var resp = await client.GetAsync(url, HttpCompletionOption.ResponseHeadersRead);
                if (resp.IsSuccessStatusCode)
                {
                    await using var stream = await resp.Content.ReadAsStreamAsync();
                    await using var fs = System.IO.File.Create(localPath);
                    await stream.CopyToAsync(fs);
                    result.Downloaded.Add(name);
                }
                else
                {
                    _logger.LogWarning("Remote image not found or returned {Status} for {Url}", resp.StatusCode, url);
                    result.Failed.Add(new ImageSyncFailure { FileName = name, StatusCode = (int)resp.StatusCode });
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error downloading image {Url}", url);
                result.Failed.Add(new ImageSyncFailure { FileName = name, Error = ex.Message });
            }
        }

        return result;
    }

    public Task<IEnumerable<SentrySMP.Shared.DTOs.ImageInfoDto>> GetAllImagesAsync()
    {
    var uploads = GetUploadsPath();
    if (!Directory.Exists(uploads)) return Task.FromResult(Enumerable.Empty<SentrySMP.Shared.DTOs.ImageInfoDto>());

        var files = new List<SentrySMP.Shared.DTOs.ImageInfoDto>();
        foreach (var f in Directory.EnumerateFiles(uploads))
        {
            try
            {
                var fi = new FileInfo(f);
                var name = Path.GetFileName(f);
                files.Add(new SentrySMP.Shared.DTOs.ImageInfoDto
                {
                    FileName = name,
                    Url = $"/uploads/keys/{Uri.EscapeDataString(name)}",
                    Size = fi.Length,
                    ContentType = GetContentTypeByExtension(fi.Extension),
                    LastModified = fi.LastWriteTimeUtc
                });
            }
            catch (Exception ex)
            {
                _logger.LogDebug(ex, "Skipping file while enumerating images: {File}", f);
            }
        }

        return Task.FromResult<IEnumerable<SentrySMP.Shared.DTOs.ImageInfoDto>>(files);
    }

    /// <summary>
    /// Resolve the uploads/keys folder. Prefer the App project's wwwroot (SentrySMP.App/wwwroot/uploads/keys) when available.
    /// Falls back to host content root/wwwroot/uploads/keys.
    /// </summary>
    private string GetUploadsPath()
    {
        // Candidates to start searching from
        var candidates = new[] {
            _env.ContentRootPath ?? string.Empty,
            AppContext.BaseDirectory ?? string.Empty,
            Directory.GetCurrentDirectory()
        };

        foreach (var start in candidates.Where(s => !string.IsNullOrWhiteSpace(s)))
        {
            try
            {
                var dir = new DirectoryInfo(start);
                while (dir != null)
                {
                    // look for a sibling/project folder named SentrySMP.App
                    var appProject = Path.Combine(dir.FullName, "SentrySMP.App");
                    if (Directory.Exists(appProject))
                    {
                        var candidate = Path.Combine(appProject, "wwwroot", "uploads", "keys");
                        return candidate;
                    }

                    dir = dir.Parent;
                }
            }
            catch
            {
                // ignore and try next candidate
            }
        }

        // fallback to content root/wwwroot/uploads/keys
        var webRoot = Path.Combine(_env.ContentRootPath ?? Directory.GetCurrentDirectory(), "wwwroot");
        return Path.Combine(webRoot, "uploads", "keys");
    }

    public async Task<IEnumerable<string>> CheckRemoteExistsAsync(IEnumerable<string> fileNames, string? remoteBase = null)
    {
        remoteBase = string.IsNullOrWhiteSpace(remoteBase) ? DefaultRemoteBase : remoteBase;
        if (!remoteBase.EndsWith('/')) remoteBase += '/';

        var client = _httpFactory.CreateClient();
        var found = new List<string>();
        foreach (var raw in fileNames)
        {
            if (string.IsNullOrWhiteSpace(raw)) continue;
            var name = Path.GetFileName(raw.Trim());
            var url = remoteBase + Uri.EscapeDataString(name);
            try
            {
                // Some servers do not respond correctly to HEAD requests; use GET with headers-only read
                using var resp = await client.GetAsync(url, HttpCompletionOption.ResponseHeadersRead);
                if (resp.IsSuccessStatusCode)
                {
                    found.Add(name);
                }
            }
            catch (Exception ex)
            {
                _logger.LogDebug(ex, "Error checking remote existence for {Url}", url);
            }
        }

        return found;
    }

    private static string? GetContentTypeByExtension(string ext)
    {
        if (string.IsNullOrEmpty(ext)) return null;
        ext = ext.ToLowerInvariant();
        return ext switch
        {
            ".png" => "image/png",
            ".jpg" or ".jpeg" => "image/jpeg",
            ".gif" => "image/gif",
            ".webp" => "image/webp",
            ".bmp" => "image/bmp",
            _ => null,
        };
    }
}
