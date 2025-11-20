namespace SentrySMP.Shared.DTOs;

public class ImageSyncFailure
{
    public string FileName { get; set; } = string.Empty;
    public int? StatusCode { get; set; }
    public string? Error { get; set; }
}

public class ImageSyncResultDto
{
    public List<string> Downloaded { get; set; } = new List<string>();
    public List<string> Skipped { get; set; } = new List<string>();
    public List<ImageSyncFailure> Failed { get; set; } = new List<ImageSyncFailure>();
}
