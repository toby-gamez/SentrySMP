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
                // record resolved existing path
                result.SkippedWithLocalPath.Add(new ImageSyncLocalInfo { FileName = name, LocalPath = localPath });
                continue;
            }

            var url = remoteBase + Uri.EscapeDataString(name);
            try
            {
                using var resp = await client.GetAsync(url, HttpCompletionOption.ResponseHeadersRead);
                if (resp.IsSuccessStatusCode)
                {
                    await using var stream = await resp.Content.ReadAsStreamAsync();
                    await using var fs = System.IO.File.Create(localPath);
                    await stream.CopyToAsync(fs);
                    result.Downloaded.Add(name);
                    // record resolved saved path
                    result.DownloadedWithLocalPath.Add(new ImageSyncLocalInfo { FileName = name, LocalPath = localPath });
                }
                else
                {
                    _logger.LogWarning("Remote image not found or returned {Status} for {Url}", resp.StatusCode, url);
                    result.Failed.Add(new ImageSyncFailure { FileName = name, StatusCode = (int)resp.StatusCode, Url = url });
                }
            }
            catch (Exception ex)
            {
                _logger.LogError(ex, "Error downloading image {Url}", url);
                result.Failed.Add(new ImageSyncFailure { FileName = name, Error = ex.Message, Url = url });
            }
        }

        return result;
    }

    public async Task<ImageSyncResultDto> DownloadByUrlAsync(string url)
    {
        var result = new ImageSyncResultDto();
        if (string.IsNullOrWhiteSpace(url)) return result;

        string fileName;
        try
        {
            var u = new Uri(url);
            fileName = Path.GetFileName(u.LocalPath);
            if (string.IsNullOrWhiteSpace(fileName))
            {
                result.Failed.Add(new ImageSyncFailure { FileName = string.Empty, Error = "Unable to determine file name from URL", Url = url });
                return result;
            }
        }
        catch (Exception ex)
        {
            _logger.LogDebug(ex, "Invalid URL provided for download: {Url}", url);
            result.Failed.Add(new ImageSyncFailure { FileName = string.Empty, Error = "Invalid URL", Url = url });
            return result;
        }

        var uploads = GetUploadsPath();
        Directory.CreateDirectory(uploads);
        var localPath = Path.Combine(uploads, fileName);
        if (System.IO.File.Exists(localPath))
        {
            result.Skipped.Add(fileName);
            result.SkippedWithLocalPath.Add(new ImageSyncLocalInfo { FileName = fileName, LocalPath = localPath });
            return result;
        }

        var client = _httpFactory.CreateClient();
        try
        {
            using var resp = await client.GetAsync(url, HttpCompletionOption.ResponseHeadersRead);
            if (resp.IsSuccessStatusCode)
            {
                await using var stream = await resp.Content.ReadAsStreamAsync();
                await using var fs = System.IO.File.Create(localPath);
                await stream.CopyToAsync(fs);
                result.Downloaded.Add(fileName);
                result.DownloadedWithLocalPath.Add(new ImageSyncLocalInfo { FileName = fileName, LocalPath = localPath });
                return result;
            }
            else
            {
                _logger.LogWarning("Remote image not found or returned {Status} for {Url}", resp.StatusCode, url);
                result.Failed.Add(new ImageSyncFailure { FileName = fileName, StatusCode = (int)resp.StatusCode, Url = url });
                return result;
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Error downloading image {Url}", url);
            result.Failed.Add(new ImageSyncFailure { FileName = fileName, Error = ex.Message, Url = url });
            return result;
        }
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

    public async Task<ImageSyncResultDto> SyncAllMissingAsync(string? remoteBase = null)
    {
        remoteBase = string.IsNullOrWhiteSpace(remoteBase) ? DefaultRemoteBase : remoteBase;
        if (!remoteBase.EndsWith('/')) remoteBase += '/';

        var client = _httpFactory.CreateClient();
        var names = new List<string>();
        try
        {
            // Try to fetch an index page and parse links
            using var resp = await client.GetAsync(remoteBase, HttpCompletionOption.ResponseHeadersRead);
            if (!resp.IsSuccessStatusCode) return new ImageSyncResultDto();
            var content = await resp.Content.ReadAsStringAsync();

            // Attempt JSON array of strings
            try
            {
                var arr = System.Text.Json.JsonSerializer.Deserialize<List<string>>(content);
                if (arr != null && arr.Count > 0)
                {
                    names.AddRange(arr.Select(s => Path.GetFileName(s.Trim())));
                }
            }
            catch { }

            // Fallback: parse hrefs from HTML
            var hrefRegex = new System.Text.RegularExpressions.Regex("href\\\"?=\\\"?([^\'>\"]+)\\\"?", System.Text.RegularExpressions.RegexOptions.IgnoreCase);
            foreach (System.Text.RegularExpressions.Match m in hrefRegex.Matches(content))
            {
                var href = m.Groups[1].Value;
                if (string.IsNullOrWhiteSpace(href)) continue;
                var name = Path.GetFileName(href.Split('?')[0].Trim('/'));
                if (string.IsNullOrEmpty(name)) continue;
                var ext = Path.GetExtension(name).ToLowerInvariant();
                if (ext == ".png" || ext == ".jpg" || ext == ".jpeg" || ext == ".webp" || ext == ".gif" || ext == ".bmp")
                {
                    if (!names.Contains(name)) names.Add(name);
                }
            }
        }
        catch (Exception ex)
        {
            _logger.LogWarning(ex, "Unable to enumerate remote index at {RemoteBase}", remoteBase);
            return new ImageSyncResultDto();
        }

        if (!names.Any()) return new ImageSyncResultDto();

        // Only sync missing
        var webRoot = Path.Combine(_env.ContentRootPath ?? Directory.GetCurrentDirectory(), "wwwroot");
        var uploads = Path.Combine(webRoot, "uploads", "keys");
        Directory.CreateDirectory(uploads);
        var missing = names.Where(n => !System.IO.File.Exists(Path.Combine(uploads, n))).ToList();
        if (!missing.Any()) return new ImageSyncResultDto { Skipped = names.ToList() };

        return await SyncImagesAsync(missing, remoteBase);
    }

    public async Task<IEnumerable<SentrySMP.Shared.DTOs.CombinedImageDto>> GetComparisonAsync(string? remoteBase = null)
    {
        remoteBase = string.IsNullOrWhiteSpace(remoteBase) ? DefaultRemoteBase : remoteBase;
        if (!remoteBase.EndsWith('/')) remoteBase += '/';

        // get local files
        var webRoot = Path.Combine(_env.ContentRootPath ?? Directory.GetCurrentDirectory(), "wwwroot");
        var uploads = Path.Combine(webRoot, "uploads", "keys");
        Directory.CreateDirectory(uploads);

        var localFiles = new Dictionary<string, FileInfo>(StringComparer.OrdinalIgnoreCase);
        foreach (var f in Directory.EnumerateFiles(uploads))
        {
            try
            {
                var fi = new FileInfo(f);
                localFiles[fi.Name] = fi;
            }
            catch { }
        }

        // try to fetch remote index; fallback to empty remote list
        var remoteNames = new List<string>();
        try
        {
            using var resp = await _httpFactory.CreateClient().GetAsync(remoteBase, HttpCompletionOption.ResponseHeadersRead);
            if (resp.IsSuccessStatusCode)
            {
                var content = await resp.Content.ReadAsStringAsync();
                try
                {
                    var arr = System.Text.Json.JsonSerializer.Deserialize<List<string>>(content);
                    if (arr != null && arr.Count > 0)
                    {
                        remoteNames.AddRange(arr.Select(s => Path.GetFileName(s.Trim())));
                    }
                }
                catch { }

                // parse hrefs
                var hrefRegex = new System.Text.RegularExpressions.Regex("href\\\"?=\\\"?([^\'>\"]+)\\\"?", System.Text.RegularExpressions.RegexOptions.IgnoreCase);
                foreach (System.Text.RegularExpressions.Match m in hrefRegex.Matches(content))
                {
                    var href = m.Groups[1].Value;
                    if (string.IsNullOrWhiteSpace(href)) continue;
                    var name = Path.GetFileName(href.Split('?')[0].Trim('/'));
                    if (string.IsNullOrEmpty(name)) continue;
                    var ext = Path.GetExtension(name).ToLowerInvariant();
                    if (ext == ".png" || ext == ".jpg" || ext == ".jpeg" || ext == ".webp" || ext == ".gif" || ext == ".bmp")
                    {
                        if (!remoteNames.Contains(name)) remoteNames.Add(name);
                    }
                }
            }
        }
        catch (Exception ex)
        {
            _logger.LogDebug(ex, "Unable to fetch remote index for comparison");
        }

        // union of names
        var allNames = new HashSet<string>(StringComparer.OrdinalIgnoreCase);
        foreach (var n in localFiles.Keys) allNames.Add(n);
        foreach (var n in remoteNames) allNames.Add(n);

        var client = _httpFactory.CreateClient();
        var results = new List<SentrySMP.Shared.DTOs.CombinedImageDto>();

        foreach (var name in allNames.OrderBy(n => n, StringComparer.OrdinalIgnoreCase))
        {
            var item = new SentrySMP.Shared.DTOs.CombinedImageDto { FileName = name };

            if (localFiles.TryGetValue(name, out var fi))
            {
                item.ExistsLocally = true;
                item.LocalUrl = $"/uploads/keys/{Uri.EscapeDataString(name)}";
                item.LocalSize = fi.Length;
                item.LocalLastModified = fi.LastWriteTimeUtc;
            }

            if (remoteNames.Contains(name))
            {
                item.ExistsRemotely = true;
                item.RemoteUrl = remoteBase + Uri.EscapeDataString(name);

                try
                {
                    using var resp = await client.GetAsync(item.RemoteUrl, HttpCompletionOption.ResponseHeadersRead);
                    if (resp.IsSuccessStatusCode)
                    {
                        if (resp.Content.Headers.ContentLength.HasValue) item.RemoteSize = resp.Content.Headers.ContentLength.Value;
                        if (resp.Content.Headers.LastModified.HasValue) item.RemoteLastModified = resp.Content.Headers.LastModified.Value.UtcDateTime;
                    }
                }
                catch (Exception ex)
                {
                    _logger.LogDebug(ex, "Error fetching remote headers for {Name}", name);
                }
            }

            // If remote index didn't list it, we can still check remote by HEAD to see if exists (optional)
            if (!item.ExistsRemotely)
            {
                var probeUrl = remoteBase + Uri.EscapeDataString(name);
                try
                {
                    using var resp = await client.GetAsync(probeUrl, HttpCompletionOption.ResponseHeadersRead);
                    if (resp.IsSuccessStatusCode)
                    {
                        item.ExistsRemotely = true;
                        item.RemoteUrl = probeUrl;
                        if (resp.Content.Headers.ContentLength.HasValue) item.RemoteSize = resp.Content.Headers.ContentLength.Value;
                        if (resp.Content.Headers.LastModified.HasValue) item.RemoteLastModified = resp.Content.Headers.LastModified.Value.UtcDateTime;
                    }
                }
                catch { }
            }

            results.Add(item);
        }

        return results;
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
