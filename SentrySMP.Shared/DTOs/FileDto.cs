namespace SentrySMP.Shared.DTOs;

public class FileUploadResponse
{
    public string FileName { get; set; } = string.Empty;
    public string OriginalFileName { get; set; } = string.Empty;
    public string Url { get; set; } = string.Empty;
    public long Size { get; set; }
    public string? ContentType { get; set; }
}