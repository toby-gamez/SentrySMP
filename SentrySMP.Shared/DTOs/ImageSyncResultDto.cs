namespace SentrySMP.Shared.DTOs;

public class ImageSyncFailure
{
    public string FileName { get; set; } = string.Empty;
    public int? StatusCode { get; set; }
    public string? Error { get; set; }
    public string? Url { get; set; }
}

public partial class ImageSyncResultDto
{
    public List<string> Downloaded { get; set; } = new List<string>();
    public List<string> Skipped { get; set; } = new List<string>();
    public List<ImageSyncFailure> Failed { get; set; } = new List<ImageSyncFailure>();
}

public class ImageSyncLocalInfo
{
    public string FileName { get; set; } = string.Empty;
    public string LocalPath { get; set; } = string.Empty; // resolved filesystem path where the file was saved
}

// Backwards-compatible extension: list of downloaded files with resolved local paths
public partial class ImageSyncResultDto
{
    public List<ImageSyncLocalInfo> DownloadedWithLocalPath { get; set; } = new List<ImageSyncLocalInfo>();
    public List<ImageSyncLocalInfo> SkippedWithLocalPath { get; set; } = new List<ImageSyncLocalInfo>();
}
