namespace SentrySMP.Shared.DTOs;

public class ImageInfoDto
{
    public string FileName { get; set; } = string.Empty;
    public string Url { get; set; } = string.Empty; // relative URL (/uploads/keys/..)
    public long Size { get; set; }
    public string? ContentType { get; set; }
    public DateTimeOffset? LastModified { get; set; }
}
