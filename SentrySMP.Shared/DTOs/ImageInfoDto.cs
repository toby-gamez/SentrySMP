namespace SentrySMP.Shared.DTOs;

public class ImageInfoDto
{
    public string FileName { get; set; } = string.Empty;
    public string Url { get; set; } = string.Empty; // relative URL (/uploads/keys/..)
    public long Size { get; set; }
    public string? ContentType { get; set; }
    public DateTimeOffset? LastModified { get; set; }
}

public class CombinedImageDto
{
    public string FileName { get; set; } = string.Empty;

    // Local info (if present)
    public bool ExistsLocally { get; set; }
    public string? LocalUrl { get; set; }
    public long? LocalSize { get; set; }
    public DateTimeOffset? LocalLastModified { get; set; }

    // Remote info (if present)
    public bool ExistsRemotely { get; set; }
    public string? RemoteUrl { get; set; }
    public long? RemoteSize { get; set; }
    public DateTimeOffset? RemoteLastModified { get; set; }
}
